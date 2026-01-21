@extends('layouts.app')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
.payment-container { max-width: 1200px; margin: 0 auto; margin-top: 68px; padding: 2rem 1rem; }
.payment-card { max-width: 28rem; margin: 0 auto; background-color: white; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1); padding: 1.5rem; text-align: center; }
.icon-container { margin-bottom: 1rem; }
.cancel-icon { display: block; margin: 0 auto; color: #ef4444; }
.payment-title { font-size: 1.5rem; font-weight: bold; color: #111827; margin-bottom: 0.5rem; }
.payment-text { color: #4b5563; margin-bottom: 1.5rem; }
.button-container { display: flex; flex-direction: column; gap: 0.75rem; }
.btn-primary { display: block; width: 100%; background-color: #2563eb; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; transition: background-color 0.2s; }
.btn-primary:hover { background-color: #1d4ed8; }
.btn-secondary { display: block; width: 100%; background-color: #e5e7eb; color: #1f2937; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; transition: background-color 0.2s; }
.btn-secondary:hover { background-color: #d1d5db; }
</style>
<div class="payment-container">
    <div class="payment-card">
        <div class="icon-container">
            <svg class="cancel-icon" width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1 class="payment-title">Payment Cancelled</h1>
        <p class="payment-text">Your payment was cancelled. No charges were made to your account.</p>
        <div class="button-container">
            <a href="{{ route('pricing') }}" class="btn-primary">
                Try Again
            </a>
            <a href="{{ route('dashboard.main') }}" class="btn-secondary">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection