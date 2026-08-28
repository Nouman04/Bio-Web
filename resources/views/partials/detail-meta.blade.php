{{-- The facts panel on a detail page: a list of label/value rows, where a
     value may be plain text, a chip, or a link. --}}
<div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
    <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700">
        <h3 class="text-base font-bold text-on-surface dark:text-white">{{ $heading ?? 'Details' }}</h3>
    </div>
    <dl class="p-6 flex flex-col gap-4">
        @foreach($rows as $label => $value)
            <div class="flex flex-col gap-1">
                <dt class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">{{ $label }}</dt>
                <dd class="text-sm text-on-surface dark:text-slate-200">
                    @if(is_array($value) && isset($value['url']))
                        <a href="{{ $value['url'] }}" class="text-primary hover:text-primary-container transition-colors">{{ $value['label'] }}</a>
                    @elseif(is_array($value) && isset($value['chip']))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $value['class'] ?? 'bg-surface-container-low text-on-surface-variant dark:bg-slate-900 dark:text-slate-300' }}">
                            {{ $value['chip'] }}
                        </span>
                    @else
                        {{ $value !== null && $value !== '' ? $value : '—' }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
</div>
