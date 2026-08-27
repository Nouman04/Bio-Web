import forms from "@tailwindcss/forms";

/**
 * The portal theme, built to a real stylesheet.
 *
 * This used to be an inline config handed to cdn.tailwindcss.com, which
 * compiles in the browser on every page load — measured at 3.6s on the chapter
 * dashboard. The colours resolve through the custom properties in
 * public/css/theme.css, which is what lets the same build serve both themes.
 */
export default {
    darkMode: "class",

    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
        "./public/js/**/*.js",
        "./app/Http/Controllers/**/*.php",
        "./app/Services/**/*.php",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    ],

    theme: {
        extend: {
            colors: {
                "background":                "rgb(var(--c-background) / <alpha-value>)",
                "error":                     "rgb(var(--c-error) / <alpha-value>)",
                "error-container":           "rgb(var(--c-error-container) / <alpha-value>)",
                "inverse-on-surface":        "rgb(var(--c-inverse-on-surface) / <alpha-value>)",
                "inverse-primary":           "rgb(var(--c-inverse-primary) / <alpha-value>)",
                "inverse-surface":           "rgb(var(--c-inverse-surface) / <alpha-value>)",
                "on-background":             "rgb(var(--c-on-background) / <alpha-value>)",
                "on-error":                  "rgb(var(--c-on-error) / <alpha-value>)",
                "on-error-container":        "rgb(var(--c-on-error-container) / <alpha-value>)",
                "on-primary":                "rgb(var(--c-on-primary) / <alpha-value>)",
                "on-primary-container":      "rgb(var(--c-on-primary-container) / <alpha-value>)",
                "on-primary-fixed":          "rgb(var(--c-on-primary-fixed) / <alpha-value>)",
                "on-primary-fixed-variant":  "rgb(var(--c-on-primary-fixed-variant) / <alpha-value>)",
                "on-secondary":              "rgb(var(--c-on-secondary) / <alpha-value>)",
                "on-secondary-container":    "rgb(var(--c-on-secondary-container) / <alpha-value>)",
                "on-secondary-fixed":        "rgb(var(--c-on-secondary-fixed) / <alpha-value>)",
                "on-secondary-fixed-variant": "rgb(var(--c-on-secondary-fixed-variant) / <alpha-value>)",
                "on-surface":                "rgb(var(--c-on-surface) / <alpha-value>)",
                "on-surface-variant":        "rgb(var(--c-on-surface-variant) / <alpha-value>)",
                "on-tertiary":               "rgb(var(--c-on-tertiary) / <alpha-value>)",
                "on-tertiary-container":     "rgb(var(--c-on-tertiary-container) / <alpha-value>)",
                "on-tertiary-fixed":         "rgb(var(--c-on-tertiary-fixed) / <alpha-value>)",
                "on-tertiary-fixed-variant": "rgb(var(--c-on-tertiary-fixed-variant) / <alpha-value>)",
                "outline":                   "rgb(var(--c-outline) / <alpha-value>)",
                "outline-variant":           "rgb(var(--c-outline-variant) / <alpha-value>)",
                "primary":                   "rgb(var(--c-primary) / <alpha-value>)",
                "primary-container":         "rgb(var(--c-primary-container) / <alpha-value>)",
                "primary-fixed":             "rgb(var(--c-primary-fixed) / <alpha-value>)",
                "primary-fixed-dim":         "rgb(var(--c-primary-fixed-dim) / <alpha-value>)",
                "secondary":                 "rgb(var(--c-secondary) / <alpha-value>)",
                "secondary-container":       "rgb(var(--c-secondary-container) / <alpha-value>)",
                "secondary-fixed":           "rgb(var(--c-secondary-fixed) / <alpha-value>)",
                "secondary-fixed-dim":       "rgb(var(--c-secondary-fixed-dim) / <alpha-value>)",
                "surface":                   "rgb(var(--c-surface) / <alpha-value>)",
                "surface-bright":            "rgb(var(--c-surface-bright) / <alpha-value>)",
                "surface-container":         "rgb(var(--c-surface-container) / <alpha-value>)",
                "surface-container-high":    "rgb(var(--c-surface-container-high) / <alpha-value>)",
                "surface-container-highest": "rgb(var(--c-surface-container-highest) / <alpha-value>)",
                "surface-container-low":     "rgb(var(--c-surface-container-low) / <alpha-value>)",
                "surface-container-lowest":  "rgb(var(--c-surface-container-lowest) / <alpha-value>)",
                "surface-dim":               "rgb(var(--c-surface-dim) / <alpha-value>)",
                "surface-tint":              "rgb(var(--c-surface-tint) / <alpha-value>)",
                "surface-variant":           "rgb(var(--c-surface-variant) / <alpha-value>)",
                "tertiary":                  "rgb(var(--c-tertiary) / <alpha-value>)",
                "tertiary-container":        "rgb(var(--c-tertiary-container) / <alpha-value>)",
                "tertiary-fixed":            "rgb(var(--c-tertiary-fixed) / <alpha-value>)",
                "tertiary-fixed-dim":        "rgb(var(--c-tertiary-fixed-dim) / <alpha-value>)",
            },

            borderRadius: {
                DEFAULT: "0.25rem",
                lg: "0.5rem",
                xl: "0.75rem",
                full: "9999px",
            },

            spacing: {
                stack_sm: "8px",
                stack_md: "16px",
                stack_lg: "24px",
                container_padding: "32px",
                sidebar_width: "260px",
                sidebar_collapsed: "80px",
                gutter: "24px",
                base: "8px",
            },

            fontFamily: {
                sans: ["Geist", "sans-serif"],
            },
        },
    },

    plugins: [forms],
};