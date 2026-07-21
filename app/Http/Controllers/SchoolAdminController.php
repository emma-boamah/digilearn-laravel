<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SchoolAdminController extends Controller
{
    /**
     * Get the active school and authorize access.
     */
    protected function getSchool()
    {
        $user = Auth::user();
        $school = app()->has('tenant') ? app('tenant') : $user->school;

        // If no school is resolved from tenant or user, check if user is a super-admin
        if (!$school && ($user->is_superuser || $user->hasRole('super-admin'))) {
            $schoolId = request()->get('school_id');
            if ($schoolId) {
                $school = School::find($schoolId);
            } else {
                // Default to the first school to prevent 403 when a super-admin first visits the page
                $school = School::first();
            }
        }

        if (!$school) {
            abort(403, 'You are not associated with a school.');
        }

        // Allow superusers to bypass the school_id restriction
        if ($user->school_id !== $school->id && !($user->is_superuser || $user->hasRole('super-admin'))) {
            abort(403, 'Unauthorized access to this school.');
        }

        return $school;
    }

    /**
     * School Admin Dashboard Overview.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $school = $this->getSchool();

        if (!$school) {
            abort(403, 'You are not associated with a school.');
        }

        $studentIds = User::where('school_id', $school->id)->role('student')->pluck('id');
        $totalStudents = $studentIds->count();
        $totalTeachers = User::where('school_id', $school->id)->role('teacher')->count();
        $totalAdmins = User::where('school_id', $school->id)->role('school-admin')->count();

        $recentUsers = User::where('school_id', $school->id)
            ->latest()
            ->take(10)
            ->get();

        // Analytics Data
        $totalQuizzesTaken = \App\Models\QuizAttempt::whereIn('user_id', $studentIds)->count();
        $averageScore = \App\Models\QuizAttempt::whereIn('user_id', $studentIds)->avg('score_percentage') ?? 0;
        
        $learningTimeSeconds = \App\Models\UserProgress::whereIn('user_id', $studentIds)->sum('total_time_spent_seconds');
        $learningTimeHours = round($learningTimeSeconds / 3600, 1);

        // Subject Performance for Chart
        $subjectPerformance = \App\Models\QuizAttempt::whereIn('user_id', $studentIds)
            ->selectRaw('quiz_subject, AVG(score_percentage) as average_score, COUNT(*) as attempts')
            ->groupBy('quiz_subject')
            ->orderByDesc('attempts')
            ->take(5)
            ->get();

        return view('schools.admin.dashboard', compact(
            'school', 'totalStudents', 'totalTeachers', 'totalAdmins', 'recentUsers',
            'totalQuizzesTaken', 'averageScore', 'learningTimeHours', 'subjectPerformance'
        ));
    }

    /**
     * School Settings Page.
     */
    public function settings()
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        return view('schools.admin.settings', compact('school'));
    }

    /**
     * Update school settings.
     */
    public function updateSettings(Request $request)
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_details' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'contact_details' => $request->contact_details,
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            if ($school->logo && Storage::disk('public')->exists($school->logo)) {
                Storage::disk('public')->delete($school->logo);
            }

            $path = $request->file('logo')->store('school-logos', 'public');
            $data['logo'] = $path;
        }

        $school->update($data);

        return redirect()->route('school.admin.settings')->with('success', 'School settings updated successfully.');
    }

    /**
     * Users Management Page.
     */
    public function users(Request $request)
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        $query = User::where('school_id', $school->id);

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->appends($request->query());

        return view('schools.admin.users', compact('school', 'users'));
    }

    /**
     * Show the invite user form.
     */
    public function showInviteForm()
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        return view('schools.admin.invite', compact('school'));
    }

    /**
     * Handle user invitation / creation.
     */
    public function inviteUser(Request $request)
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|in:teacher,student',
            'password' => ['required', Password::defaults()],
        ]);

        if ($request->role === 'student' && $school->isOverLimit()) {
            return back()->withInput()->withErrors(['role' => 'You have reached your seat limit. Please upgrade your plan or remove inactive students.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school_id' => $school->id,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('school.admin.users')->with('success', "{$request->name} has been added as a {$request->role}.");
    }

    /**
     * Remove a user from the school.
     */
    public function removeUser(User $user)
    {
        $school = $this->getSchool();

        if (!$school || $user->school_id !== $school->id) {
            abort(403);
        }

        // Prevent removing yourself
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot remove yourself.');
        }

        $user->update(['school_id' => null]);
        $user->removeRole($user->roles->first()?->name);

        return back()->with('success', "{$user->name} has been removed from the school.");
    }

    /**
     * Show the bulk import form.
     */
    public function showImportForm()
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        return view('schools.admin.import', compact('school'));
    }

    /**
     * Download CSV template.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="shoutoutgh_users_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'email', 'role', 'password']);
            fputcsv($file, ['John Doe', 'john@school.edu', 'student', 'secret123']);
            fputcsv($file, ['Jane Smith', 'jane@school.edu', 'teacher', 'secret123']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle CSV import.
     */
    public function importUsers(Request $request)
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $data = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');
            // Normalize header
            $header = array_map('strtolower', array_map('trim', $header));
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($header) === count($row)) {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        $successCount = 0;
        $errors = [];
        $remainingSeats = $school->max_seats >= 99999 ? PHP_INT_MAX : $school->remainingSeats();

        foreach ($data as $index => $row) {
            $rowNum = $index + 2; // +1 for 0-index, +1 for header

            if (empty($row['name']) || empty($row['email']) || empty($row['role']) || empty($row['password'])) {
                $errors[] = "Row {$rowNum}: Missing required fields.";
                continue;
            }

            $role = strtolower(trim($row['role']));
            if (!in_array($role, ['student', 'teacher'])) {
                $errors[] = "Row {$rowNum}: Invalid role. Must be 'student' or 'teacher'.";
                continue;
            }

            if ($role === 'student') {
                if ($remainingSeats <= 0) {
                    $errors[] = "Row {$rowNum}: Skipped. Seat limit reached.";
                    continue;
                }
                $remainingSeats--;
            }

            if (User::where('email', trim($row['email']))->exists()) {
                $errors[] = "Row {$rowNum}: Email '{$row['email']}' already exists.";
                continue;
            }

            try {
                $user = User::create([
                    'name' => trim($row['name']),
                    'email' => trim($row['email']),
                    'password' => Hash::make(trim($row['password'])),
                    'school_id' => $school->id,
                ]);

                $user->assignRole($role);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: Failed to create user.";
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('school.admin.users.import')->with('error', "Imported {$successCount} users. Errors encountered:<br>" . implode('<br>', $errors));
        }

        return redirect()->route('school.admin.users')->with('success', "Successfully imported {$successCount} users.");
    }

    /**
     * Show Academic Setup Page (Years, Terms, Classes).
     */
    public function academicSetup()
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        $academicYears = \App\Models\AcademicYear::where('school_id', $school->id)->with('terms')->get();
        $schoolClasses = \App\Models\SchoolClass::where('school_id', $school->id)->with('level')->get();
        $levels = \App\Models\Level::all();

        return view('schools.admin.academic-setup', compact('school', 'academicYears', 'schoolClasses', 'levels'));
    }

    /**
     * Store a new Academic Year.
     */
    public function storeAcademicYear(Request $request)
    {
        $school = $this->getSchool();

        $request->validate([
            'year_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ]);

        if ($request->is_active) {
            // Deactivate others
            \App\Models\AcademicYear::where('school_id', $school->id)->update(['is_active' => false]);
        }

        \App\Models\AcademicYear::create([
            'school_id' => $school->id,
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active ?? false,
        ]);

        return back()->with('success', 'Academic Year added successfully.');
    }

    /**
     * Store a new Term.
     */
    public function storeTerm(Request $request)
    {
        $school = $this->getSchool();

        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Verify the academic year belongs to this school
        $year = \App\Models\AcademicYear::where('id', $request->academic_year_id)
            ->where('school_id', $school->id)
            ->firstOrFail();

        \App\Models\AcademicTerm::create([
            'academic_year_id' => $year->id,
            'term_name' => $request->term_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return back()->with('success', 'Term added successfully.');
    }

    /**
     * Store a new School Class.
     */
    public function storeClass(Request $request)
    {
        $school = $this->getSchool();

        $request->validate([
            'name' => 'required|string|max:255',
            'level_id' => 'required|exists:levels,id',
        ]);

        \App\Models\SchoolClass::create([
            'school_id' => $school->id,
            'name' => $request->name,
            'level_id' => $request->level_id,
        ]);

        return back()->with('success', 'Class added successfully.');
    }

    /**
     * Show Subscription & Billing page.
     */
    public function billing()
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        // Get all payments from school admin users
        $adminIds = User::where('school_id', $school->id)->role('school-admin')->pluck('id');
        $payments = \App\Models\Payment::whereIn('user_id', $adminIds)->latest()->take(20)->get();

        return view('schools.admin.billing', compact('school', 'payments'));
    }

    /**
     * Show the renewal checkout page.
     */
    public function renewalForm()
    {
        $school = $this->getSchool();

        if (!$school) {
            abort(403);
        }

        $usedSeats = $school->usedSeats();
        $pricePerSeat = $school->price_per_seat;
        $totalAmount = $usedSeats * $pricePerSeat;

        return view('schools.admin.renew', compact('school', 'usedSeats', 'pricePerSeat', 'totalAmount'));
    }
}
