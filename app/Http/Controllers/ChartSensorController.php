<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class ChartSensorController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    // Show the chart dashboard view
    public function index()
    {
        return view('chart_sensor_dashboard');
    }

    // Fetch last 20 sensor readings from Firebase
    public function fetchData()
    {
        // Get all readings from Firebase
        $allReadings = $this->database->getReference('chart_sensor_readings')
            ->orderByChild('created_at')
            ->limitToLast(20)
            ->getValue();

        // Firebase returns associative array, convert to numeric array and sort ascending by created_at
        $data = collect($allReadings)
            ->map(function ($item, $key) {
                $item['firebase_key'] = $key;
                return $item;
            })
            ->sortBy('created_at')
            ->values();

        return response()->json($data);
    }
}
