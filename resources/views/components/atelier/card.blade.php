@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-6',
    'brackets' => true,
])

<div {{ $attributes->merge(['class' => 'bg-navy-900/50 border border-navy-800 rounded-xl overflow-hidden relative']) }}>
    
    @if($brackets)
        <div class="corner-bracket corner-bracket-tl"></div>
        <div class="corner-bracket corner-bracket-tr"></div>
        <div class="corner-bracket corner-bracket-bl"></div>
        <div class="corner-bracket corner-bracket-br"></div>
    @endif

    @if($title)
        <div class="px-5 py-4 border-b border-navy-800">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-1.5 rounded-full bg-gold-500" style="box-shadow: 0 0 8px #C9A961;"></div>
                <div>
                    <h3 class="font-serif text-lg font-bold text-white">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-xs text-navy-400 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>