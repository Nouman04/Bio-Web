{{--
    The payment-method popup.

    Include once on any page that has a subscribe button, then give the button
    `data-open-payment`:

        @include('public._payment-modal', ['course' => $course, 'plan' => $plan ?? $course->plan])

    Stripe is the only gateway wired up; PayFast is shown as what is coming so
    the choice is honest rather than a dead button that looks live.
--}}
@php
    $plan = $plan ?? $course?->plan;
@endphp

<div id="payment-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4"
    role="dialog" aria-modal="true" aria-labelledby="payment-modal-title">

    {{-- Backdrop; clicking it closes. --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-close-payment></div>

    <div class="relative w-full max-w-md bg-surface-container-low border border-glass-stroke rounded-xl shadow-2xl p-6 md:p-8"
        id="payment-modal-panel">

        <button type="button" data-close-payment
            class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface transition-colors"
            aria-label="Close">
            <span class="material-symbols-outlined">close</span>
        </button>

        <h2 id="payment-modal-title" class="font-headline-md text-headline-md text-on-surface mb-1">Choose how to pay</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mb-6">
            {{ $course?->title }}
            @if($plan?->price !== null)
                — <span class="text-on-surface font-semibold" data-plan-price>{{ $plan->formatted_price }}</span> per <span data-plan-interval>{{ $plan->billing_interval }}</span>
            @endif
        </p>

        <div class="space-y-3">
            {{-- Stripe: the live one. --}}
            <a data-stripe-checkout href="{{ route('public.subscribe.checkout', $course) }}"
                class="group flex items-center gap-4 w-full p-4 rounded-lg border-2 border-primary/30 bg-primary/5 hover:border-primary hover:bg-primary/10 transition-all">
                <span class="flex items-center justify-center w-11 h-11 rounded-lg bg-[#635bff] text-white shrink-0">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">credit_card</span>
                </span>
                <span class="flex-1 text-left">
                    <span class="block font-label-md text-label-md text-on-surface font-semibold">Pay with Stripe</span>
                    <span class="block font-body-sm text-body-sm text-on-surface-variant">Card, Apple Pay and Google Pay</span>
                </span>
                <span class="material-symbols-outlined text-primary group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>

            {{-- PayFast: not wired up yet, and said so plainly. --}}
            <div class="flex items-center gap-4 w-full p-4 rounded-lg border border-outline-variant/40 opacity-60 cursor-not-allowed"
                aria-disabled="true">
                <span class="flex items-center justify-center w-11 h-11 rounded-lg bg-surface-container-high text-on-surface-variant shrink-0">
                    <span class="material-symbols-outlined">account_balance</span>
                </span>
                <span class="flex-1 text-left">
                    <span class="block font-label-md text-label-md text-on-surface font-semibold">Pay with PayFast</span>
                    <span class="block font-body-sm text-body-sm text-on-surface-variant">Instant EFT and local cards</span>
                </span>
                <span class="font-label-sm text-label-sm text-on-surface-variant border border-outline-variant/60 rounded-full px-2 py-0.5 whitespace-nowrap">
                    Coming soon
                </span>
            </div>
        </div>

        <p class="mt-6 text-center font-label-sm text-label-sm text-on-surface-variant">
            Payment is handled by the provider. Cancel any time.
        </p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const modal = document.getElementById('payment-modal');

    if (!modal) {
        return;
    }

    const panel = document.getElementById('payment-modal-panel');
    let lastFocused = null;

    function open(event) {
        if (event) {
            event.preventDefault();
        }

        // The plan page offers monthly and yearly; carry whichever is
        // selected through to Stripe.
        const chosen = document.querySelector('.plan-interval:checked');
        const link = modal.querySelector('[data-stripe-checkout]');

        if (chosen && link) {
            const url = new URL(link.href, window.location.origin);
            url.searchParams.set('interval', chosen.value);
            link.href = url.toString();

            const row = chosen.closest('label');
            const price = modal.querySelector('[data-plan-price]');
            const interval = modal.querySelector('[data-plan-interval]');
            if (price && row) { price.textContent = row.querySelector('.text-headline-md')?.textContent.trim() ?? price.textContent; }
            if (interval) { interval.textContent = chosen.value; }
        }

        lastFocused = document.activeElement;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Stops the page behind scrolling under the dialog.
        document.body.style.overflow = 'hidden';
        panel.querySelector('a, button')?.focus();
    }

    function close() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        lastFocused?.focus();
    }

    document.querySelectorAll('[data-open-payment]').forEach((trigger) => {
        trigger.addEventListener('click', open);
    });

    modal.querySelectorAll('[data-close-payment]').forEach((trigger) => {
        trigger.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            close();
        }
    });
})();
</script>
@endpush
