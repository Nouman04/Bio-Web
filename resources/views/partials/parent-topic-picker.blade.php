{{--
    Parent topic picker.

    A type-ahead over every topic in the system, not just this chapter's — a
    topic often continues one introduced elsewhere. Each result carries the
    chapter (and course) it belongs to, so it is clear where a parent comes
    from when two chapters use similar wording.

    Posts `parent_topic_id`, or nothing when left empty.
--}}
<div class="parent-topic-picker flex flex-col gap-1.5"
     data-search-url="{{ route('topics.search', [$course, $chapter]) }}"
     data-field-name="{{ $ptFieldName ?? 'parent_topic_id' }}">
    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
        Parent Topic <span class="font-normal text-outline">(Optional)</span>
    </label>
    <select class="parent-topic-select" placeholder="Search topics in any chapter…" autocomplete="off"></select>
    <p class="text-[11px] text-on-surface-variant/70 dark:text-slate-500">
        Leave empty for a top-level topic. Parents may live in another chapter.
    </p>
</div>
