@extends('layouts.app')

@section('content')
<div class="coming-soon-container" style="min-height: 80vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <i class="fa-solid fa-rocket" style="font-size: 4rem; color: var(--primary-red);"></i>
    </div>
    
    <h1 style="font-size: 3rem; font-weight: 800; color: var(--gray-900); margin-bottom: 1rem; letter-spacing: -0.05em;">
        Coming Soon
    </h1>
    
    <p style="font-size: 1.25rem; color: var(--gray-600); max-width: 600px; margin-bottom: 2.5rem; line-height: 1.6;">
        We're working hard to bring you this amazing feature. It will be available very soon!
    </p>

    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('dashboard.main') }}" class="btn-primary" style="padding: 1rem 2rem; border-radius: 2rem; font-weight: 600; text-decoration: none; background-color: var(--primary-red); color: white; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(225, 30, 45, 0.2);">
            Back to Dashboard
        </a>
    </div>
</div>
@endsection
