<?php

/**
 * Only the messages this app overrides. Everything else falls back to the
 * framework's own translations.
 */
return [
    /*
     * PHP rejects a file that exceeds upload_max_filesize before Laravel ever
     * sees it, and the default wording ("failed to upload") gives the reader
     * nothing to act on. Name the real limit instead.
     */
    'uploaded' => 'The :attribute could not be uploaded. It may be larger than the '
        . (ini_get('upload_max_filesize') ?: '2M')
        . ' the server accepts per file.',
];
