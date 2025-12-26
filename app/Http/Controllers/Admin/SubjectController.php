<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = Subject::withCount(['videos', 'primaryVideos', 'quizzes'])->orderBy('name')->paginate(10);

        return view('admin.subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.subjects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Subject::create($request->only(['name', 'description']));

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $subject->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $subject->update($request->only(['name', 'description']));

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        // Check if subject is being used
        $videosCount = $subject->videos()->count() + $subject->primaryVideos()->count();
        $quizzesCount = $subject->quizzes()->count();

        if ($videosCount > 0 || $quizzesCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete subject that is being used by content.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted successfully!');
    }
}
