@props([
    'selectedLevel' => null,
    'class' => ''
])

@php
    $displayName = $selectedLevel ? ([
        'primary-lower' => 'Grade 1-3',
        'primary-upper' => 'Grade 4-6',
        'jhs' => 'Grade 7-9',
        'shs' => 'Grade 10-12',
        'university' => 'University'
    ][$selectedLevel] ?? ucwords(str_replace('-', ' ', $selectedLevel))) : 'Grade 1-3';

    $defaultClasses = 'level-indicator';
    $allClasses = trim($defaultClasses . ' ' . $class);
@endphp

<div class="level-container">
    <a href="{{ route('dashboard.level-selection') }}" class="{{ $allClasses }}">
        {{ $displayName }}
    </a>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
.level-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}

.level-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.875rem 1.25rem;
    background-color: var(--white, #ffffff);
    border: 2px solid var(--gray-200, #e5e7eb);
    border-radius: 0.75rem;
    color: var(--gray-700, #374151);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    min-width: 140px;
    height: 43px;
    text-decoration: none;
}

/* Mobile: Compact level indicator */
@media (max-width: 768px) {
    .level-indicator {
        padding: 0.75rem 0.75rem;
        font-size: 0.7rem;
        min-width: 80px;
        gap: 0.25rem;
    }

    .level-indicator svg {
        width: 12px;
        height: 12px;
        margin-left: 0.25rem;
    }
}

.level-indicator:hover {
    border-color: var(--secondary-blue, #2677B8);
    background-color: rgba(38, 119, 184, 0.05);
    color: var(--secondary-blue, #2677B8);
    box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
    text-decoration: none;
}
</style>