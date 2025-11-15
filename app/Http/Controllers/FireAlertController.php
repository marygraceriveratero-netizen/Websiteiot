<?php

namespace App\Http\Controllers;

use App\Mail\FireAlertMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class FireAlertController extends Controller
{
    public function send(Request $request)
    {
        $status = $request->status;
        $fireType = $request->fire_type ?? '-';
        $extinguisher = $request->extinguisher ?? '-';
        $time = now()->toDateTimeString();

        $imageUrl = null;
        $imageResponse = Http::get('https://fire-monitoring-b1d3c-default-rtdb.firebaseio.com/fire_logs/DEVICE001/images.json');

        if ($imageResponse->ok()) {
            $images = $imageResponse->json();

            if (!empty($images)) {
                // Get the last uploaded image (latest key)
                $latestKey = array_key_last($images);
                $latestImage = $images[$latestKey];

                // Some images may have `url` inside, so extract it properly
                if (isset($latestImage['url'])) {
                    $imageUrl = $latestImage['url'];
                }
            }
        }

        // fallback if no image found
        if (empty($imageUrl)) {
            $imageUrl = 'https://via.placeholder.com/400x300?text=No+Image+Available';
        }

        $response = Http::get('https://fire-monitoring-b1d3c-default-rtdb.firebaseio.com/admins.json');

        if ($response->ok()) {
            $admins = $response->json();

            foreach ($admins as $admin) {
                if (!empty($admin['email'])) {
                    Mail::to($admin['email'])->send(new FireAlertMail(
                        $status,
                        $fireType,
                        $extinguisher,
                        $time,
                        $imageUrl,
                        $request->location_name ?? '-',
                        $request->temperature ?? '-',
                        $request->smoke ?? '-'
                    ));
                }
            }
        } else {
            return response()->json(['error' => 'Cannot fetch admins from Firebase'], 500);
        }

        return response()->json([
            'message' => 'Email sent successfully',
            'image_used' => $imageUrl
        ]);
    }
}