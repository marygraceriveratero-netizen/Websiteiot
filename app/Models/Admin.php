<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'role',
        'pnumber',
        'password',
        'verification_code',
        'confirm_verification_code',
    ];
}
