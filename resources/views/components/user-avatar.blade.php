@props([
    'user' => null,
    'size' => 40,
    'class' => '',
    'showName' => false,
    'nameClass' => 'text-sm text-gray-700 ml-2',
    'logout' => false
])

@php
    $avatarUrl = $user ? $user->getAvatarWithFallback($size) : "https://ui-avatars.com/api/?name=User&size={$size}&background=random&color=fff&bold=true";
    $userName = $user->name ?? 'User';
    $defaultClasses = 'rounded-full object-cover border border-gray-200';
    $allClasses = trim($defaultClasses . ' ' . $class);
@endphp

@if($logout)
    <form action="{{ route('logout') }}" method="POST" class="flex items-center">
        @csrf
        <button type="submit" class="flex items-center border-none bg-transparent p-0 cursor-pointer">
            <img
                src="{{ $avatarUrl }}"
                alt="{{ $userName }}"
                class="{{ $allClasses }}"
                style="width: {{ $size }}px; height: {{ $size }}px;"
                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($userName) }}&size={{ $size }}&background=random&color=fff&bold=true'"
            />

            @if($showName && $user)
                <span class="{{ $nameClass }}">{{ $userName }}</span>
            @endif
        </button>
    </form>
@else
    <div class="flex items-center">
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $userName }}"
            class="{{ $allClasses }}"
            style="width: {{ $size }}px; height: {{ $size }}px;"
            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($userName) }}&size={{ $size }}&background=random&color=fff&bold=true'"
        />

        @if($showName && $user)
            <span class="{{ $nameClass }}">{{ $userName }}</span>
        @endif
    </div>
@endif
