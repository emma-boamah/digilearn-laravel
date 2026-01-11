@extends('layouts.dashboard-simple')

@section('content')
    <!-- Selected Level Info -->
    <div class="level-info-container">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: var(--gray-600); font-size: 0.875rem;">
                    Selected Level: <strong style="color: var(--primary-red);">{{ $selectedLevelGroup ? ucwords(str_replace('-', ' ', $selectedLevelGroup)) : 'None' }}</strong>
                </span>
                <a href="{{ route('dashboard.change-level') }}" style="color: var(--secondary-blue); font-size: 0.875rem; text-decoration: none;">
                    {{ $selectedLevelGroup ? 'Change Level' : 'Select Level' }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="three-column-grid">
                <!-- DigiLearn Section -->
                <div class="card">
                    <div class="card-image">
                        <img src="https://via.placeholder.com/400x200/9CA3AF/ffffff?text=DigiLearn" alt="DigiLearn">
                    </div>
                    <h3 class="card-title">DigiLearn</h3>
                    <p class="card-description">
                        Practical, Demonstrative, Educative, Informative and Edutainment lessons which aids students to understand topics and with ease 21st Century tutoring approach
                    </p>
                    <a href="{{ route('dashboard.digilearn') }}" class="card-button" style="display: inline-block; text-decoration: none; text-align: center;">
                        Start Lessons
                    </a>
                </div>

                <!-- Personalized Learning Section -->
                <div class="card">
                    <div class="card-image">
                        <img src="https://via.placeholder.com/400x200/3B82F6/ffffff?text=Personalized+Learning" alt="Personalized Learning">
                    </div>
                    <h3 class="card-title">Personalized learning</h3>
                    <p class="card-description">
                        Learn at your own pace with our tutors and explore more educative videos on personalized learning. Get the chance to schedule time with tutors.
                    </p>
                    <a href="{{ route('dashboard.personalized') }}" class="card-button" style="display: inline-block; text-decoration: none; text-align: center;">
                        Start
                    </a>
                </div>

                <!-- Shop Section -->
                <div class="card">
                    <div class="card-image">
                        <img src="https://via.placeholder.com/400x200/3B82F6/ffffff?text=Shop" alt="Shop">
                    </div>
                    <h3 class="card-title">Shop</h3>
                    <p class="card-description">
                        Purchase all your student needs here. Items are affordable and drastically reduced to suit your financial equilibrium.
                    </p>
                    <a href="{{ route('dashboard.shop') }}" class="card-button" style="display: inline-block; text-decoration: none; text-align: center;">
                        Shop now
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Debug function to test route
            function testDigiLearnRoute() {
                console.log('DigiLearn route:', '{{ route('dashboard.digilearn') }}');
                console.log('Current URL:', window.location.href);
            }

            // Call debug function
            testDigiLearnRoute();
        })

    </script>
@endsection