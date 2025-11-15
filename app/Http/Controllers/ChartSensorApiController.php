<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class ChartSensorApiController extends Controller
{
    public function store(Request $request, Database $database)
    {
        // Validate incoming sensor data
        $request->validate([
            'temperature' => 'required|numeric',
            'smoke_level' => 'required|numeric',
            'air_quality' => 'required|numeric',
        ]);

        // Push data to Firebase Realtime Database
        $readingRef = $database->getReference('chart_sensor_readings')->push([
            'temperature' => $request->temperature,
            'smoke_level' => $request->smoke_level,
            'air_quality' => $request->air_quality,
            'created_at' => now()->toDateTimeString(),
        ]);

        // Return response with Firebase-generated key
        return response()->json([
            'status' => 'success',
            'firebase_key' => $readingRef->getKey(),
            'data' => [
                'temperature' => $request->temperature,
                'smoke_level' => $request->smoke_level,
                'air_quality' => $request->air_quality,
            ]
        ]);
    }
}
