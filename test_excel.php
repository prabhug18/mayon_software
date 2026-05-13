<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, 'doesnotexist.xlsx');
    echo "Success\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
