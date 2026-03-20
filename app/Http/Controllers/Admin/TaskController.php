<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Http\Requests\Admin\TaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('super-admin')) {
            $tasks = Task::with(['assignee', 'creator'])->latest()->paginate(10);
        } else {
            $tasks = Task::where('assignee_id', $user->id)
                        ->with(['assignee', 'creator'])
                        ->latest()
                        ->paginate(10);
        }

        return view('admin.tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Task::class);

        // Only show users who can be assigned tasks (e.g., have the restricted-admin role or any admin role)
        $admins = User::role('restricted-admin')->orWhere('is_superuser', true)->get();
        return view('admin.tasks.create', compact('admins'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        $this->authorize('create', Task::class);

        $data = $request->validated();
        $data['creator_id'] = Auth::id();
        
        Task::create($data);

        return redirect()->route('admin.tasks.index')->with('success', 'Task assigned successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $admins = User::role('restricted-admin')->orWhere('is_superuser', true)->get();
        return view('admin.tasks.edit', compact('task', 'admins'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validated();
        
        // If not super-admin, only allow updating status
        if (!Auth::user()->hasRole('super-admin')) {
            $task->update(['status' => $data['status']]);
        } else {
            $task->update($data);
        }

        return redirect()->route('admin.tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted successfully.');
    }
}
