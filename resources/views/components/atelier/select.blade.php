@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => '-- Pilih --',
    'required' => false,
])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
            {{ $label }}
            @if($required) <span class="text-red-400">*</span> @endif
        </label>
    @endif

    <select 
        name="{{ $name }}"
        id="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition']) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $key => $label)
            <option value="{{ $key }}" @selected(old($name, $value) == $key)>{{ $label }}</option>
        @endforeach
    </select>

    @error($name)
        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
    @enderror
</div>