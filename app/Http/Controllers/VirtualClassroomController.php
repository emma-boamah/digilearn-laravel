<?php

namespace App\Http\Controllers;

use App\Events\ClassroomMessageEvent;
use App\Events\ClassroomSignalEvent;
use App\Events\ClassroomWhiteboardEvent;
use App\Models\VirtualClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VirtualClassroomController extends Controller
{
    /**
     * Send WebRTC signal or room control action.
     */
    public function signal(Request $request, string $roomId)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:offer,answer,ice-candidate,mute-request,hand-raise,screen-share-start,screen-share-stop,camera-toggle,mic-toggle',
            'payload' => 'nullable|array',
            'targetUserId' => 'nullable|integer',
        ]);

        $user = Auth::user();

        $sender = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role ?? ($user->tutorProfile ? 'tutor' : 'student'),
            'avatar_initial' => strtoupper(substr($user->name, 0, 1)),
        ];

        broadcast(new ClassroomSignalEvent(
            $roomId,
            $validated['type'],
            $validated['payload'] ?? [],
            $sender,
            $validated['targetUserId'] ?? null
        ))->toOthers();

        return response()->json(['status' => 'success']);
    }

    /**
     * Send real-time chat message in the classroom.
     */
    public function sendMessage(Request $request, string $roomId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $messageData = [
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role ?? ($user->tutorProfile ? 'tutor' : 'student'),
            'avatar_initial' => strtoupper(substr($user->name, 0, 1)),
            'content' => $validated['message'],
            'created_at' => now()->format('h:i A'),
        ];

        broadcast(new ClassroomMessageEvent($roomId, $messageData));

        return response()->json([
            'status' => 'success',
            'message' => $messageData,
        ]);
    }

    /**
     * Broadcast whiteboard draw / clear / shape action.
     */
    public function syncWhiteboard(Request $request, string $roomId)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:draw,clear,add-object,modify-object,remove-object,load-state,cursor,change-page',
            'data' => 'required|array',
        ]);

        $user = Auth::user();

        $sender = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role ?? ($user->tutorProfile ? 'tutor' : 'student'),
        ];

        broadcast(new ClassroomWhiteboardEvent(
            $roomId,
            $validated['action'],
            $validated['data'],
            $sender
        ))->toOthers();

        return response()->json(['status' => 'success']);
    }

    /**
     * Get ICE / STUN servers for WebRTC.
     */
    public function getIceServers(string $roomId)
    {
        return response()->json([
            'iceServers' => [
                ['urls' => 'stun:stun.l.google.com:19302'],
                ['urls' => 'stun:stun1.l.google.com:19302'],
                ['urls' => 'stun:stun2.l.google.com:19302'],
                ['urls' => 'stun:stun3.l.google.com:19302'],
                ['urls' => 'stun:stun4.l.google.com:19302'],
            ],
        ]);
    }

    /**
     * Upload presentation slides or PDF lesson sheet.
     */
    public function uploadMaterial(Request $request, string $roomId)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $path = $file->store('classroom-materials/' . $roomId, 'public');
        $url = Storage::url($path);

        return response()->json([
            'status' => 'success',
            'url' => asset($url),
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
        ]);
    }
}
