@extends('layouts.app')

@section('title', $video->title)
@section('meta-description', 'Video lesson details.')

@section('page-title', $video->title)
@section('page-subtitle', $video->chapter?->title ?? 'Video lesson')

@section('content')
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('videos') }}">Video Lessons</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold truncate max-w-xs">{{ $video->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 flex flex-col gap-6">
            {{-- An uploaded file plays inline; an external link is offered as a
                 link, since it may be hosted anywhere. --}}
            <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm overflow-hidden">
                @if($video->file_path)
                    <video class="w-full max-h-[560px] bg-black" controls preload="metadata" src="{{ $video->video_url }}"></video>
                @elseif($video->external_link)
                    <div class="p-10 text-center">
                        <i class="fa-solid fa-up-right-from-square text-2xl text-primary mb-3"></i>
                        <p class="text-sm text-on-surface-variant dark:text-slate-400 mb-4">This lesson is hosted elsewhere.</p>
                        <a href="{{ $video->external_link }}" target="_blank" rel="noopener"
                            class="px-6 py-2.5 rounded-full bg-primary text-on-primary text-sm font-semibold inline-flex items-center gap-2 hover:bg-primary/95 transition-colors">
                            <i class="fa-solid fa-circle-play text-xs"></i>
                            Watch the lesson
                        </a>
                    </div>
                @else
                    <div class="p-16 text-center text-on-surface-variant dark:text-slate-400">
                        <i class="fa-solid fa-circle-play text-3xl mb-2"></i>
                        <p class="text-sm">No video on file.</p>
                    </div>
                @endif
            </div>

            @if($video->description)
                <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                    <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700">
                        <h3 class="text-base font-bold text-on-surface dark:text-white">Description</h3>
                    </div>
                    <div class="p-6 prose max-w-none text-sm text-on-surface dark:text-slate-200 leading-relaxed [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-primary">
                        {!! $video->description !!}
                    </div>
                </div>
            @endif

            @include('partials.detail-questions', ['questions' => $questions])
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            @include('partials.detail-meta', ['rows' => [
                'Chapter' => $video->chapter?->title,
                'Topic' => $video->topic?->title,
                'Source' => ['chip' => $video->file_path ? 'Uploaded' : 'External link'],
                'Slug' => $video->slug,
                'Link' => $video->external_link ? ['url' => $video->external_link, 'label' => 'Open source'] : null,
                'Added by' => $video->addedBy?->name,
                'Created' => $video->created_at?->format('M j, Y'),
                'Last updated' => $video->updated_at?->diffForHumans(),
            ]])

            <a href="{{ route('videos', ['title' => $video->title, 'open' => $video->uuid]) }}"
                class="w-full px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-pen text-xs"></i>
                Edit lesson
            </a>
        </div>
    </div>
@endsection
