{{--
    The bookmark button, on any listing or detail page that shows content a
    student can keep:

        <x-save-button :record="$note" />

    It renders nothing for content that cannot be saved, or for a visitor who is
    not signed in. Everything saved appears on the resources page.

    `label` shows the word beside the icon; leave it off for the icon-only
    variant used on image tiles.
--}}
@props(['record', 'label' => false, 'class' => ''])

@php
    $key = \App\Models\SavedContent::keyFor($record);

    $saved = $key && auth()->check()
        && \App\Models\SavedContent::where('user_id', auth()->id())
            ->where('contentable_type', $record::class)
            ->where('contentable_id', $record->id)
            ->exists();
@endphp

@if($key && auth()->check())
    <button type="button"
        data-save-content
        data-save-type="{{ $key }}"
        data-save-uuid="{{ $record->uuid }}"
        data-saved="{{ $saved ? '1' : '0' }}"
        aria-pressed="{{ $saved ? 'true' : 'false' }}"
        title="{{ $saved ? 'Saved — click to remove' : 'Save for later' }}"
        {{ $attributes->merge(['class' => 'transition-colors ' . $class]) }}>
        <span class="material-symbols-outlined"
            @if($saved) style="font-variation-settings:'FILL' 1;color:#4648d4;" @endif>
            {{ $saved ? 'bookmark' : 'bookmark_border' }}
        </span>
        @if($label)
            <span data-save-label="{{ $label }}">{{ $saved ? 'Saved' : $label }}</span>
        @endif
    </button>

    @once
        @push('scripts')
        <script>
        // Posts the bookmark and repaints the button from what came back, so
        // the icon never claims something the server did not record.
        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-save-content]');

            if (!button || button.dataset.busy === '1') {
                return;
            }

            event.preventDefault();
            button.dataset.busy = '1';

            fetch(@json(route('student.saved.toggle')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    type: button.dataset.saveType,
                    uuid: button.dataset.saveUuid,
                }),
            })
                .then((response) => (response.ok ? response.json() : null))
                .then((result) => {
                    if (!result) {
                        return;
                    }

                    const icon = button.querySelector('.material-symbols-outlined');
                    const label = button.querySelector('[data-save-label]');

                    button.dataset.saved = result.saved ? '1' : '0';
                    button.setAttribute('aria-pressed', result.saved ? 'true' : 'false');
                    button.title = result.saved ? 'Saved — click to remove' : 'Save for later';

                    icon.textContent = result.saved ? 'bookmark' : 'bookmark_border';
                    icon.style.fontVariationSettings = result.saved ? "'FILL' 1" : "'FILL' 0";
                    icon.style.color = result.saved ? '#4648d4' : '';

                    if (label) {
                        label.textContent = result.saved ? 'Saved' : label.dataset.saveLabel;
                    }
                })
                .finally(() => {
                    button.dataset.busy = '0';
                });
        });
        </script>
        @endpush
    @endonce
@endif
