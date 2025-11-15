<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kreait\Firebase\Factory;

class AdminsSeeder extends Seeder
{
    public function run(): void
    {
        $firebase = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'))
            ->createDatabase();

        // Custom key instead of push()
        $adminKey = 'admin_001';

        $firebase->getReference('admins/' . $adminKey)
            ->set([
                'name' => 'Julius N. Manganti',
                'email' => 'juliusmanganti02@gmail.com',
                'password' => 'secret123',
                'verification_code' => '123456',
                'confirm_verification_code' => '123456',
                'role' => 'OSHO',
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]);

        echo "✅ Admin created with key {$adminKey}\n";
    }
}