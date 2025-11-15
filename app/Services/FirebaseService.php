<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'))
            ->withDatabaseUri(config('services.firebase.database_url'));

        $this->database = $factory->createDatabase();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    // 🔥 Special for Admins
    public function saveAdmin($id, array $data): void
    {
        $this->database
            ->getReference('admins/' . $id)
            ->set($data);
    }


    /**
     * Save or update data into Firebase
     */
    public function save(string $path, array $data): void
    {
        $this->database
            ->getReference($path)
            ->set($data);
    }

    /**
     * Push new data (auto-generate ID in Firebase)
     */
    public function push(string $path, array $data): void
    {
        $this->database
            ->getReference($path)
            ->push($data);
    }

    /**
     * Get data from Firebase
     */
    public function get(string $path): mixed
    {
        return $this->database
            ->getReference($path)
            ->getValue();
    }

    /**
     * Delete data from Firebase
     */
    public function delete(string $path): void
    {
        $this->database
            ->getReference($path)
            ->remove();
    }




}

