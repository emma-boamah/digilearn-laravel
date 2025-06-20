<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function index()
    {
        // In a real application, you would fetch notes from the database
        $notes = $this->getSampleNotes();
        
        return view('dashboard.notes', compact('notes'));
    }

    public function view($id)
    {
        // In a real application, you would fetch the specific note from the database
        $note = $this->getSampleNote($id);
        
        if (!$note) {
            return redirect()->route('dashboard.notes')->with('error', 'Note not found.');
        }
        
        return view('dashboard.notes.view', compact('note'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'lesson_id' => 'nullable|integer',
            'subject' => 'nullable|string|max:100'
        ]);

        // In a real application, you would save to the database
        // For now, we'll simulate saving and return success
        
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
            'content' => 'required|string'
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
