<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OTP;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    protected $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // OTP generate karein
        $otp = $this->otpService->generateOTP($request->email);

        // User create karein par email verify nahi karein
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'customer';
        $user->email_verified_at = null; // Email abhi verify nahi hua
        $user->save();

        // Email send karein OTP ke saath (for development, console pe print karein)
        // Production ke liye mail send karein
        Log::info("OTP for {$request->email}: {$otp}");
        
        // Agar mail configure hai toh use karein
        // Mail::to($request->email)->send(new OTPMail($otp));

        return response()->json([
            'status' => 200,
            'message' => 'Registration successful. Please verify your email with OTP.',
            'email' => $request->email,
            'requires_verification' => true
        ], 200);
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // OTP verify karein
        $isValid = $this->otpService->verifyOTP($request->email, $request->otp);

        if (!$isValid) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        // User ka email verify mark karein
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ], 404);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Email verified successfully. You can now login.'
        ], 200);
    }

    public function resendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // Check karein user exists hai ya nahi
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ], 404);
        }

        // Naya OTP generate karein
        $otp = $this->otpService->resendOTP($request->email);

        // Email send karein
        Log::info("Resent OTP for {$request->email}: {$otp}");
        // Mail::to($request->email)->send(new OTPMail($otp));

        return response()->json([
            'status' => 200,
            'message' => 'OTP resent successfully'
        ], 200);
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'error' => $validator->errors()
            ], 400);
        }

        // Check karein email verified hai ya nahi
        $user = User::where('email', $request->email)->first();
        
        if ($user && !$user->email_verified_at) {
            return response()->json([
                'status' => 403,
                'message' => 'Please verify your email first before logging in.',
                'requires_verification' => true,
                'email' => $user->email
            ], 403);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = User::find(Auth::user()->id);
            $token = $user->createToken('token')->plainTextToken;

            return response()->json([
                'status' => 200, 
                'token' => $token,
                'id' => $user->id,
                'name' => $user->name, 
            ], 200);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Either email/password is incorrect.' 
            ], 401);
        }
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}