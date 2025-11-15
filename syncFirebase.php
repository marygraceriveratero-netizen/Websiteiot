<?php

use Illuminate\Support\Facades\App;
use App\Models\FireLog;
use App\Models\Sensor;
use App\Services\FirebaseService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Kunin ang Firebase database
$firebase = App::make(FirebaseService::class)->getDatabase();

// ===== Delete existing Firebase data =====
$firebase->getReference('sensors')->remove();
$firebase->getReference('fire_logs')->remove();

echo "🧹 Cleared old Firebase data.\n";

// ===== Sync Sensors =====
$sensors = Sensor::all();
foreach ($sensors as $sensor) {
    $firebase->getReference('sensors/' . $sensor->device_id)
        ->set($sensor->toArray());
}
echo "✅ All Sensor records synced to Firebase!\n";

// ===== Sync Fire Logs =====
$fireLogs = FireLog::all();
foreach ($fireLogs as $log) {
    $firebase->getReference('fire_logs/' . $log->id)
        ->set($log->toArray());
}
echo "✅ All FireLog records synced to Firebase!\n";
