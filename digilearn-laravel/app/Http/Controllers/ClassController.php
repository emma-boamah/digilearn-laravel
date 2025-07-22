<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Models\VirtualClass;
use App\Notifications\ClassStartedNotification;
use Illuminate\Support\Facades\Auth;
class ClassController extends Controller
{
    //
    public function startClass(Request $request)
    {
        // Validate grade level
        $roomId = Str::random(10); // Generate unique room ID
        
        $virtualClass = VirtualClass::create([
            'tutor_id' => Auth::id(),
            'grade_level' => $request->grade_level,
            'room_id' => $roomId
        ]);
        
        // Notify students
        $students = User::where('grade', $request->grade_level)->get();
        Notification::send($students, new ClassStartedNotification($virtualClass));
        
        return redirect()->route('classroom.show', $roomId);
    }
}
