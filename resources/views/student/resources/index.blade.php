@extends('layouts.student')

@section('title', 'My Resources Central Hub')
@section('meta-description', 'Everything you have saved — videos, flashcards, notes, summaries, diagrams and guides')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
</style>
@endpush

@php
    // A saved video or diagram has no cover image, so its tile is a gradient
    // keyed off the record — stable per item, and no placeholder stock art.
    $palettes = [
        ['#4648d4', '#7c3aed'], ['#0891b2', '#4648d4'], ['#c026d3', '#7c3aed'],
        ['#059669', '#0891b2'], ['#ea580c', '#c026d3'],
    ];
    $tint = fn ($record) => $palettes[$record->id % count($palettes)];

    // Which panels to draw: all of them, or just the one being filtered to.
    $panels = $type ? [$type] : array_keys(\App\Models\SavedContent::PRESENTATION);
    $filterUrl = fn ($key) => route('student.resources', ['type' => $key]);
@endphp

@section('content')
<div class="flex flex-col gap-6 pt-4">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-on-surface mb-2" style="font-size:32px;line-height:40px;letter-spacing:-0.01em;font-weight:600;">My Resources Central Hub</h2>
            <p class="text-on-surface-variant" style="font-size:18px;line-height:28px;">
                @if($total === 0)
                    Bookmark anything as you study and it will collect here.
                @else
                    {{ $total }} saved {{ Str::plural('item', $total) }}, ready when you are.
                @endif
            </p>
        </div>
        @if($type)
            <a href="{{ route('student.resources') }}"
                class="text-primary hover:underline text-sm font-semibold inline-flex items-center gap-1 shrink-0">
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                Show everything
            </a>
        @endif
    </div>

    @if($total === 0)
        <div class="bg-surface-container-lowest rounded-xl p-6 border border-outline-variant/30 shadow-sm py-20 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">bookmark_border</span>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2">Nothing saved yet</h3>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">
                Tap the bookmark on any note, flashcard set, diagram, summary, video or guide and it will appear here.
            </p>
            <a href="{{ route('student.courses') }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                Go to my courses
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    @else
        <!-- Bento Grid: one panel per kind that has something saved -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach($panels as $key)
                @php $items = $groups[$key] ?? collect(); @endphp
                @continue($items->isEmpty())

                @php $shape = \App\Models\SavedContent::PRESENTATION[$key]; @endphp

                <div class="bg-surface-container-lowest rounded-xl p-6 hover-lift border border-outline-variant/30 flex flex-col h-full shadow-sm {{ $key === 'note' || $key === 'summary' ? 'xl:col-span-2' : '' }}">
                    <div class="flex justify-between items-center mb-6 gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                <span class="material-symbols-outlined">{{ $shape['icon'] }}</span>
                            </div>
                            {{-- A section heading is always plural, however many
                                 it happens to hold. --}}
                            <h3 class="text-on-surface truncate" style="font-size:20px;line-height:28px;font-weight:600;">
                                {{ Str::plural($shape['label']) }}
                            </h3>
                            <span class="text-on-surface-variant text-sm shrink-0">{{ $items->count() }}</span>
                        </div>
                        @unless($type)
                            <a class="text-primary hover:underline text-sm font-semibold shrink-0" href="{{ $filterUrl($key) }}">View All</a>
                        @endunless
                    </div>

                    @if($key === 'video' || $key === 'diagram')
                        {{-- Tiles, the way the mockup showed media. --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                            @foreach($items as $item)
                                @php $record = $item->contentable; $palette = $tint($record); @endphp
                                <a href="{{ $item->url }}"
                                    class="group relative rounded-lg overflow-hidden border border-outline-variant/30 block">
                                    <div class="aspect-video relative"
                                        @if($key === 'diagram' && $record->image_url)
                                            style="background-image:url('{{ $record->image_url }}');background-size:cover;background-position:center;"
                                        @else
                                            style="background-image:linear-gradient(135deg, {{ $palette[0] }}, {{ $palette[1] }});"
                                        @endif>
                                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                            <span class="material-symbols-outlined text-white text-4xl opacity-80 group-hover:opacity-100 transition-opacity" style="font-variation-settings: 'FILL' 1;">
                                                {{ $key === 'video' ? 'play_arrow' : 'account_tree' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-surface-container-lowest">
                                        <h4 class="text-on-surface text-sm font-semibold line-clamp-1 mb-1">{{ $record->title }}</h4>
                                        <p class="text-on-surface-variant text-xs line-clamp-1">{{ $record->chapter->title }} &bull; {{ $record->chapter->course->title }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                    @elseif($key === 'flashcard')
                        {{-- Compact deck cards, two across. --}}
                        <div class="grid grid-cols-2 gap-4 flex-1">
                            @foreach($items as $item)
                                @php $record = $item->contentable; @endphp
                                <a href="{{ $item->url }}"
                                    class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-5 hover:border-secondary-container transition-colors shadow-sm block">
                                    <h4 class="text-on-surface text-sm font-semibold mb-2 line-clamp-2">{{ $record->title }}</h4>
                                    <p class="text-on-surface-variant text-xs">{{ $record->assessments()->count() }} Cards</p>
                                </a>
                            @endforeach
                        </div>

                    @elseif($key === 'note' || $key === 'summary')
                        {{-- A table, as the mockup listed documents. --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-outline-variant/20 text-on-surface-variant text-xs font-medium">
                                        <th class="py-3 px-2">Document Name</th>
                                        <th class="py-3 px-2 hidden sm:table-cell">Course</th>
                                        <th class="py-3 px-2">Saved</th>
                                        <th class="py-3 px-2 text-right"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        @php $record = $item->contentable; @endphp
                                        <tr class="border-b border-outline-variant/10 hover:bg-surface-container-low transition-colors">
                                            <td class="py-4 px-2">
                                                <a href="{{ $item->url }}" class="flex items-center gap-3 group">
                                                    <span class="material-symbols-outlined text-primary text-xl shrink-0">{{ $shape['icon'] }}</span>
                                                    <span class="text-on-surface text-sm font-medium group-hover:text-primary transition-colors">{{ $record->title }}</span>
                                                </a>
                                            </td>
                                            <td class="py-4 px-2 text-on-surface-variant text-sm hidden sm:table-cell">{{ $record->chapter->course->title }}</td>
                                            <td class="py-4 px-2 text-on-surface-variant text-sm">{{ $item->created_at?->diffForHumans() }}</td>
                                            <td class="py-4 px-2 text-right">
                                                <x-save-button :record="$record"
                                                    class="text-on-surface-variant hover:text-primary hover:bg-primary-container/10 p-2 rounded-full inline-flex" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @else
                        {{-- Guides: a plain row list. --}}
                        <div class="flex flex-col gap-4 flex-1">
                            @foreach($items as $item)
                                @php $record = $item->contentable; @endphp
                                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex justify-between items-center gap-3 hover:bg-surface-container-low transition-colors shadow-sm">
                                    <a href="{{ $item->url }}" class="min-w-0 group">
                                        <h4 class="text-on-surface text-sm font-semibold mb-1 truncate group-hover:text-primary transition-colors">{{ $record->title }}</h4>
                                        <p class="text-on-surface-variant text-xs truncate">{{ $record->chapter->title }} &bull; {{ $record->chapter->course->title }}</p>
                                    </a>
                                    <x-save-button :record="$record" class="text-on-surface-variant hover:text-primary shrink-0" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Everything here is on the page because it is saved; unsaving should take
    // it away rather than leave a card that no longer belongs.
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-save-content]');

        if (!button) {
            return;
        }

        const row = button.closest('tr') || button.closest('.rounded-xl');

        const observer = new MutationObserver(() => {
            if (button.dataset.saved === '0') {
                observer.disconnect();
                row?.remove();
                window.location.reload();
            }
        });

        observer.observe(button, { attributes: true, attributeFilter: ['data-saved'] });
        setTimeout(() => observer.disconnect(), 5000);
    });
</script>
@endpush
@endsection
