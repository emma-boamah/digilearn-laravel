@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Performance Analytics</h2>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        <!-- Top Metrics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Profile Views</span>
                    <i class="fa-solid fa-eye" style="color: #3b82f6; font-size: 1.2rem;"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); margin: 0;">{{ number_format($profileViews) }}</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Intro plays: {{ $videoPlays }}</span>
            </div>

            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Conversion Rate</span>
                    <i class="fa-solid fa-chart-line" style="color: #10b981; font-size: 1.2rem;"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); margin: 0;">{{ $conversionRate }}%</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Views to Bookings</span>
            </div>

            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Average Rating</span>
                    <i class="fa-solid fa-star" style="color: #f59e0b; font-size: 1.2rem;"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); margin: 0;">{{ number_format($avgRating, 1) }} / 5.0</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">From {{ $totalRatings }} ratings</span>
            </div>

            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Course Enrollments</span>
                    <i class="fa-solid fa-graduation-cap" style="color: #8b5cf6; font-size: 1.2rem;"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); margin: 0;">{{ number_format($totalEnrollments) }}</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Across {{ $publishedCourses }} courses</span>
            </div>
        </div>

        <!-- Charts Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Revenue Trend Chart -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem;">6-Month Revenue Trend (GHS)</h3>
                <canvas id="revenueTrendChart" height="220"></canvas>
            </div>

            <!-- Bookings Trend Chart -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem;">Monthly Bookings Volume</h3>
                <canvas id="bookingsTrendChart" height="220"></canvas>
            </div>
        </div>

        <!-- Booking Breakdown & Top Subjects -->
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
            <!-- Booking Status Card -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem;">Session Distribution</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                        <span style="font-weight: 600; color: var(--text-main);"><i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 0.5rem;"></i> Completed</span>
                        <span style="font-weight: 700; color: var(--text-main);">{{ $completedBookings }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                        <span style="font-weight: 600; color: var(--text-main);"><i class="fa-solid fa-clock" style="color: #3b82f6; margin-right: 0.5rem;"></i> Upcoming</span>
                        <span style="font-weight: 700; color: var(--text-main);">{{ $upcomingBookings }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; color: var(--text-main);"><i class="fa-solid fa-circle-xmark" style="color: #ef4444; margin-right: 0.5rem;"></i> Cancelled</span>
                        <span style="font-weight: 700; color: var(--text-main);">{{ $cancelledBookings }}</span>
                    </div>
                </div>
            </div>

            <!-- Subject Stats Table -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem;">Top Tutored Subjects</h3>
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
                            <th style="padding: 0.75rem 0.5rem;">Subject</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center;">Bookings</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjectStats as $subject)
                            <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-main);">
                                <td style="padding: 0.85rem 0.5rem; font-weight: 600;">{{ $subject->subject }}</td>
                                <td style="padding: 0.85rem 0.5rem; text-align: center;">{{ $subject->count }}</td>
                                <td style="padding: 0.85rem 0.5rem; text-align: right; font-weight: 700; color: #10b981;">GHS {{ number_format($subject->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding: 1.5rem; text-align: center; color: var(--text-muted);">No subject booking data recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Integration -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const months = {!! json_encode($months) !!};
            const revenueTrend = {!! json_encode($revenueTrend) !!};
            const bookingsTrend = {!! json_encode($bookingsTrend) !!};

            // Revenue Chart
            const ctxRev = document.getElementById('revenueTrendChart').getContext('2d');
            new Chart(ctxRev, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Earnings (GHS)',
                        data: revenueTrend,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // Bookings Volume Chart
            const ctxBookings = document.getElementById('bookingsTrendChart').getContext('2d');
            new Chart(ctxBookings, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Bookings',
                        data: bookingsTrend,
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
@endsection
