<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FireLog;

class FirebaseSeeder extends Seeder
{
    public function run(): void
    {

        // Optional: Clear old local DB logs
        FireLog::truncate();

        $fireLog = [
            [
                'device_id' => 'DEVICE001',
                'location_name' => 'Server Room',
                'alert_type' => 'Smoke',
                'severity' => 'Low',
                'temperature' => 30,
                'smoke' => 50,
                'air_quality' => 80,
                'flame' => 0,
                'fire_detected' => 0,
                'status' => 'Monitoring',
                'fire_type' => 'Class A',
                'extinguisher' => 'Water',
                'image_url' => 'http://your-iot-camera-ip/snapshot.jpg',
            ],

        ];

        foreach ($fireLog as $log) {
            FireLog::updateOrCreate(
                ['device_id' => $log['device_id']], // ensure unique key
                $log
            );
        }

        echo "✅ FireLogs seeded and pushed safely to Firebase!\n";
    }
}
