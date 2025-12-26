<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'otp', 'expires_at', 'is_used'];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Table name specify karein agar different ho
    protected $table = 'otps';
}