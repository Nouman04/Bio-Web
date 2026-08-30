@props([
    'name',
    'label' => 'File',
    'hint' => null,
    'accept' => null,
    'multiple' => false,
    'required' => false,
    'prompt' => null,
    'current' => null,
    'id' => null,
])

{{--
    A file input with a drop area and, underneath, the name of whatever was
    picked. The names are rendered by the shared behaviour in app-ajax.js, which
    watches every file input on the page — this component only marks where the
    list belongs.

    `current` is the name of the file already stored, so an edit form can say
    what is attached before anything new is chosen.
--}}
<div class="flex flex-col gap-1.5">
    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
        {{ $label }}
        @unless($required)
            <span class="font-normal text-outline">(Optional)</span>
        @endunless
    </label>

    <div data-file-field
        class="relative group border-2 border-dashed border-outline-variant/60 dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-5 cursor-pointer overflow-hidden">
        <div class="w-10 h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform duration-300">
            <i class="fa-solid fa-cloud-arrow-up"></i>
        </div>
        <p class="text-sm font-semibold text-on-surface dark:text-white">
            {{ $prompt ?? ($multiple ? 'Click or drag files to this area to upload' : 'Click or drag a file to this area to upload') }}
        </p>
        @if($hint)
            <p class="text-xs font-medium text-outline dark:text-slate-500 mt-1">{{ $hint }}</p>
        @endif

        <input type="file"
            @if($id) id="{{ $id }}" @endif
            name="{{ $multiple ? $name . '[]' : $name }}"
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            @if($required) required @endif
            @if($current) data-current-name="{{ $current }}" @endif
            {{ $attributes->merge(['class' => 'absolute inset-0 w-full h-full opacity-0 cursor-pointer']) }}>
    </div>
</div>
