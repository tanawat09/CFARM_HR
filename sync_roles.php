<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;

echo "Starting role synchronization...\n";

$employees = Employee::with(['user', 'position'])->get();
$count = 0;

foreach ($employees as $emp) {
    if (!$emp->user || !$emp->position) continue;
    
    $oldRole = $emp->user->role->value;
    $emp->syncUserRole();
    $newRole = $emp->user->role->value;
    
    if ($oldRole !== $newRole) {
        echo "Updating [{$emp->first_name} {$emp->last_name}]: {$oldRole} -> {$newRole} (Position: {$emp->position->name})\n";
        $count++;
    }
}

echo "Finished. Total updated: {$count}\n";
