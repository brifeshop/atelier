@props([
    'name',
    'options' => [],
    'value' => null,
    'placeholder' => '-- Pilih --',
    'searchPlaceholder' => 'Cari...',
    'required' => false,
    'label' => null,
])

@php
    // Format options: ['id' => 'Label', 'data-*' => 'value']
    $formattedOptions = [];
    foreach ($options as $key => $opt) {
        if (is_array($opt)) {
            $formattedOptions[] = [
                'value' => (string) $key,
                'label' => $opt['label'] ?? $key,
                'data' => $opt['data'] ?? [],
            ];
        } else {
            $formattedOptions[] = [
                'value' => (string) $key,
                'label' => $opt,
                'data' => [],
            ];
        }
    }
@endphp

<div x-data="searchableSelect({
    options: {{ Js::from($formattedOptions) }},
    modelValue: '{{ old($name, $value) }}',
    name: '{{ $name }}'
})" x-init="init()" class="relative">

    @if($label)
        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
            {{ $label }}
            @if($required) <span class="text-red-400">*</span> @endif
        </label>
    @endif

    {{-- Hidden input untuk form submit --}}
    <input type="hidden" :name="name" :value="selected">

    {{-- Display --}}
    <div @click="toggle()" @keydown.escape="close()"
         class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer focus-within:border-gold-500 focus-within:ring-2 focus-within:ring-gold-500/20 transition flex items-center justify-between"
         :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">

        <span x-show="selected" x-text="selectedLabel" class="truncate"></span>
        <span x-show="!selected" class="text-navy-500">{{ $placeholder }}</span>

        <svg class="w-4 h-4 text-navy-500 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute z-50 mt-1 w-full bg-navy-800 border border-navy-700 rounded-lg shadow-xl overflow-hidden">

        {{-- Search Input --}}
        <div class="p-2 border-b border-navy-700">
            <input type="text" x-model="search" x-ref="searchInput"
                   @keydown.arrow-down.prevent="highlightNext()"
                   @keydown.arrow-up.prevent="highlightPrev()"
                   @keydown.enter.prevent="selectHighlighted()"
                   @keydown.escape="close()"
                   placeholder="{{ $searchPlaceholder }}"
                   class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-0 focus:outline-none">
        </div>

        {{-- Options --}}
        <div class="max-h-64 overflow-y-auto">
            <template x-if="filteredOptions.length === 0">
                <div class="px-3 py-4 text-center text-sm text-navy-500">
                    Tidak ada hasil
                </div>
            </template>

            <template x-for="(opt, index) in filteredOptions" :key="opt.value">
                <div @click="select(opt)"
                     @mouseenter="highlighted = index"
                     class="px-3 py-2 text-sm cursor-pointer transition flex items-center justify-between"
                     :class="{
                         'bg-gold-500 text-navy-900': highlighted === index,
                         'text-white hover:bg-navy-700': highlighted !== index && selected === opt.value,
                         'text-navy-300 hover:bg-navy-700': highlighted !== index && selected !== opt.value,
                     }">
                    <span x-text="opt.label"></span>
                    <svg x-show="selected === opt.value" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </template>
        </div>
    </div>

    {{-- Click outside handler --}}
    <div x-show="open" @click="close()" class="fixed inset-0 z-40"></div>
</div>

@push('scripts')
<script>
    function searchableSelect({ options, modelValue, name }) {
        return {
            options: options,
            selected: modelValue || '',
            search: '',
            open: false,
            highlighted: 0,
            name: name,

            init() {
                // Watch selected, dispatch event agar parent bisa dengar
                this.$watch('selected', (value) => {
                    this.$dispatch('select-changed', { value, name: this.name });
                });
            },

            get selectedLabel() {
                const opt = this.options.find(o => o.value === this.selected);
                return opt ? opt.label : '';
            },

            get filteredOptions() {
                if (!this.search) return this.options;
                const q = this.search.toLowerCase();
                return this.options.filter(o => o.label.toLowerCase().includes(q));
            },

            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.search = '';
                    this.highlighted = 0;
                    this.$nextTick(() => this.$refs.searchInput.focus());
                }
            },

            close() {
                this.open = false;
                this.search = '';
            },

            select(opt) {
                this.selected = opt.value;
                this.close();
            },

            highlightNext() {
                if (this.highlighted < this.filteredOptions.length - 1) {
                    this.highlighted++;
                }
            },

            highlightPrev() {
                if (this.highlighted > 0) {
                    this.highlighted--;
                }
            },

            selectHighlighted() {
                if (this.filteredOptions[this.highlighted]) {
                    this.select(this.filteredOptions[this.highlighted]);
                }
            },
        };
    }
</script>
@endpush