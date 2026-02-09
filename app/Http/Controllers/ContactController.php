<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $user = auth()->user();
        $data = array_merge($validated, [
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        Mail::to('contact@shoutoutgh.com')->send(new ContactFormMail($data));

        return redirect()->route('contact')->with('success', 'Thank you for your message. We will get back to you soon!');
    }

    public function submitFeedback(Request $request)
    {
        $validated = $request->validate([
            'feedback' => 'required|string',
        ]);

        $user = auth()->user();
        $data = array_merge($validated, [
            'email' => $user->email,
        ]);

        Mail::to('contact@shoutoutgh.com')->send(new ContactFormMail($data));

        return redirect()->route('contact')->with('success', 'Thank you for your feedback!');
    }
}
