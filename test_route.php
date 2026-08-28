<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/questions', 'GET');
$response = $kernel->handle($request);
if ($response->exception) {
    echo "ERROR: " . $response->exception->getMessage() . "\n";
    echo $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
    echo $response->exception->getTraceAsString();
} else {
    echo "STATUS: " . $response->status() . "\n";
}
