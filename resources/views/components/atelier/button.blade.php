@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'icon' => null,
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-gold-500 text-navy-900 hover:bg-gold-400 shadow-lg shadow-gold-500/20',
        'secondary' => 'bg-navy-800 text-white hover:bg-navy-700 border border-navy-700',
        'ghost' => 'text-navy-300 hover:text-white hover:bg-navy-800',
        'danger' => 'bg-red-500 text-white hover:bg-red-600',
        'success' => 'bg-green-500 text-white hover:bg-green-600',
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    
    $classes = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition-all duration-200 ' 
        . $variants[$variant] . ' ' . $sizes[$size];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif