<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FireLog extends Model
{
    // Explicitly set the DB table name
    protected $table = 'fire_logs';

    protected $fillable = [
        'device_id',
        'location_name',
        'alert_type',
        'severity',
        'temperature',
        'smoke',
        'air_quality',
        'flame',
        'fire_detected',
        'status',
        'fire_type',
        'extinguisher',
        'image_url',
    ];

    // Optional: auto push to Firebase
    protected static function booted()
    {
        static::saved(function ($fireLog) {
            try {
                $database = app('firebase.database');
                $ref = $database->getReference('fire_logs/' . $fireLog->device_id);
                $ref->set($fireLog->toArray());
            } catch (\Exception $e) {
                Log::error("Firebase update failed: " . $e->getMessage());
            }
        });
    }
}
