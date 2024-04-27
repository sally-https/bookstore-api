<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VerifyStudent;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyStudent as VerifyStudentMail;
use App\Jobs\SendVerificationEmailJob; // Import the SendVerificationEmailJob

class VerifyStudentController extends Controller
{
    /**
     * Collect student ID and send verification email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function studentVerification(Request $request)
    {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|string|min:9|max:9',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Append @nileuniversity.edu.ng to the student ID
        $email = $request->school_id . '@nileuniversity.edu.ng';

        // Check if the email already exists
        $existingVerification = VerifyStudent::where('school_id', $email)->first();

        if ($existingVerification) {
            // Check if the verification code has expired
            if ($existingVerification->expiration > Carbon::now()) {
                // If it hasn't expired, return a response indicating that another verification cannot be generated
                return response()->json(['message' => 'Another verification code cannot be generated.'], 409);
            } else {
                // If it has expired, generate a new verification code
                $existingVerification->verification_code = strtoupper(Str::random(5));
                $existingVerification->expiration = Carbon::now()->addMinutes(25);
                $existingVerification->save();
            }
        } else {
            // Generate a verification token
            $token = strtoupper(Str::random(5));

            // Calculate token expiration time (25 minutes from now)
            $expiration = Carbon::now()->addMinutes(1);

            // Store the token and expiration time in the database
            VerifyStudent::create([
                'school_id' => $email,
                'verification_code' => $token,
                'expiration' => $expiration,
            ]);
        }

        // Dispatch the job to send the verification email
        SendVerificationEmailJob::dispatch($email, $token ?? $existingVerification->verification_code);

        // Return a success response
        return response()->json(['message' => 'Verification email queued for sending'], 200);
    }
}
