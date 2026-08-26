<?php

/**
 * wkhtmltopdf, used for the worksheet question paper and mark scheme.
 *
 * The binary is not bundled — set WKHTMLTOPDF_BINARY in .env to wherever it is
 * installed. The default below is where it lives on the development machine.
 */
return [

    'pdf' => [
        'enabled' => true,
        'binary' => env('WKHTMLTOPDF_BINARY', 'D:\\wkhtmltopdf\\wkhtmltox\\bin\\wkhtmltopdf.exe'),
        'timeout' => 120,
        'options' => [
            'encoding' => 'UTF-8',
            'page-size' => 'A4',
            'margin-top' => 14,
            'margin-bottom' => 16,
            'margin-left' => 12,
            'margin-right' => 12,
            // The page number in the footer, which a printed paper needs.
            'footer-right' => 'Page [page] of [topage]',
            'footer-font-size' => 8,
            'footer-spacing' => 5,
            // Local CSS and images have to be readable for the paper to look
            // like the preview.
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],

    'image' => [
        'enabled' => false,
        'binary' => env('WKHTMLTOIMAGE_BINARY', 'D:\\wkhtmltopdf\\wkhtmltox\\bin\\wkhtmltoimage.exe'),
        'timeout' => 60,
        'options' => [],
        'env' => [],
    ],

];
