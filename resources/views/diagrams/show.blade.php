@extends('layouts.app')

@section('title', $diagram->title)
@section('meta-description', 'Diagram details.')

@section('page-title', $diagram->title)
@section('page-subtitle', $diagram->chapter?->title ?? 'Diagram')

@section('content')
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('diagrams') }}">Diagrams</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold truncate max-w-xs">{{ $diagram->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 flex flex-col gap-6">
            {{-- The image --}}
            <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm overflow-hidden">
                @if($diagram->image_url)
                    <a href="{{ $diagram->image_url }}" target="_blank" rel="noopener" class="block bg-surface-container-low dark:bg-slate-900">
                        <img src="{{ $diagram->image_url }}" alt="{{ $diagram->title }}" class="w-full max-h-[560px] object-contain mx-auto">
                    </a>
                @else
                    <div class="p-16 text-center text-on-surface-variant dark:text-slate-400">
                        <i class="fa-regular fa-image text-3xl mb-2"></i>
                        <p class="text-sm">No image on file.</p>
                    </div>
                @endif
            </div>

            @if($diagram->content)
                <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                    <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700">
                        <h3 class="text-base font-bold text-on-surface dark:text-white">Description</h3>
                    </div>
                    <div class="p-6 prose max-w-none text-sm text-on-surface dark:text-slate-200 leading-relaxed [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-primary">
                        {!! $diagram->content !!}
                    </div>
                </div>
            @endif

            @include('partials.detail-questions', ['questions' => $questions])
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            @include('partials.detail-meta', ['rows' => [
                'Chapter' => $diagram->chapter?->title,
                'Topic' => $diagram->topic?->title,
                'Slug' => $diagram->slug,
                'Image' => $diagram->image_url ? ['url' => $diagram->image_url, 'label' => 'Open full size'] : null,
                'Added by' => $diagram->addedBy?->name,
                'Created' => $diagram->created_at?->format('M j, Y'),
                'Last updated' => $diagram->updated_at?->diffForHumans(),
            ]])

            <a href="{{ route('diagrams', ['title' => $diagram->title, 'open' => $diagram->uuid]) }}"
                class="w-full px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-pen text-xs"></i>
                Edit diagram
            </a>
        </div>
    </div>
@endsection
