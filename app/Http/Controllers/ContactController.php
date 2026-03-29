<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Support\Facades\Log;

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

        try {
            Mail::to('contact@shoutoutgh.com')->send(new ContactFormMail($data));
            return redirect()->route('contact')->with('success', 'Thank you for your message. We will get back to you soon!');
        } catch (\Exception $e) {
            Log::error('Mail API error during contact submission: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'error_details' => $e->getMessage()
            ]);

            // Notify admins
            $this->notifyAdminsOfMailFailure($user, 'Contact Form', $data['message']);

            return redirect()->route('contact')->with('error', 'Failed to submit message. Please try again later.');
        }
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

        try {
            Mail::to('contact@shoutoutgh.com')->send(new ContactFormMail($data));
            return redirect()->route('contact')->with('success', 'Thank you for your feedback!');
        } catch (\Exception $e) {
            Log::error('Mail API error during feedback submission: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'error_details' => $e->getMessage()
            ]);

            // Notify admins
            $this->notifyAdminsOfMailFailure($user, 'Feedback Form', $data['feedback']);

            return redirect()->route('contact')->with('error', 'Failed to submit feedback. Please try again later.');
        }
    }

    /**
     * Notify admins of a mail service failure.
     */
    private function notifyAdminsOfMailFailure($user, $source, $content)
    {
        $admins = User::where('is_superuser', true)
            ->orWhereHas('roles', function($q) {
                $q->whereIn('name', ['super-admin', 'restricted-admin']);
            })
            ->get();

        $title = "Mail Service Failure: {$source}";
        $message = "A mail submission from {$user->name} ({$user->email}) failed due to a service error (likely Zoho credits exhausted). Content: " . substr($content, 0, 100) . "...";

        foreach ($admins as $admin) {
            $admin->notify(new AdminNotification($title, $message));
        }
    }
}
