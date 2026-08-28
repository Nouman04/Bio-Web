import forms from "@tailwindcss/forms";

/**
 * The sign-in shell.
 *
 * These pages use a different geometry from the rest of the app — a 1rem
 * default radius, an xs/sm/md/lg/xl spacing scale and a fixed card width —
 * which is why they build separately rather than sharing the portal sheet.
 * The palette is the shared one, read through the theme variables.
 */
export default {
    darkMode: "class",

    content: [
        "./resources/views/auth/**/*.blade.php",
        "./resources/views/layouts/auth.blade.php",
    ],

    theme: {
        extend: {
            colors: {
                "background":                  "rgb(var(--c-background) / <alpha-value>)",
                "error":                       "rgb(var(--c-error) / <alpha-value>)",
                "error-container":             "rgb(var(--c-error-container) / <alpha-value>)",
                "inverse-on-surface":          "rgb(var(--c-inverse-on-surface) / <alpha-value>)",
                "inverse-primary":             "rgb(var(--c-inverse-primary) / <alpha-value>)",
                "inverse-surface":             "rgb(var(--c-inverse-surface) / <alpha-value>)",
                "on-background":               "rgb(var(--c-on-background) / <alpha-value>)",
                "on-error":                    "rgb(var(--c-on-error) / <alpha-value>)",
                "on-error-container":          "rgb(var(--c-on-error-container) / <alpha-value>)",
                "on-primary":                  "rgb(var(--c-on-primary) / <alpha-value>)",
                "on-primary-container":        "rgb(var(--c-on-primary-container) / <alpha-value>)",
                "on-primary-fixed":            "rgb(var(--c-on-primary-fixed) / <alpha-value>)",
                "on-primary-fixed-variant":    "rgb(var(--c-on-primary-fixed-variant) / <alpha-value>)",
                "on-secondary":                "rgb(var(--c-on-secondary) / <alpha-value>)",
                "on-secondary-container":      "rgb(var(--c-on-secondary-container) / <alpha-value>)",
                "on-secondary-fixed":          "rgb(var(--c-on-secondary-fixed) / <alpha-value>)",
                "on-secondary-fixed-variant":  "rgb(var(--c-on-secondary-fixed-variant) / <alpha-value>)",
                "on-surface":                  "rgb(var(--c-on-surface) / <alpha-value>)",
                "on-surface-variant":          "rgb(var(--c-on-surface-variant) / <alpha-value>)",
                "on-tertiary":                 "rgb(var(--c-on-tertiary) / <alpha-value>)",
                "on-tertiary-container":       "rgb(var(--c-on-tertiary-container) / <alpha-value>)",
                "on-tertiary-fixed":           "rgb(var(--c-on-tertiary-fixed) / <alpha-value>)",
                "on-tertiary-fixed-variant":   "rgb(var(--c-on-tertiary-fixed-variant) / <alpha-value>)",
                "outline":                     "rgb(var(--c-outline) / <alpha-value>)",
                "outline-variant":             "rgb(var(--c-outline-variant) / <alpha-value>)",
                "primary":                     "rgb(var(--c-primary) / <alpha-value>)",
                "primary-container":           "rgb(var(--c-primary-container) / <alpha-value>)",
                "primary-fixed":               "rgb(var(--c-primary-fixed) / <alpha-value>)",
                "primary-fixed-dim":           "rgb(var(--c-primary-fixed-dim) / <alpha-value>)",
                "secondary":                   "rgb(var(--c-secondary) / <alpha-value>)",
                "secondary-container":         "rgb(var(--c-secondary-container) / <alpha-value>)",
                "secondary-fixed":             "rgb(var(--c-secondary-fixed) / <alpha-value>)",
                "secondary-fixed-dim":         "rgb(var(--c-secondary-fixed-dim) / <alpha-value>)",
                "surface":                     "rgb(var(--c-surface) / <alpha-value>)",
                "surface-bright":              "rgb(var(--c-surface-bright) / <alpha-value>)",
                "surface-container":           "rgb(var(--c-surface-container) / <alpha-value>)",
                "surface-container-high":      "rgb(var(--c-surface-container-high) / <alpha-value>)",
                "surface-container-highest":   "rgb(var(--c-surface-container-highest) / <alpha-value>)",
                "surface-container-low":       "rgb(var(--c-surface-container-low) / <alpha-value>)",
                "surface-container-lowest":    "rgb(var(--c-surface-container-lowest) / <alpha-value>)",
                "surface-dim":                 "rgb(var(--c-surface-dim) / <alpha-value>)",
                "surface-tint":                "rgb(var(--c-surface-tint) / <alpha-value>)",
                "surface-variant":             "rgb(var(--c-surface-variant) / <alpha-value>)",
                "tertiary":                    "rgb(var(--c-tertiary) / <alpha-value>)",
                "tertiary-container":          "rgb(var(--c-tertiary-container) / <alpha-value>)",
                "tertiary-fixed":              "rgb(var(--c-tertiary-fixed) / <alpha-value>)",
                "tertiary-fixed-dim":          "rgb(var(--c-tertiary-fixed-dim) / <alpha-value>)",
            },

            borderRadius: {
                DEFAULT: "1rem",
                lg: "2rem",
                xl: "3rem",
                full: "9999px",
            },

            spacing: {
                xs: "4px",
                sm: "12px",
                md: "24px",
                lg: "48px",
                xl: "80px",
                base: "8px",
                "container-max": "1280px",
                "auth-card-width": "440px",
            },

            fontFamily: {
                sans: ["Geist", "sans-serif"],
            },
        },
    },

    plugins: [forms],
};