<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use App\Models\User;
use App\Notifications\AdminNotification;
use App\Models\Feedback;
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

        $feedback = Feedback::create([
            'user_id' => $user->id,
            'type' => 'contact',
            'message' => $validated['message'],
            'status' => 'pending',
            'metadata' => [
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        ]);

        try {
            Mail::to('contact@shoutoutgh.com')->send(new ContactFormMail($data));
            $feedback->update(['status' => 'sent']);
            
            // Notify admins of success
            $this->notifyAdminsOfSuccess($user, 'Contact Form', $data['message']);

            return redirect()->route('contact')->with('success', 'Thank you for your message. We will get back to you soon!');
        } catch (\Exception $e) {
            $feedback->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

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
            'name' => $user->name,
        ]);

        $feedback = Feedback::create([
            'user_id' => $user->id,
            'type' => 'feedback',
            'message' => $validated['feedback'],
            'status' => 'pending',
            'metadata' => [
                'email' => $user->email,
                'name' => $user->name,
            ]
        ]);

        try {
            Mail::to('contact@shoutoutgh.com')->send(new ContactFormMail($data));
            $feedback->update(['status' => 'sent']);

            // Notify admins of success
            $this->notifyAdminsOfSuccess($user, 'Feedback Form', $data['feedback']);

            return redirect()->route('contact')->with('success', 'Thank you for your feedback!');
        } catch (\Exception $e) {
            $feedback->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

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
        try {
            app(\App\Services\AuthSecurityAlertService::class)->handleMailFailure(
                source: $source,
                recipientEmail: 'contact@shoutoutgh.com',
                errorMessage: 'Form submission mail delivery failure',
                context: [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'excerpt' => substr($content, 0, 100),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Could not notify admins of contact mail failure: ' . $e->getMessage());
        }
    }

    /**
     * Notify admins of a successful feedback/contact submission.
     */
    private function notifyAdminsOfSuccess($user, $source, $content)
    {
        $title = "New {$source} Received";
        $mailSearchUrl = "https://mail.zoho.com/zm/#search/from:{$user->email}";
        $message = "You have received a new message from {$user->name} ({$user->email}). You can also check the message at: {$mailSearchUrl}";

        foreach ($this->getAdmins() as $admin) {
            $admin->notify(new AdminNotification($title, $message, $mailSearchUrl));
        }
    }

    /**
     * Get the list of super admins and restricted admins.
     */
    private function getAdmins()
    {
        return User::where('is_superuser', true)
            ->orWhereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'restricted-admin']);
            })
            ->get();
    }
}
