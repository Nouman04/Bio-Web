/**
 * The password policy, shown as it is typed.
 *
 * The server enforces it through Password::defaults() — eight characters, at
 * least one number, at least one symbol. This says the same thing while the
 * reader is still in the field, so a rejected password is never a surprise.
 *
 * Mark the field and the meter renders itself underneath:
 *
 *   <input type="password" name="password" data-password-policy>
 *
 * The check also runs on submit, so a form cannot be sent with a password the
 * server is going to refuse.
 */
(function () {
    'use strict';

    /** Each rule, in the order the meter lists them. */
    const RULES = [
        { label: 'At least 8 characters', test: (value) => value.length >= 8 },
        { label: 'A number', test: (value) => /\d/.test(value) },
        { label: 'A special character', test: (value) => /[^\w\s]/.test(value) },
    ];

    /** The message shown against the field when it does not pass. */
    const MESSAGE = 'Password must be at least 8 characters and include a number and a special character.';

    function buildMeter(input) {
        const meter = document.createElement('div');
        meter.className = 'password-meter';

        const track = document.createElement('div');
        track.className = 'password-meter-track';
        const fill = document.createElement('div');
        fill.className = 'password-meter-fill';
        track.appendChild(fill);

        const rules = document.createElement('div');
        rules.className = 'password-meter-rules';

        const items = RULES.map((rule) => {
            const item = document.createElement('span');
            item.className = 'password-meter-rule';
            item.textContent = rule.label;
            rules.appendChild(item);
            return item;
        });

        meter.append(track, rules);

        // Below the field, which for these forms means below the wrapper the
        // input sits inside rather than beside the icon.
        const anchor = input.closest('.input-field, .relative') ?? input;
        anchor.insertAdjacentElement('afterend', meter);

        return { meter, fill, items };
    }

    /** Whether a value satisfies every rule. */
    function satisfies(value) {
        return RULES.every((rule) => rule.test(value));
    }

    function attach(input) {
        if (input.dataset.passwordMeterBound) return;
        input.dataset.passwordMeterBound = 'true';

        const { meter, fill, items } = buildMeter(input);

        const refresh = () => {
            const value = input.value ?? '';
            const met = RULES.reduce((total, rule, index) => {
                const passed = rule.test(value);
                items[index].classList.toggle('is-met', passed);
                return total + (passed ? 1 : 0);
            }, 0);

            meter.dataset.met = String(met);
            fill.style.width = `${(met / RULES.length) * 100}%`;
        };

        input.addEventListener('input', refresh);
        refresh();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[data-password-policy]').forEach(attach);

        // Nothing leaves the page carrying a password the server will refuse.
        document.querySelectorAll('form').forEach((form) => {
            const fields = form.querySelectorAll('input[data-password-policy]');
            if (!fields.length) return;

            form.addEventListener('submit', (event) => {
                if (event.defaultPrevented) return;

                for (const field of fields) {
                    if (field.value === '' || satisfies(field.value)) continue;

                    event.preventDefault();
                    window.App?.clearFieldErrors?.(form);
                    window.App?.showFieldErrors?.(form, { [field.name]: MESSAGE });
                    field.focus();
                    return;
                }
            });
        });
    });
})();
