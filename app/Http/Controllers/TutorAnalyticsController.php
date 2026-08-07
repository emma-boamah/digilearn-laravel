<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\TutorProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TutorAnalyticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;

        if (!$tutorProfile || !$tutorProfile->is_approved) {
            return redirect()->route('tutors.index')->with('error', 'Only approved tutors can access analytics.');
        }

        // 1. Core Profile Stats
        $profileViews = $tutorProfile->profile_views_count ?? 0;
        $videoPlays = $tutorProfile->intro_video_plays_count ?? 0;
        $responseRate = $tutorProfile->response_rate ?? 100.00;

        // 2. Booking Stats
        $totalBookings = Booking::where('tutor_id', $user->id)->count();
        $completedBookings = Booking::where('tutor_id', $user->id)->where('status', 'completed')->count();
        $upcomingBookings = Booking::where('tutor_id', $user->id)->whereIn('status', ['scheduled', 'confirmed', 'pending_scheduling'])->count();
        $cancelledBookings = Booking::where('tutor_id', $user->id)->where('status', 'cancelled')->count();

        $conversionRate = $profileViews > 0 ? round(($totalBookings / $profileViews) * 100, 1) : 0;

        // Ratings
        $avgRating = Booking::where('tutor_id', $user->id)->whereNotNull('student_rating')->avg('student_rating') ?? 5.0;
        $totalRatings = Booking::where('tutor_id', $user->id)->whereNotNull('student_rating')->count();

        // 3. Course Content Stats
        $myCourses = Course::where('tutor_id', $user->id)->get();
        $totalCourses = $myCourses->count();
        $publishedCourses = $myCourses->where('status', 'published')->count();
        $totalEnrollments = CourseEnrollment::whereIn('course_id', $myCourses->pluck('id'))->count();

        // 4. Monthly Revenue & Booking Trends (last 6 months)
        $months = [];
        $revenueTrend = [];
        $bookingsTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthName = $monthDate->format('M Y');
            $months[] = $monthName;

            $monthlyRev = Booking::where('tutor_id', $user->id)
                ->where('status', 'completed')
                ->whereYear('updated_at', $monthDate->year)
                ->whereMonth('updated_at', $monthDate->month)
                ->selectRaw('SUM(credits_paid - commission_amount) as net')
                ->value('net') ?? 0.00;

            $monthlyCount = Booking::where('tutor_id', $user->id)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();

            $revenueTrend[] = (float) $monthlyRev;
            $bookingsTrend[] = $monthlyCount;
        }

        // 5. Subject Breakdown
        $subjectStats = Booking::with('subject')
            ->select('subject_id', DB::raw('count(*) as count'), DB::raw('sum(credits_paid - commission_amount) as revenue'))
            ->where('tutor_id', $user->id)
            ->whereNotNull('subject_id')
            ->groupBy('subject_id')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'subject' => $item->subject->name ?? 'General',
                    'count' => $item->count,
                    'revenue' => $item->revenue ?? 0.00,
                ];
            });

        return view('tutors.analytics', compact(
            'tutorProfile',
            'profileViews',
            'videoPlays',
            'responseRate',
            'totalBookings',
            'completedBookings',
            'upcomingBookings',
            'cancelledBookings',
            'conversionRate',
            'avgRating',
            'totalRatings',
            'totalCourses',
            'publishedCourses',
            'totalEnrollments',
            'months',
            'revenueTrend',
            'bookingsTrend',
            'subjectStats'
        ));
    }

    public function apiStats()
    {
        $user = Auth::user();
        
        $views = $user->tutorProfile->profile_views_count ?? 0;
        $completed = Booking::where('tutor_id', $user->id)->where('status', 'completed')->count();
        $totalRev = Booking::where('tutor_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('SUM(credits_paid - commission_amount) as net')
            ->value('net') ?? 0.00;

        return response()->json([
            'profile_views' => $views,
            'completed_sessions' => $completed,
            'total_earnings' => (float) $totalRev,
        ]);
    }
}
