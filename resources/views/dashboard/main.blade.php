@extends('layouts.dashboard-simple')

@section('content')
    <!-- Selected Level Info -->
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .level-info-row { display: flex; justify-content: space-between; align-items: center; }
        .level-info-text { color: var(--text-muted); font-size: 0.875rem; }
        .level-info-strong { color: var(--primary-red); }
        .card-button-link { display: inline-block; text-decoration: none; text-align: center; }
    </style>
    <div class="level-info-container">
        <div class="container">
            <div class="level-info-row">
                <span class="level-info-text">
                    Selected Level: <strong class="level-info-strong">{{ $selectedLevelGroup ? ucwords(str_replace('-', ' ', $selectedLevelGroup)) : 'None' }}</strong>
                </span>
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
                        <img src="{{ asset('images/digilrn.jpeg') }}" alt="DigiLearn">
                    </div>
                    <h3 class="card-title">DigiLearn</h3>
                    <p class="card-description">
                        Practical, Demonstrative, Educative, Informative and Edutainment lessons which aids students to understand topics and with ease 21st Century tutoring approach
                    </p>
                    <a href="{{ route('dashboard.digilearn') }}" class="card-button card-button-link">
                        Start Lessons
                    </a>
                </div>

                <!-- Personalized Learning Section -->
                <div class="card">
                    <div class="card-image">
                        <img src="{{ asset('images/personalized.jpeg') }}" alt="Personalized Learning">
                    </div>
                    <h3 class="card-title">Personalized learning</h3>
                    <p class="card-description">
                        Learn at your own pace with our tutors and explore more educative videos on personalized learning. Get the chance to schedule time with tutors.
                    </p>
                    <a href="{{ route('tutors.index') }}" class="card-button card-button-link">
                        Start
                    </a>
                </div>

                <!-- Shop Section -->
                <div class="card">
                    <div class="card-image">
                        <img src="{{ asset('images/digishop.jpeg') }}" alt="Shop">
                    </div>
                    <h3 class="card-title">Shop</h3>
                    <p class="card-description">
                        Purchase all your student needs here. Items are affordable and drastically reduced to suit your financial equilibrium.
                    </p>
                    <a href="{{ route('coming-soon') }}" class="card-button card-button-link">
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