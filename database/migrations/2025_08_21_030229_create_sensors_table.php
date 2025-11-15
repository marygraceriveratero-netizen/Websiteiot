<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Sensors table
        Schema::create('sensors', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('device_id', 50);  // must be unique for FK
            $table->string('location_name');
            $table->timestamps();
        });



    }

    public function down(): void
    {

        Schema::dropIfExists('sensors');
    }
};
