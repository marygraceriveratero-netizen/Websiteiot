<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fire_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50);
            $table->string('location_name');
            $table->string('alert_type')->nullable();
            $table->string('severity')->nullable();
            $table->float('temperature');
            $table->integer('smoke');
            $table->float('air_quality');
            $table->float('flame')->default(0);
            $table->boolean('fire_detected');
            $table->enum('status', ['Under Review', 'Monitoring', 'Resolved'])->default('Monitoring');
            $table->string('fire_type')->nullable();
            $table->string('extinguisher')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();


        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fire_logs');
    }
};
