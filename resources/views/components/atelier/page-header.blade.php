@props([
    'title' => 'Page Title',
    'subtitle' => null,
    'segment' => null,
    'action' => null,
    'actionUrl' => null,
])

<div class="mb-8">
    <!-- Laser Line -->
    <div class="laser-line laser-line-pulse mb-4">
        <div class="laser-dot laser-dot-left"></div>
        <div class="laser-dot laser-dot-right"></div>
    </div>

    <!-- Header Content -->
    <div class="flex items-end justify-between">
        <div class="flex items-baseline gap-4">
            @if($segment)
                <span class="font-mono text-xs text-gold-500 tracking-widest glow-gold">{{ $segment }}</span>
            @endif
            <div>
                <h1 class="font-serif text-2xl font-bold text-white leading-none">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-xs text-navy-400 mt-1.5 tracking-wide uppercase">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if($action && $actionUrl)
            <a href="{{ $actionUrl }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-gold-500 text-navy-900 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-gold-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                {{ $action }}
            </a>
        @endif
    </div>
</div>