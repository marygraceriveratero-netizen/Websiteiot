<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\FirebaseService;

class Sensor extends Model
{
    protected $fillable = ['device_id', 'location_name'];

    protected static function booted()
    {
        static::created(function ($sensor) {
            $firebase = app(FirebaseService::class)->getDatabase();
            $firebase->getReference('sensors/' . $sensor->device_id)
                ->set($sensor->toArray());
        });

        static::updated(function ($sensor) {
            $firebase = app(FirebaseService::class)->getDatabase();
            $firebase->getReference('sensors/' . $sensor->device_id)
                ->update($sensor->toArray());
        });

        static::deleted(function ($sensor) {
            $firebase = app(FirebaseService::class)->getDatabase();
            $firebase->getReference('sensors/' . $sensor->device_id)
                ->remove();
        });
    }
}
