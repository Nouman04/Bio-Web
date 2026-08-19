{{-- Actions column for the courses DataTable --}}
<div class="relative inline-block text-left action-dropdown">
    <button type="button" class="action-dropdown-trigger w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant dark:text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors" title="Actions">
        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
    </button>
    <div class="action-dropdown-menu hidden absolute right-0 z-20 mt-1 w-44 rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-lg py-1">
        <a href="{{ route('courses.chapters', $course) }}" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
            <i class="fa-solid fa-book-bookmark w-4 text-on-surface-variant"></i>
            View Chapters
        </a>
        <a href="{{ route('courses.configuration', $course) }}" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
            <i class="fa-solid fa-sliders w-4 text-on-surface-variant"></i>
            Configuration
        </a>
        @can('edit course')
            <button type="button"
                onclick="openEditCourseModal(this)"
                data-id="{{ $course->uuid }}"
                data-category-id="{{ $course->category_id }}"
                data-title="{{ $course->title }}"
                data-slug="{{ $course->slug }}"
                data-description="{{ $course->description }}"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                <i class="fa-solid fa-pen w-4 text-on-surface-variant"></i>
                Edit
            </button>
        @endcan
        @can('delete course')
            <div class="my-1 border-t border-outline-variant/20 dark:border-slate-700"></div>
            <button type="button"
                onclick="deleteCourse(this)"
                data-id="{{ $course->uuid }}"
                data-title="{{ $course->title }}"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-error hover:bg-error/5 transition-colors">
                <i class="fa-solid fa-trash w-4"></i>
                Delete
            </button>
        @endcan
    </div>
</div>
