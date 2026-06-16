<?php

namespace App\Http\Controllers;

use App\Services\LearningAgentService;
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
        $history = $this->agentService->getHistory($user->id, 10);

        return view('dashboard.agent', compact(
            'selectedLevelGroup',
            'remainingRequests',
            'history'
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

        // Backward compatibility: if only query is sent
        if (empty($messages) && !empty($query)) {
            $messages = [
                ['role' => 'user', 'text' => $query]
            ];
        }

        Log::info('Agent chat request', [
            'user_id' => $user->id,
            'message_count' => count($messages),
            'type' => $type,
            'grade' => $user->grade,
            'level_group' => $user->current_level_group,
        ]);

        // Chat with tutor
        $result = $this->agentService->chatWithTutor($messages, $user, $contextId, $type);

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
}
