@props([
    'label',
    'value',
    'desc' => null,
    'trend' => null,
    'trendType' => 'neutral', // up, down, neutral
    'accent' => false,
    'icon' => null,
    'href' => null,
])

@php
    $trendColors = [
        'up' => 'text-green-400 bg-green-500/10',
        'down' => 'text-red-400 bg-red-500/10',
        'neutral' => 'text-navy-400 bg-navy-500/10',
    ];
@endphp

<a href="{{ $href ?? '#' }}" 
   class="group relative {{ $accent ? 'bg-gradient-to-br from-gold-500/10 to-navy-900/50 border-gold-500/30 hover:border-gold-500' : 'bg-navy-900/50 border-navy-800 hover:border-gold-500/50' }} border rounded-xl p-5 transition-all duration-300 overflow-hidden block">
    
    <div class="flex items-start justify-between mb-3">
        @if($icon)
            <div class="w-9 h-9 rounded-lg {{ $accent ? 'bg-gold-500/20' : 'bg-navy-800 group-hover:bg-gold-500/20' }} flex items-center justify-center transition">
                <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
        @else
            <div></div>
        @endif

        @if($trend)
            <span class="text-[10px] font-semibold {{ $trendColors[$trendType] }} px-2 py-0.5 rounded-full font-mono">
                {{ $trend }}
            </span>
        @endif
    </div>

    <p class="font-serif {{ $accent ? 'text-2xl' : 'text-3xl' }} font-bold text-white leading-none">{{ $value }}</p>
    <p class="text-xs {{ $accent ? 'text-gold-500/70' : 'text-navy-400' }} uppercase tracking-wider mt-2">{{ $label }}</p>
    
    @if($desc)
        <p class="text-xs text-navy-500 mt-2">{{ $desc }}</p>
    @endif
</a>