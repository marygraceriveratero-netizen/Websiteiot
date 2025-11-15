<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FireAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $status;
    public $fireType;
    public $extinguisher;
    public $time;
    public $imageUrl;
    public $location_name;
    public $temperature;
    public $smoke;

    public function __construct($status, $fireType, $extinguisher, $time, $imageUrl, $location_name, $temperature, $smoke)
    {
        $this->status = $status;
        $this->fireType = $fireType;
        $this->extinguisher = $extinguisher;
        $this->time = $time;
        $this->imageUrl = $imageUrl;
        $this->location_name = $location_name;
        $this->temperature = $temperature;
        $this->smoke = $smoke;
    }

    public function build()
    {
        return $this->subject("🔥 Fire Monitoring Alert: {$this->status}")
            ->view('emails.fire_alert')
            ->with([
                'status' => $this->status,
                'fireType' => $this->fireType,
                'extinguisher' => $this->extinguisher,
                'time' => $this->time,
                'imageUrl' => $this->imageUrl,
                'location_name' => $this->location_name,
                'temperature' => $this->temperature,
                'smoke' => $this->smoke,
            ]);
    }
}
