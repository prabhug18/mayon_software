<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$path = __DIR__ . '/public/favicon.ico'; // dummy absolute path, assuming we can read it to array? No, just checking if it says "does not exist"
try {
    $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $path);
    echo "Success\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
