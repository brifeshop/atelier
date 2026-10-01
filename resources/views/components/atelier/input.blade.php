@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
            {{ $label }}
            @if($required) <span class="text-red-400">*</span> @endif
        </label>
    @endif

    <input 
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition']) }}
    >

    @if($hint)
        <p class="text-xs text-navy-500 mt-1.5">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
    @enderror
</div>