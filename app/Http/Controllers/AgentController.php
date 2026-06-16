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
     * Handle an AI lesson request.
     */
    public function ask(Request $request)
    {
        $request->validate([
            'query' => 'sometimes|string|min:2|max:1000',
            'messages' => 'sometimes|array',
            'messages.*.role' => 'required_with:messages|string|in:user,model',
            'messages.*.text' => 'required_with:messages|string',
            'type' => 'sometimes|string|in:lesson,roadmap,quiz',
            'context_id' => 'sometimes|nullable|integer',
        ]);

        $user = Auth::user();
        $query = trim($request->input('query', ''));
        $messages = $request->input('messages', []);
        $type = $request->input('type', 'lesson');
        $contextId = $request->input('context_id');
        $sessionId = $request->input('session_id');

        // Backward compatibility: if only query is sent
        if (empty($messages) && !empty($query)) {
            $messages = [
                ['role' => 'user', 'text' => $query]
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
            $title = !empty($query) ? \Illuminate\Support\Str::limit($query, 30) : 'Chat ' . now()->format('Y-m-d H:i');
            $chatSession = AgentChatSession::create([
                'user_id' => $user->id,
                'title' => $title
            ]);
        }
        
        // Save the user's latest message to DB
        if (!empty($query) && $chatSession) {
            AgentChatMessage::create([
                'agent_chat_session_id' => $chatSession->id,
                'role' => 'user',
                'text' => $query
            ]);
        }

        Log::info('Agent chat request', [
            'user_id' => $user->id,
            'message_count' => count($messages),
            'type' => $type,
            'grade' => $user->grade,
            'level_group' => $user->current_level_group,
            'session_id' => $chatSession ? $chatSession->id : null
        ]);

        // Chat with tutor
        $result = $this->agentService->chatWithTutor($messages, $user, $contextId, $type);
        
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
