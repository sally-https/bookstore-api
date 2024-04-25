<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\VerifyStudent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Authenticate an admin user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLogin(Request $request)
    {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'personal_email' => 'required|string|email|max:254',
            'password' => 'required|string',
        ]);

        // Validation failed
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Retrieve the user details
        $user = User::where('personal_email', $request->email)->first();

        // Attempt authentication
        if (!$token = auth()->attempt(['personal_email' => $request->email, 'password' => $request->password])) {
            return response()->json(['success' => false, 'errors' => ['personal_email' => ['Invalid email or password']]], 401);
        }

        // Retrieve user details
        $user = auth()->user();

        // Generate new token data
        $tokenData = $this->createNewToken($token)->getData();

        // Return success response
        $response = [
            'success' => true,
            'message' => 'User logged in successfully.',
            'user' => [
                'accessToken' => $tokenData->accessToken,
                'email' => $user->email,
                'name' => $user->name,
                'is_admin' => $user->is_admin,
            ],
        ];
        return response()->json($response, 200);
    }

public function userLogin(Request $request)
{
    // Validate the inputs
    $validator = Validator::make($request->all(), [
        'personal_email' => 'required|string|email',
        'verification_code' => 'required|string|max:5|min:5',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    // Find the verification record
    $verification = VerifyStudent::where('personal_email', $request->personal_email)
                                 ->where('verification_code', $request->verification_code)
                                 ->where('expiration', '>', now())
                                 ->first();

    if (!$verification) {
        return response()->json(['success' => false, 'errors' => ['verification' => ['Invalid verification code or expired']]], 401);
    }

    // Retrieve the user by personal email
    $user = User::where('personal_email', $request->personal_email)->first();

    if (!$user) {
        // If the user doesn't exist, create a new user
        $user = User::create([
            'personal_email' => $request->personal_email,
        ]);
    }

    try {
        // Generate JWT token for the user
        $auth_token = JWTAuth::fromUser($user);
    } catch (JWTException $e) {
        return response()->json(['success' => false, 'errors' => ['token' => ['Failed to generate token']]], 500);
    }

    // Return success response with token
    return response()->json([
        'success' => true,
        'message' => 'User logged in successfully.',
        'user' => [
            'accessToken' => $auth_token,
            'personal_email' => $user->personal_email,
            'created_at' => $user->created_at,
            'role' => $user->role,
            'student_id' => $user->student_id,
            'name' => $user->name,
        ],
    ], 200);
}




    /**
     * Get the token array structure.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'accessToken' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user()
        ]);
    }
}
