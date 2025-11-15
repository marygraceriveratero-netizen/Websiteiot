<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\FireLog;
class FireLogController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    // Show all logs
    public function index()
    {
        $allLogs = $this->database->getReference('fire_logs')
            ->orderByChild('created_at')
            ->getValue() ?? [];

        $logs = collect($allLogs)
            ->map(fn($item, $key) => array_merge($item, ['firebase_key' => $key]))
            ->sortByDesc('created_at')
            ->values();

        return response()->json($logs);
    }

    // Store new log from IoT
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'temperature' => 'required|numeric',
            'smoke' => 'required|numeric',
            'location_name' => 'required|string',
            'flame' => 'sometimes|numeric',
            'fire_detected' => 'required|integer|in:0,1,2',
            'fire_type' => 'nullable|string',
            'extinguisher' => 'nullable|string',
            'image_url' => 'nullable|url',
        ]);

        $validated['flame'] = $request->has('flame') ? $validated['flame'] : 0;

        $newLog = [
            ...$validated,
            'status' => 'Monitoring',
            'created_at' => now()->toDateTimeString(),
        ];

        $logRef = $this->database->getReference('fire_logs')->push($newLog);

        return response()->json([
            'message' => 'Log created successfully.',
            'firebase_key' => $logRef->getKey(),
            'log' => $newLog,
        ], 201);
    }

    // Update only fire_detected manually
    public function update(Request $request, $firebaseKey)
    {
        $validated = $request->validate([
            'fire_detected' => 'required|integer|in:0,1,2',
        ]);

        $logRef = $this->database->getReference("fire_logs/{$firebaseKey}");
        $log = $logRef->getValue();

        if (!$log)
            return response()->json(['message' => 'Log not found'], 404);

        $log['fire_detected'] = $validated['fire_detected'];
        $logRef->set($log);

        return response()->json([
            'message' => 'Log updated successfully.',
            'log' => $log,
        ]);
    }



    public function indexx()
    {
        // Fetch all fire logs from Firebase
        $reference = $this->database->getReference('fire_logs');
        $snapshot = $reference->getSnapshot();
        $alerts = $snapshot->getValue();

        // Kung walang laman, gawin empty array para hindi mag-error
        $alerts = $alerts ? $alerts : [];

        // Convert to collection para madali i-handle sa Blade
        $alerts = collect($alerts)->sortByDesc('time');

        return view('Alert_type', compact('alerts'));
    }


    public function showFireLogs()
    {
        // Fetch logs from Firebase
        $reference = $this->database->getReference('fire_logs');
        $snapshot = $reference->getSnapshot();
        $fireLogs = $snapshot->getValue();

        // Default empty array para safe
        $fireLogs = $fireLogs ? $fireLogs : [];

        // Convert to Laravel collection and sort by time (desc)
        $fireLogs = collect($fireLogs)->sortByDesc('time');

        return view('Event_log_history', compact('fireLogs'));
    }


    public function showImageFeed()
    {
        // Kunin lahat ng fire logs
        $fireLogs = FireLog::all();

        // Ipass sa view
        return view('imagefeed', compact('fireLogs'));
    }



    // Export CSV
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'fire_logs.csv';

        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Firebase Key',
                'Device ID',
                'Temperature',
                'location_name',
                'Smoke',
                'Flame',
                'Event',
                'Fire Type',
                'Extinguisher',
                'Status',
                'Created At'
            ]);

            $allLogs = $this->database->getReference('fire_logs')
                ->orderByChild('created_at')
                ->getValue() ?? [];

            $logs = collect($allLogs)
                ->map(fn($item, $key) => array_merge($item, ['firebase_key' => $key]))
                ->sortByDesc('created_at');

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['firebase_key'] ?? '',
                    $log['device_id'] ?? '',
                    $log['temperature'] ?? '',
                    $log['smoke'] ?? '',
                    $log['flame'] ?? '',
                    $log['event'] ?? 'Under Review',
                    $log['fire_type'] ?? '',
                    $log['extinguisher'] ?? '',
                    $log['status'] ?? '',
                    $log['created_at'] ?? ''
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }
}
