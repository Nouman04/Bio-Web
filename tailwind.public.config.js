import forms from "@tailwindcss/forms";

/**
 * The public marketing site's theme, built rather than compiled in the browser.
 *
 * The object below is the inline config the page was handing to
 * cdn.tailwindcss.com, lifted verbatim so the built sheet renders the same
 * theme. The site is light-only, so its colours stay literal — it does not use
 * the portal's theme variables.
 */
const inline = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-tertiary-fixed": "#40000d",
                        "on-surface-variant": "#464554",
                        "on-tertiary": "#ffffff",
                        "on-error-container": "#93000a",
                        "primary-container": "#0041a3",
                        "inverse-primary": "#80b2ff",
                        "surface-container-lowest": "#ffffff",
                        "background": "#f7f9fb",
                        "outline-variant": "#c6c5d7",
                        "surface-container-low": "#f2f4f6",
                        "tertiary-fixed": "#ffdadb",
                        "secondary": "#516072",
                        "on-tertiary-fixed-variant": "#920029",
                        "primary-fixed": "#c2daff",
                        "secondary-container": "#d1e1f7",
                        "surface-container": "#eceef0",
                        "on-secondary-fixed-variant": "#394859",
                        "inverse-surface": "#2d3133",
                        "error": "#ba1a1a",
                        "tertiary-container": "#b90538",
                        "surface-container-highest": "#e0e3e5",
                        "on-background": "#191c1e",
                        "secondary-fixed-dim": "#b8c8dd",
                        "on-secondary-container": "#556476",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed": "#0d1d2c",
                        "primary-fixed-dim": "#80b2ff",
                        "on-tertiary-container": "#ffc7ca",
                        "surface": "#f7f9fb",
                        "primary": "#001330",
                        "surface-dim": "#d8dadc",
                        "on-primary-fixed-variant": "#002866",
                        "secondary-fixed": "#d4e4f9",
                        "surface-container-high": "#e6e8ea",
                        "surface-bright": "#f7f9fb",
                        "on-error": "#ffffff",
                        "on-primary": "#ffffff",
                        "on-surface": "#191c1e",
                        "outline": "#767586",
                        "on-primary-fixed": "#001330",
                        "glass-stroke": "rgba(0, 0, 0, 0.05)",
                        "primary-glow": "rgba(0, 19, 48, 0.15)",
                        "tertiary": "#8e0028",
                        "surface-variant": "#e0e3e5",
                        "on-secondary": "#ffffff",
                        "inverse-on-surface": "#eff1f3",
                        "glass-bg": "rgba(255, 255, 255, 0.8)",
                        "tertiary-fixed-dim": "#ffb2b7",
                        "on-primary-container": "#ffffff",
                        "surface-tint": "#001a42"
                    },
                    "borderRadius": {
                        "DEFAULT": "1rem",
                        "lg": "2rem",
                        "xl": "3rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "container-max": "1280px",
                        "base": "8px",
                        "lg": "48px",
                        "md": "24px",
                        "sm": "12px",
                        "xl": "80px",
                        "xs": "4px",
                        "2xl": "120px"
                    },
                    "fontFamily": {
                        "body-lg": ["Geist"],
                        "headline-lg-mobile": ["Geist"],
                        "headline-lg": ["Geist"],
                        "display-lg": ["Geist"],
                        "headline-md": ["Geist"],
                        "label-sm": ["Geist"],
                        "body-md": ["Geist"],
                        "label-md": ["Geist"]
                    },
                    "fontSize": {
                        "body-lg": ["18px", { "lineHeight": "1.6", "fontWeight": "400" }],
                        "headline-lg-mobile": ["24px", { "lineHeight": "1.2", "fontWeight": "600" }],
                        "headline-lg": ["32px", { "lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "display-lg": ["48px", { "lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-md": ["24px", { "lineHeight": "1.3", "fontWeight": "600" }],
                        "label-sm": ["12px", { "lineHeight": "1.2", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "1.5", "fontWeight": "400" }],
                        "label-md": ["14px", { "lineHeight": "1.4", "letterSpacing": "0.01em", "fontWeight": "500" }]
                    }
                }
            }
        };

export default {
    ...inline,
    content: [
        "./resources/views/public/**/*.blade.php",
        "./resources/views/vendor/pagination/public.blade.php",
    ],
    plugins: [forms],
};