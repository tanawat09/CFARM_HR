<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "DEPT: " . json_encode(App\Models\Department::pluck('name')->toArray(), JSON_UNESCAPED_UNICODE) . "\n";
echo "POS: " . json_encode(App\Models\Position::pluck('name')->toArray(), JSON_UNESCAPED_UNICODE) . "\n";
