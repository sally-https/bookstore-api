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
use Illuminate\Support\Facades\Hash;
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
            'school_id' => 'required|string|email|max:254',
            'password' => 'required|string',
        ]);

        // Validation failed
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Retrieve the user details
        $user = User::where('school_id', $request->email)->first();

        // Attempt authentication
        if (!$token = auth()->attempt(['school_id' => $request->email, 'password' => $request->password])) {
            return response()->json(['success' => false, 'errors' => ['school_id' => ['Invalid email or password']]], 401);
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
            'school_id' => 'required|string|min:9|max:9',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Append '@nileuniversity.edu.ng' to the school_id
        $school_id = $request->school_id . '@nileuniversity.edu.ng';

        // Find the user by school_id
        $user = User::where('school_id', $school_id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['school_id' => ['User not found']]], 404);
        }

        // Verify the password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'errors' => ['password' => ['Invalid password']]], 401);
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
            'accessToken' => $auth_token,
            'user' => [
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'created_at' => $user->created_at,
                'role' => $user->role,
                'student_id' => $user->student_id,
                'name' => $user->name,
            ],
        ], 200);
    }

public function userRegister(Request $request)
{
    // Validate the inputs
    $validator = Validator::make($request->all(), [
        'school_id' => 'required|string|min:9|max:9',
        'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    // Append '@nileuniversity.edu.ng' to the school_id
    $school_id = $request->school_id . '@nileuniversity.edu.ng';

    // Check if the user already exists
    $user = User::where('school_id', $school_id)->first();

    if (!$user) {
        // If the user doesn't exist, create a new user
        $user = User::create([
            'school_id' => $school_id,
            'password' => bcrypt($request->password),
        ]);
    } else {
        // If the user already exists, return an error response
        return response()->json(['success' => false, 'errors' => ['school_id' => ['User already exists']]], 409);
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
        'message' => 'User registered successfully.',
        'accessToken' => $auth_token,
        'user' => [
            'school_id' => $user->school_id,
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
