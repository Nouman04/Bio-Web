@extends('layouts.student')

@section('title', 'Demo')
@section('page-title', 'Demo')
@section('page-subtitle', 'Sidenav + header shell for layout testing.')

@section('content')
<div class="pt-6 flex flex-col items-center justify-center text-center gap-6 min-h-[60vh]">

    <div class="w-20 h-20 rounded-2xl bg-primary/10 flex items-center justify-center">
        <span class="material-symbols-outlined text-primary" style="font-size:36px;">widgets</span>
    </div>

    <div>
        <h3 class="text-2xl sm:text-3xl font-bold text-on-background dark:text-white">test</h3>
        <p class="text-on-surface-variant dark:text-slate-400 text-sm mt-2 max-w-sm mx-auto">
            This page only wires up the sidenav and header. Resize the window, rotate to landscape, or tap the menu icon on mobile to try the responsive shell.
        </p>
    </div>

    {{-- Interactive controls to sanity-check hover / press / toggle states --}}
    <div class="flex flex-wrap items-center justify-center gap-3 mt-2">
        <button id="demoCounterBtn"
            class="flex items-center gap-2 bg-primary text-on-primary text-sm font-semibold px-5 py-2.5 rounded-full shadow-sm hover:shadow-md hover:scale-105 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            Clicked <span id="demoCounterValue">0</span> times
        </button>

        <button id="demoToggleBtn"
            class="flex items-center gap-2 bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 text-on-surface-variant dark:text-slate-300 text-sm font-semibold px-5 py-2.5 rounded-full shadow-sm hover:text-primary hover:scale-105 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-[18px]" id="demoToggleIcon">toggle_off</span>
            <span id="demoToggleLabel">Off</span>
        </button>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let count = 0;
        const counterBtn = document.getElementById('demoCounterBtn');
        const counterValue = document.getElementById('demoCounterValue');
        counterBtn?.addEventListener('click', () => {
            count++;
            counterValue.textContent = count;
        });

        const toggleBtn = document.getElementById('demoToggleBtn');
        const toggleIcon = document.getElementById('demoToggleIcon');
        const toggleLabel = document.getElementById('demoToggleLabel');
        toggleBtn?.addEventListener('click', () => {
            const isOn = toggleBtn.classList.toggle('is-on');
            toggleIcon.textContent = isOn ? 'toggle_on' : 'toggle_off';
            toggleIcon.style.color = isOn ? '#4648d4' : '';
            toggleLabel.textContent = isOn ? 'On' : 'Off';
        });
    });
</script>
@endpush
@endsection
