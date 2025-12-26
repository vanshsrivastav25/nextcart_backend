<?php

namespace App\Services;

use App\Models\OTP;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OTPService
{
    public function generateOTP($email)
    {
        // Purane OTPs ko invalid mark karein
        OTP::where('email', $email)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Naya OTP generate karein
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $otpRecord = OTP::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5), // 5 minutes validity
        ]);

        Log::info("Generated OTP for {$email}: {$otp}");

        return $otp;
    }

    public function verifyOTP($email, $otp)
    {
        $otpRecord = OTP::where('email', $email)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($otpRecord) {
            $otpRecord->update(['is_used' => true]);
            return true;
        }

        return false;
    }

    public function resendOTP($email)
    {
        return $this->generateOTP($email);
    }
}