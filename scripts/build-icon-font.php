<?php

/**
 * Builds the self-hosted Material Symbols font from the icons the app uses.
 *
 *     php scripts/build-icon-font.php
 *
 * The full variable font is 3.8 MB, and 448 KB even with the axes pinned,
 * because it carries every glyph Google ships. The app uses fewer than a
 * hundred, so this asks for exactly those and writes the result to
 * public/fonts/.
 *
 * RUN THIS AFTER ADDING A NEW ICON. An icon missing from the subset renders as
 * its own name in plain text.
 *
 * Names are collected two ways, because plenty of icons never appear literally
 * in the markup:
 *
 *   · between the tags — <span class="material-symbols-outlined">school</span>
 *   · as a quoted string in a view, service or controller, which is how the
 *     dashboards do it: ['school', 'Courses', …] rendered as {{ $icon }}
 *
 * The second sweep is deliberately greedy, so every candidate is checked
 * against Google's own icon list before being asked for. Anything that is not
 * a real icon name is simply dropped.
 */

$root = dirname(__DIR__);

$sources = [
    $root . '/resources/views',
    $root . '/app/Services',
    $root . '/app/Http/Controllers',
    $root . '/app/Notifications',
    $root . '/public/js',
];

/* ── Collect candidates ──────────────────────────────────────────────────── */

$literal = [];
$quoted = [];

foreach ($sources as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

    foreach ($it as $file) {
        if (! $file->isFile() || ! preg_match('/\.(blade\.php|php|js)$/', $file->getFilename())) {
            continue;
        }

        $code = file_get_contents($file->getPathname());

        // Written straight into the markup.
        if (preg_match_all('#material-symbols-outlined[^>]*>\s*([a-z0-9_]+)\s*<#i', $code, $m)) {
            foreach ($m[1] as $name) {
                $literal[$name] = true;
            }
        }

        // Or handed in as a string. Anything snake_case and plausible.
        if (preg_match_all('#[\'"]([a-z][a-z0-9]*(?:_[a-z0-9]+)*)[\'"]#', $code, $m)) {
            foreach ($m[1] as $name) {
                if (strlen($name) >= 3) {
                    $quoted[$name] = true;
                }
            }
        }
    }
}

$candidates = array_keys($literal + $quoted);
printf("Collected %d literal glyphs and %d further candidates.\n", count($literal), count($quoted));

/* ── Keep only the ones Google actually ships ────────────────────────────── */

$context = stream_context_create(['http' => [
    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        . "(KHTML, like Gecko) Chrome/120.0 Safari/537.36\r\n",
    'timeout' => 90,
]]);

$metadata = @file_get_contents('https://fonts.google.com/metadata/icons', false, $context);

if ($metadata === false) {
    fwrite(STDERR, "Could not reach the icon list — the existing font is left alone.\n");
    exit(1);
}

// The response is guarded with an anti-JSON-hijack prefix.
$metadata = json_decode(substr($metadata, strpos($metadata, '{')), true);

if (! isset($metadata['icons'])) {
    fwrite(STDERR, "The icon list was not in the expected shape — the existing font is left alone.\n");
    exit(1);
}

$valid = array_flip(array_column($metadata['icons'], 'name'));

$names = array_values(array_filter($candidates, fn ($n) => isset($valid[$n])));
sort($names);

// A literal glyph that Google does not recognise is a typo worth knowing about.
foreach (array_keys($literal) as $name) {
    if (! isset($valid[$name])) {
        fwrite(STDERR, "  warning: \"{$name}\" is used as an icon but is not a Material Symbol.\n");
    }
}

if (count($names) < 20) {
    fwrite(STDERR, "Only " . count($names) . " icons matched — that looks wrong, so nothing was written.\n");
    exit(1);
}

printf("%d of them are real Material Symbols.\n", count($names));

/* ── Ask for exactly those ───────────────────────────────────────────────── */

$query = http_build_query([
    'family' => 'Material Symbols Outlined:opsz,wght,FILL,GRAD@24,400,0..1,0',
    'icon_names' => implode(',', $names),
    'display' => 'swap',
]);

$css = @file_get_contents('https://fonts.googleapis.com/css2?' . $query, false, $context);

if ($css === false || ! str_contains($css, '@font-face')) {
    fwrite(STDERR, "Could not fetch the stylesheet — the existing font is left alone.\n");
    exit(1);
}

preg_match_all('#https://fonts\.gstatic\.com/[^)]+#', $css, $urls);

if ($urls[0] === []) {
    fwrite(STDERR, "No font file in the response — the existing font is left alone.\n");
    exit(1);
}

foreach (array_unique($urls[0]) as $url) {
    $data = @file_get_contents($url, false, $context);

    if ($data === false) {
        fwrite(STDERR, "Could not download the font — nothing was overwritten.\n");
        exit(1);
    }

    $file = 'symbols-' . substr(sha1($data), 0, 10) . '.woff2';
    file_put_contents($root . '/public/fonts/files/' . $file, $data);
    $css = str_replace($url, '/fonts/files/' . $file, $css);

    printf("Wrote %s (%.0f KB).\n", $file, strlen($data) / 1024);
}

foreach (glob($root . '/public/fonts/files/symbols-*.woff2') as $old) {
    if (! str_contains($css, basename($old))) {
        unlink($old);
        printf("Removed the superseded %s.\n", basename($old));
    }
}

file_put_contents($root . '/public/fonts/symbols.css', $css);
echo "public/fonts/symbols.css updated.\n";
