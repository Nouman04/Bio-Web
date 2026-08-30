{{-- Extra styles the catalogue mockups rely on, merged from html/public2. --}}
@push("styles")
<style>
        .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-panel-dim { background-color: rgba(255, 255, 255, 0.5); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(0, 0, 0, 0.05); }
        .glass-header { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(30px); -webkit-backdrop-filter: blur(30px); border-bottom: 1px solid rgba(0, 0, 0, 0.03); }
        /* Flip Card Animation Context */ .perspective-1000 { perspective: 1000px; }
        .transform-style-3d { transform-style: preserve-3d; }
        .backface-hidden { backface-visibility: hidden; }
        .rotate-y-180 { transform: rotateY(180deg); }
        .flip-card-inner { transition: transform 0.6s cubic-bezier(0.4, 0.0, 0.2, 1); }
        .flip-card.flipped .flip-card-inner { transform: rotateY(180deg); }
        /* Custom ambient glow */ .ambient-glow::before { content: ''; position: absolute; top: -20%; left: -20%; width: 140%; height: 140%; background: rgba(0, 19, 48, 0.05); z-index: -1; pointer-events: none; }
        .glass-shadow { box-shadow: 0 10px 15px -3px rgba(0, 19, 48, 0.05); }
        .mcq-option-hover:hover { background-color: var(--color-surface-container-low); border-color: var(--color-primary-fixed); box-shadow: 0 4px 6px -1px rgba(0, 19, 48, 0.05); transform: translateY(-2px); }
        .mcq-option-selected { background-color: var(--color-primary-container); color: var(--color-on-primary-container); border-color: var(--color-primary); }
        .text-gradient { color: #001330; }
        .material-symbols-outlined.filled { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        /* Custom scrollbar for a cleaner look */ ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d8dadc; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #b8c8dd; }
        .material-symbols-outlined[data-weight="fill"] { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .icon-filled { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
