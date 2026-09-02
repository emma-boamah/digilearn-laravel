<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TutorProfile;
use App\Models\TutorSubject;
use App\Models\Subject;
use App\Models\Booking;
use App\Models\Course;
use App\Models\TutorAvailability;
use Illuminate\Support\Facades\Auth;

class TutorController extends Controller
{
    /**
     * Display a listing of tutors.
     */
    public function index(Request $request)
    {
        $query = User::whereHas('tutorProfile', function ($q) {
            $q->where('is_approved', true);
        })->with(['tutorProfile', 'tutorSubjects.subject']);

        if ($request->has('subject_id') && $request->subject_id != '') {
            $query->whereHas('tutorSubjects', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        // Search by tutor name, bio, tagline, or subject name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('tutorProfile', function ($pq) use ($search) {
                      $pq->where('bio', 'like', "%{$search}%")
                         ->orWhere('tagline', 'like', "%{$search}%")
                         ->orWhere('qualifications', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tutorSubjects.subject', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $tutors = $query->paginate(12)->appends($request->query());
        // Only fetch subjects that have at least one approved tutor
        $subjects = Subject::whereHas('tutorSubjects', function ($q) {
            $q->whereHas('user', function ($uq) {
                $uq->whereHas('tutorProfile', function ($tq) {
                    $tq->where('is_approved', true);
                });
            });
        })->get();

        return view('tutors.index', compact('tutors', 'subjects'));
    }

    /**
     * Display the specified tutor profile.
     */
    public function show($tutorId)
    {
        $tutor = User::with(['tutorProfile', 'tutorSubjects.subject'])->findOrFail($tutorId);
        
        if (!$tutor->tutorProfile || !$tutor->tutorProfile->is_approved) {
            abort(404, 'Tutor not found or not approved yet.');
        }

        // Fetch tutor's published public courses
        $courses = Course::with(['videos', 'documents', 'quizzes'])
            ->where('created_by', $tutor->id)
            ->where('status', 'published')
            ->latest()
            ->get();

        // Fetch tutor's available recurring days of week (0 = Sun, 1 = Mon, ..., 6 = Sat)
        $availableDays = TutorAvailability::where('tutor_id', $tutor->id)
            ->where('is_recurring', true)
            ->pluck('day_of_week')
            ->unique()
            ->values()
            ->toArray();

        // Fetch specifically blocked dates from today onwards
        $blockedDates = TutorAvailability::where('tutor_id', $tutor->id)
            ->where('is_blocked', true)
            ->where('specific_date', '>=', now()->toDateString())
            ->pluck('specific_date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        return view('tutors.show', compact('tutor', 'courses', 'availableDays', 'blockedDates'));
    }

    /**
     * Show the form for applying to be a tutor.
     */
    public function apply()
    {
        $user = Auth::user();
        if ($user->tutorProfile) {
            return redirect()->route('tutors.dashboard')
                ->with('info', 'You have already applied to be a tutor.');
        }

        $subjects = Subject::all();
        return view('tutors.apply', compact('subjects'));
    }

    /**
     * Show the enhanced tutor dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;

        if (!$tutorProfile) {
            return redirect()->route('tutors.apply');
        }

        // If pending approval, show simplified pending state view
        if (!$tutorProfile->is_approved) {
            return view('tutors.dashboard', compact('tutorProfile'));
        }

        // Stats calculations for approved tutors
        $totalEarnings = Booking::where('tutor_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('SUM(credits_paid - commission_amount) as total')
            ->value('total') ?? 0.00;

        $activeStudentsCount = Booking::where('tutor_id', $user->id)
            ->distinct('student_id')
            ->count('student_id');

        $upcomingSessions = Booking::with(['student', 'subject'])
            ->where('tutor_id', $user->id)
            ->whereIn('status', ['scheduled', 'confirmed', 'pending_scheduling'])
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();

        $pendingRequestsCount = Booking::where('tutor_id', $user->id)
            ->where('status', 'pending_scheduling')
            ->count();

        // Calculate profile completeness score
        $completenessScore = 0;
        $totalWeight = 6;
        if (!empty($tutorProfile->bio)) $completenessScore++;
        if (!empty($tutorProfile->tagline)) $completenessScore++;
        if (!empty($tutorProfile->headshot_path)) $completenessScore++;
        if (!empty($tutorProfile->intro_video_url)) $completenessScore++;
        if (!empty($tutorProfile->scheduling_link)) $completenessScore++;
        if (!empty($tutorProfile->payout_method)) $completenessScore++;
        $completenessPercentage = round(($completenessScore / $totalWeight) * 100);

        // Recent activity feed
        $recentBookings = Booking::with(['student', 'subject'])
            ->where('tutor_id', $user->id)
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('tutors.dashboard', compact(
            'tutorProfile',
            'totalEarnings',
            'activeStudentsCount',
            'upcomingSessions',
            'pendingRequestsCount',
            'completenessPercentage',
            'recentBookings'
        ));
    }

    /**
     * Store a newly created tutor application.
     */
    public function storeApplication(Request $request)
    {
        $request->validate([
            'legal_name' => 'required|string|max:255',
            'tagline' => 'required|string|max:255',
            'bio' => 'required|string|max:1000',
            'headshot_file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'id_document_file' => 'required|mimes:pdf,jpeg,png,jpg|max:10240',
            'qualifications' => 'required|string|max:1000',
            'certificates_file' => 'nullable|mimes:pdf,jpeg,png,jpg|max:10240',
            'intro_video_url' => 'nullable|url',
            'scheduling_preference' => 'required|in:in_app,external',
            'scheduling_link' => 'required_if:scheduling_preference,external|nullable|url',
            'communication_handle' => 'required|string|max:255',
            'payout_method' => 'required|in:momo,bank',
            'payout_momo_network' => 'required_if:payout_method,momo|nullable|in:MTN,Telecel,AT',
            'payout_momo_number' => 'required_if:payout_method,momo|nullable|string|max:20',
            'payout_bank_name' => 'required_if:payout_method,bank|nullable|string|max:255',
            'payout_bank_account_name' => 'required_if:payout_method,bank|nullable|string|max:255',
            'payout_bank_account_number' => 'required_if:payout_method,bank|nullable|string|max:50',
            'payout_bank_branch' => 'required_if:payout_method,bank|nullable|string|max:255',
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
            'rates' => 'required|array',
            'rates.*' => 'numeric|min:0',
        ]);

        $user = Auth::user();

        if ($user->tutorProfile) {
            return back()->with('error', 'You already have a tutor profile.');
        }

        // Handle file uploads
        $headshotPath = $request->file('headshot_file')->store('tutors/headshots', 'public');
        $idDocumentPath = $request->file('id_document_file')->store('tutors/id_documents', 'local');
        
        $certificatesPath = null;
        if ($request->hasFile('certificates_file')) {
            $certificatesPath = $request->file('certificates_file')->store('tutors/certificates', 'local');
        }

        $profile = TutorProfile::create([
            'user_id' => $user->id,
            'legal_name' => $request->legal_name,
            'tagline' => $request->tagline,
            'bio' => $request->bio,
            'headshot_path' => $headshotPath,
            'id_document_path' => $idDocumentPath,
            'certificates_path' => $certificatesPath,
            'qualifications' => $request->qualifications,
            'intro_video_url' => $request->intro_video_url,
            'scheduling_preference' => $request->scheduling_preference,
            'scheduling_link' => $request->scheduling_preference === 'external' ? $request->scheduling_link : null,
            'communication_handle' => $request->communication_handle,
            'payout_method' => $request->payout_method,
            'payout_momo_network' => $request->payout_momo_network,
            'payout_momo_number' => $request->payout_momo_number,
            'payout_bank_name' => $request->payout_bank_name,
            'payout_bank_account_name' => $request->payout_bank_account_name,
            'payout_bank_account_number' => $request->payout_bank_account_number,
            'payout_bank_branch' => $request->payout_bank_branch,
            'is_approved' => false, // Requires admin approval
            'is_verified' => false,
        ]);

        foreach ($request->subjects as $subjectId) {
            if (isset($request->rates[$subjectId])) {
                TutorSubject::create([
                    'user_id' => $user->id,
                    'subject_id' => $subjectId,
                    'hourly_rate' => $request->rates[$subjectId],
                ]);
            }
        }

        // Assign 'tutor' role if it exists using Spatie Permissions
        try {
            $user->assignRole('tutor');
        } catch (\Exception $e) {
            // Role might not exist, ignore for MVP or log
        }

        // Notify all super admins and admins about new tutor application
        try {
            $admins = User::where('is_superuser', true)
                ->orWhere('is_admin', true)
                ->orWhereHas('roles', function ($q) {
                    $q->whereIn('name', ['super-admin', 'restricted-admin']);
                })
                ->get();

            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\TutorApplicationSubmittedNotification($profile));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to notify admins of tutor application: " . $e->getMessage());
        }

        return redirect()->route('tutors.dashboard')
            ->with('success', 'Your tutor application has been submitted and is pending review.');
    }

    /**
     * Profile settings page for tutors.
     */
    public function profileSettings()
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;

        if (!$tutorProfile) {
            return redirect()->route('tutors.apply');
        }

        $allSubjects = Subject::all();
        $tutorSubjects = TutorSubject::where('user_id', $user->id)->pluck('hourly_rate', 'subject_id')->toArray();

        return view('tutors.profile-settings', compact('user', 'tutorProfile', 'allSubjects', 'tutorSubjects'));
    }

    /**
     * Update tutor profile settings.
     */
    public function updateProfileSettings(Request $request)
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;

        if (!$tutorProfile) {
            return redirect()->route('tutors.apply');
        }

        $request->validate([
            'legal_name' => 'required|string|max:255',
            'tagline' => 'required|string|max:255',
            'bio' => 'required|string|max:1000',
            'qualifications' => 'required|string|max:1000',
            'intro_video_url' => 'nullable|url',
            'scheduling_preference' => 'required|in:in_app,external',
            'scheduling_link' => 'required_if:scheduling_preference,external|nullable|url',
            'communication_handle' => 'required|string|max:255',
            'payout_method' => 'required|in:momo,bank',
            'payout_momo_network' => 'required_if:payout_method,momo|nullable|in:MTN,Telecel,AT',
            'payout_momo_number' => 'required_if:payout_method,momo|nullable|string|max:20',
            'payout_bank_name' => 'required_if:payout_method,bank|nullable|string|max:255',
            'payout_bank_account_name' => 'required_if:payout_method,bank|nullable|string|max:255',
            'payout_bank_account_number' => 'required_if:payout_method,bank|nullable|string|max:50',
            'payout_bank_branch' => 'required_if:payout_method,bank|nullable|string|max:255',
            'headshot_file' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'rates' => 'nullable|array',
            'rates.*' => 'numeric|min:0',
        ]);

        $updateData = [
            'legal_name' => $request->legal_name,
            'tagline' => $request->tagline,
            'bio' => $request->bio,
            'qualifications' => $request->qualifications,
            'intro_video_url' => $request->intro_video_url,
            'scheduling_preference' => $request->scheduling_preference,
            'scheduling_link' => $request->scheduling_preference === 'external' ? $request->scheduling_link : null,
            'communication_handle' => $request->communication_handle,
            'payout_method' => $request->payout_method,
            'payout_momo_network' => $request->payout_momo_network,
            'payout_momo_number' => $request->payout_momo_number,
            'payout_bank_name' => $request->payout_bank_name,
            'payout_bank_account_name' => $request->payout_bank_account_name,
            'payout_bank_account_number' => $request->payout_bank_account_number,
            'payout_bank_branch' => $request->payout_bank_branch,
        ];

        if ($request->hasFile('headshot_file')) {
            $updateData['headshot_path'] = $request->file('headshot_file')->store('tutors/headshots', 'public');
        }

        $tutorProfile->update($updateData);

        // Sync subjects and rates
        if ($request->has('subjects')) {
            TutorSubject::where('user_id', $user->id)->delete();
            foreach ($request->subjects as $subjectId) {
                if (isset($request->rates[$subjectId])) {
                    TutorSubject::create([
                        'user_id' => $user->id,
                        'subject_id' => $subjectId,
                        'hourly_rate' => $request->rates[$subjectId],
                    ]);
                }
            }
        }

        return back()->with('success', 'Profile settings updated successfully.');
    }
}
