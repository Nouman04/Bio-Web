{{--
    Update Price.

    Saving does not overwrite what the course cost before — a new entry is
    written and the newest one is what is charged from now on, which is what the
    history below the form is. See CourseService::reprice().
--}}
<div id="price-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closePriceModal()"></div>

    <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
        <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
            <div class="min-w-0">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Update Price</h3>
                <p id="price-modal-course" class="text-xs text-on-surface-variant dark:text-slate-400 truncate"></p>
            </div>
            <button type="button" onclick="closePriceModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="price-form" data-ajax-form action="#" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="price-interval">Billing</label>
                    <select id="price-interval" name="billing_interval" required data-label="Billing"
                        class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option value="month">Per month</option>
                        <option value="year">Per year</option>
                    </select>
                    <p class="text-xs text-outline dark:text-slate-500">Each term is priced separately.</p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="price-amount">Price (USD)</label>
                    <input id="price-amount" name="price" type="number" step="0.01" min="0.5" max="999999" required
                        data-label="Price" placeholder="e.g. 19.00"
                        class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                    <p id="price-current" class="text-xs text-outline dark:text-slate-500">Not on sale yet.</p>
                </div>
            </div>

            {{-- The offer running against this price. Optional as a whole:
                 without a code, nothing else here is stored. --}}
            <div class="flex flex-col gap-3 pt-3 border-t border-outline-variant/20 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-on-surface dark:text-slate-200">Promo code</p>
                        <p class="text-xs text-on-surface-variant dark:text-slate-400">An offer belongs to the price it discounts.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input id="price-promo-toggle" type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-container-high dark:bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div id="price-promo-fields" class="hidden flex-col gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="price-promo-code">Code</label>
                            <input id="price-promo-code" name="promo_code" type="text" maxlength="60"
                                data-label="Promo code" placeholder="e.g. SPRING25"
                                pattern="[A-Za-z0-9_\-]+"
                                data-pattern-message="A promo code can only use letters, numbers, dashes and underscores."
                                class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm uppercase focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="price-promo-type">Discount</label>
                            <select id="price-promo-type" name="promo_type" data-label="Discount"
                                class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                                @foreach(\App\Models\CoursePrice::PROMO_TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="price-promo-value">
                                Amount <span id="price-promo-unit" class="font-normal text-outline">(%)</span>
                            </label>
                            <input id="price-promo-value" name="promo_value" type="number" step="0.01" min="0"
                                data-label="Discount amount" placeholder="e.g. 25"
                                class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="price-promo-expires">
                            Expires <span class="font-normal text-outline">(Optional)</span>
                        </label>
                        <input id="price-promo-expires" name="promo_expires_at" type="datetime-local"
                            data-label="Expiry"
                            class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <p class="text-xs text-outline dark:text-slate-500">Leave empty and the offer runs until the price is changed again.</p>
                    </div>
                </div>
            </div>

            {{-- What the course has cost before. --}}
            <div class="flex flex-col gap-2 pt-3 border-t border-outline-variant/20 dark:border-slate-700">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant/70 dark:text-slate-500">Price history</span>
                <ul id="price-history" class="flex flex-col gap-1.5 max-h-48 overflow-y-auto custom-scrollbar"></ul>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closePriceModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                <button type="submit" data-loading-text="Saving…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Save Price</button>
            </div>
        </form>
    </div>
</div>
