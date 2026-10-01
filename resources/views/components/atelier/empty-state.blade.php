@props([
    'title' => 'Belum ada data',
    'subtitle' => 'Mulai dengan menambahkan data pertama.',
    'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
    'action' => null,
    'actionUrl' => null,
])

<div class="p-12 text-center">
    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-navy-800/50 flex items-center justify-center border border-navy-700">
        <svg class="w-10 h-10 text-navy-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
        </svg>
    </div>
    <p class="font-serif text-lg text-white mb-2">{{ $title }}</p>
    <p class="text-sm text-navy-400 mb-6">{{ $subtitle }}</p>

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