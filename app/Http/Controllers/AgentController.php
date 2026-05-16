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
            'query' => 'required|string|min:3|max:500',
            'type' => 'sometimes|string|in:lesson,roadmap,quiz',
            'context_id' => 'sometimes|nullable|integer',
        ]);

        $user = Auth::user();
        $query = trim($request->input('query'));
        $type = $request->input('type', 'lesson');
        $contextId = $request->input('context_id');

        Log::info('Agent ask request', [
            'user_id' => $user->id,
            'query' => $query,
            'type' => $type,
            'grade' => $user->grade,
            'level_group' => $user->current_level_group,
        ]);

        // Detect smart intent based on query
        $detectedType = $this->agentService->detectIntent($query, $type);

        if ($detectedType === 'roadmap') {
            $result = $this->agentService->findOrCreateRoadmap($query, $user, $contextId);
        } elseif ($detectedType === 'quiz') {
            $result = $this->agentService->findOrCreateQuiz($query, $user, $contextId);
        } else {
            $result = $this->agentService->findOrCreateLesson($query, $user, $contextId);
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
}
