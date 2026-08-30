/**
 * The extra width and height steps, shared by all three builds.
 *
 * Tailwind's own scale jumps 12 → 14 → 16 → 20 → 24, which leaves no way to
 * size something like a logo to the step in between without dropping into an
 * arbitrary value. These fill in the odd numbers, add a run of literal pixel
 * sizes, and add the viewport/content keywords — so `w-13`, `h-72px`,
 * `max-w-fit` and `min-h-screen-1/2` all resolve.
 *
 * They are applied to width, height, minWidth, minHeight, maxWidth and
 * maxHeight alike, so anything that can be given a width can be given the same
 * height.
 */

/** The half-steps Tailwind's spacing scale leaves out. */
const steps = {
    "1.5": "0.375rem",
    "2.5": "0.625rem",
    "3.5": "0.875rem",
    "4.5": "1.125rem",
    "5.5": "1.375rem",
    "6.5": "1.625rem",
    "7.5": "1.875rem",
    "8.5": "2.125rem",
    "9.5": "2.375rem",
    "13": "3.25rem",
    "15": "3.75rem",
    "17": "4.25rem",
    "18": "4.5rem",
    "19": "4.75rem",
    "21": "5.25rem",
    "22": "5.5rem",
    "26": "6.5rem",
    "30": "7.5rem",
    "34": "8.5rem",
    "38": "9.5rem",
    "42": "10.5rem",
    "46": "11.5rem",
    "50": "12.5rem",
    "54": "13.5rem",
    "58": "14.5rem",
    "62": "15.5rem",
    "66": "16.5rem",
    "70": "17.5rem",
    "74": "18.5rem",
    "78": "19.5rem",
    "82": "20.5rem",
    "86": "21.5rem",
    "90": "22.5rem",
    "94": "23.5rem",
    "100": "25rem",
    "104": "26rem",
    "112": "28rem",
    "120": "30rem",
    "128": "32rem",
    "144": "36rem",
    "160": "40rem",
    "176": "44rem",
    "192": "48rem",
};

/** Literal pixel sizes, for things measured against an asset rather than the grid. */
const pixels = Object.fromEntries(
    [
        2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 36, 40, 44,
        48, 52, 56, 60, 64, 72, 80, 88, 96, 104, 112, 120, 128, 140, 150, 160,
        180, 200, 220, 240, 260, 280, 300, 320, 360, 400, 440, 480, 520, 560,
        600, 640, 720, 800, 900, 1000, 1200,
    ].map((px) => [`${px}px`, `${px}px`]),
);

/** Fractions past Tailwind's own, plus the intrinsic and viewport keywords. */
const relative = {
    "1/7": "14.2857143%",
    "2/7": "28.5714286%",
    "3/7": "42.8571429%",
    "4/7": "57.1428571%",
    "5/7": "71.4285714%",
    "6/7": "85.7142857%",
    "1/8": "12.5%",
    "3/8": "37.5%",
    "5/8": "62.5%",
    "7/8": "87.5%",
    "1/10": "10%",
    "3/10": "30%",
    "7/10": "70%",
    "9/10": "90%",
    fit: "fit-content",
    max: "max-content",
    min: "min-content",
    "screen-1/4": "25vh",
    "screen-1/3": "33.333333vh",
    "screen-1/2": "50vh",
    "screen-2/3": "66.666667vh",
    "screen-3/4": "75vh",
    "vw-1/4": "25vw",
    "vw-1/3": "33.333333vw",
    "vw-1/2": "50vw",
    "vw-2/3": "66.666667vw",
    "vw-3/4": "75vw",
    "vw-full": "100vw",
};

export const sizes = { ...steps, ...pixels, ...relative };

/**
 * Spread into a config's `theme.extend` so every sizing utility gets the same
 * scale.
 */
export const sizing = {
    width: sizes,
    height: sizes,
    minWidth: sizes,
    minHeight: sizes,
    maxWidth: sizes,
    maxHeight: sizes,
    size: sizes,
};

export default sizing;
