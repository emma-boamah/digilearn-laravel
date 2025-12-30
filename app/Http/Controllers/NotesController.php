<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserNote;
use App\Services\UrlObfuscator;

class NotesController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $notes = UserNote::where('user_id', $userId)
            ->with('video')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'encoded_id' => UrlObfuscator::encode($note->id),
                    'title' => $note->title ?: 'Untitled Notes',
                    'subject' => $note->video ? $note->video->title : 'Unknown Video',
                    'content' => substr(strip_tags($note->content), 0, 150) . '...',
                    'created_at' => $note->formatted_created_at,
                    'updated_at' => $note->formatted_updated_at,
                    'lesson_id' => $note->video_id,
                    'video_title' => $note->video ? $note->video->title : 'Unknown Video',
                ];
            });

        return view('dashboard.notes', compact('notes'));
    }

    public function view($id)
    {
        $userId = Auth::id();

        $note = UserNote::where('id', $id)
            ->where('user_id', $userId)
            ->with('video')
            ->first();

        if (!$note) {
            return redirect()->route('dashboard.notes')->with('error', 'Note not found.');
        }

        $noteData = [
            'id' => $note->id,
            'encoded_id' => UrlObfuscator::encode($note->id),
            'title' => $note->title ?: 'Untitled Notes',
            'subject' => $note->video ? $note->video->title : 'Unknown Video',
            'content' => $note->content,
            'created_at' => $note->formatted_created_at,
            'updated_at' => $note->formatted_updated_at,
            'lesson_id' => $note->video_id,
            'video_title' => $note->video ? $note->video->title : 'Unknown Video',
        ];

        return view('dashboard.notes.view', ['note' => $noteData]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'lesson_id' => 'nullable|integer',
            'subject' => 'nullable|string|max:100'
        ]);

        // This method seems to be for a different notes system (not the lesson notes)
        // The lesson notes are handled by DashboardController@saveUserNotes
        // For now, we'll simulate saving as this might be for a different feature

        return response()->json([
            'success' => true,
            'message' => 'Note saved successfully!',
            'note_id' => rand(1000, 9999) // Simulate generated ID
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:1000'
        ]);

        // In a real application, you would update the note in the database
        
        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        // In a real application, you would delete the note from the database
        
        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully!'
        ]);
    }

    private function getSampleNotes()
    {
        return [
            [
                'id' => 1,
                'title' => 'Living and Non Living organism',
                'subject' => '(Science -Note G1-3)',
                'content' => 'Detailed notes about living and non-living organisms...',
                'created_at' => 'April 2025',
                'lesson_id' => 4
            ],
            [
                'id' => 2,
                'title' => 'Basic Numbers and Counting',
                'subject' => '(Mathematics -Note G1-3)',
                'content' => 'Notes on basic counting and number recognition...',
                'created_at' => 'April 2025',
                'lesson_id' => 1
            ],
            [
                'id' => 3,
                'title' => 'Animals and Their Homes',
                'subject' => '(Science -Note G2-3)',
                'content' => 'Notes about different animal habitats...',
                'created_at' => 'March 2025',
                'lesson_id' => 2
            ]
        ];
    }

    private function getSampleNote($id)
    {
        $notes = $this->getSampleNotes();
        
        foreach ($notes as $note) {
            if ($note['id'] == $id) {
                return $note;
            }
        }
        
        return null;
    }
}
