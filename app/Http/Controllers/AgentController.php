<?php

namespace App\Http\Controllers;

use App\Services\LearningAgentService;
use App\Models\AgentChatSession;
use App\Models\AgentChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    private LearningAgentService $agentService;

    public function __construct(LearningAgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    /**
     * Show the AI Learning Agent page.
     */
    public function index()
    {
        $user = Auth::user();
        $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');

        if (!$selectedLevelGroup) {
            return redirect()->route('dashboard.level-selection');
        }

        $remainingRequests = $this->agentService->getRemainingRequests($user->id);
        
        $allSessions = AgentChatSession::where('user_id', $user->id)->orderBy('updated_at', 'desc')->take(20)->get();
        $chatSession = $allSessions->first();
        $chatMessages = $chatSession ? $chatSession->messages()->orderBy('id', 'asc')->get() : collect();

        return view('dashboard.agent', compact(
            'selectedLevelGroup',
            'remainingRequests',
            'allSessions',
            'chatSession',
            'chatMessages'
        ));
    }

    /**
     * Handle an AI lesson request with multimodal support.
     */
    public function ask(Request $request)
    {
        // Decode JSON messages if provided as string in FormData
        if ($request->has('messages') && is_string($request->input('messages'))) {
            $decoded = json_decode($request->input('messages'), true);
            if (is_array($decoded)) {
                $request->merge(['messages' => $decoded]);
            }
        }

        $request->validate([
            'query' => 'sometimes|nullable|string|max:2000',
            'messages' => 'sometimes|array',
            'messages.*.role' => 'required_with:messages|string|in:user,model',
            'messages.*.text' => 'sometimes|nullable|string',
            'type' => 'sometimes|string|in:lesson,roadmap,quiz',
            'context_id' => 'sometimes|nullable|integer',
            'session_id' => 'sometimes|nullable|integer',
            'attachment' => 'sometimes|nullable|file|mimes:jpeg,png,jpg,webp,gif,pdf,txt,csv,docx|max:10240',
        ]);

        $user = Auth::user();
        $query = trim($request->input('query', ''));
        $messages = $request->input('messages', []);
        $type = $request->input('type', 'lesson');
        $contextId = $request->input('context_id');
        $sessionId = $request->input('session_id');

        // Handle uploaded attachment safely
        $attachmentData = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType() ?: $file->getMimeType();
            $size = $file->getSize();

            // Store securely in storage/app/public/agent_attachments/{user_id}/
            $storedPath = $file->store('agent_attachments/' . $user->id, 'public');
            $fullPath = storage_path('app/public/' . $storedPath);
            $publicUrl = asset('storage/' . $storedPath);

            $attachmentData = [
                'path' => $fullPath,
                'url' => $publicUrl,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'is_image' => str_starts_with($mimeType, 'image/'),
            ];
        }

        // Backward compatibility: if only query or attachment is sent
        if (empty($messages) && (!empty($query) || $attachmentData)) {
            $messages = [
                ['role' => 'user', 'text' => $query ?: 'Please analyze this attached file.']
            ];
        }
        
        // Resolve Chat Session
        if ($sessionId) {
            $chatSession = AgentChatSession::where('user_id', $user->id)->find($sessionId);
            // Touch the session so it goes to the top of the list
            if ($chatSession) {
                $chatSession->touch();
            }
        } else {
            $title = !empty($query) 
                ? \Illuminate\Support\Str::limit($query, 30) 
                : ($attachmentData ? 'File: ' . \Illuminate\Support\Str::limit($attachmentData['original_name'], 20) : 'Chat ' . now()->format('Y-m-d H:i'));
            
            $chatSession = AgentChatSession::create([
                'user_id' => $user->id,
                'title' => $title
            ]);
        }
        
        // Save the user's latest message to DB
        if ((!empty($query) || $attachmentData) && $chatSession) {
            $userMetadata = null;
            if ($attachmentData) {
                $userMetadata = [
                    'attachment' => [
                        'url' => $attachmentData['url'],
                        'name' => $attachmentData['original_name'],
                        'mime_type' => $attachmentData['mime_type'],
                        'size' => $attachmentData['size'],
                        'is_image' => $attachmentData['is_image'],
                    ]
                ];
            }

            AgentChatMessage::create([
                'agent_chat_session_id' => $chatSession->id,
                'role' => 'user',
                'text' => $query ?: 'Attached: ' . ($attachmentData['original_name'] ?? 'file'),
                'metadata' => $userMetadata
            ]);
        }

        Log::info('Agent chat request', [
            'user_id' => $user->id,
            'message_count' => count($messages),
            'type' => $type,
            'grade' => $user->grade,
            'level_group' => $user->current_level_group,
            'session_id' => $chatSession ? $chatSession->id : null,
            'has_attachment' => !empty($attachmentData),
        ]);

        // Chat with tutor
        $result = $this->agentService->chatWithTutor($messages, $user, $contextId, $type, $attachmentData);
        
        // Save the AI's response to DB
        if ($result['success'] && !empty($result['message']) && $chatSession) {
            $metadata = null;
            if (isset($result['type'])) {
                $metadata = array_filter([
                    'success' => true,
                    'type' => $result['type'] ?? null,
                    'topic' => $result['topic'] ?? null,
                    'title' => $result['title'] ?? $result['topic'] ?? null,
                    'lesson_url' => $result['lesson_url'] ?? null,
                    'quiz_url' => $result['quiz_url'] ?? null,
                    'quiz_type' => $result['quiz_type'] ?? null,
                    'roadmap' => $result['roadmap'] ?? null,
                    'is_existing' => $result['is_existing'] ?? null,
                    'thumbnail' => $result['thumbnail'] ?? null,
                    'duration' => $result['duration'] ?? null,
                ]);
            }
            
            AgentChatMessage::create([
                'agent_chat_session_id' => $chatSession->id,
                'role' => 'model',
                'text' => $result['message'],
                'metadata' => empty($metadata) ? null : $metadata
            ]);
        }
        
        if ($chatSession) {
            $result['session_id'] = $chatSession->id;
        }

        if ($attachmentData) {
            $result['attachment'] = [
                'url' => $attachmentData['url'],
                'name' => $attachmentData['original_name'],
                'mime_type' => $attachmentData['mime_type'],
                'is_image' => $attachmentData['is_image'],
            ];
        }

        return response()->json($result);
    }

    /**
     * Get the user's agent request history.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $limit = min((int) $request->get('limit', 20), 50);
        $history = $this->agentService->getHistory($user->id, $limit);

        return response()->json([
            'success' => true,
            'history' => $history,
            'remaining_requests' => $this->agentService->getRemainingRequests($user->id),
        ]);
    }
    /**
     * Load a specific chat session's messages.
     */
    public function loadSession($id)
    {
        $user = Auth::user();
        $session = AgentChatSession::where('user_id', $user->id)->findOrFail($id);
        $messages = $session->messages()->orderBy('id', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'messages' => $messages->map(function($msg) {
                return [
                    'role' => $msg->role,
                    'text' => $msg->text,
                    'metadata' => $msg->metadata
                ];
            })
        ]);
    }
}
