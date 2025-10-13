<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
        ]);

        // Here you would typically process the contact form
        // For example, send an email or save to database

        return redirect()->route('contact')->with('success', 'Thank you for your message. We will get back to you soon!');
    }

    public function submitFeedback(Request $request)
    {
        $validated = $request->validate([
            'feedback' => 'required|string',
        ]);

        // Process feedback submission

        return redirect()->route('contact')->with('success', 'Thank you for your feedback!');
    }
}
