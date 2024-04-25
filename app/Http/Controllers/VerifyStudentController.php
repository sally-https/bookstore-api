<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VerifyStudent;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use ElasticEmail\Api\EmailsApi;
use ElasticEmail\Configuration;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use ElasticEmail\Model\EmailTransactionalMessageData;
use ElasticEmail\Model\TransactionalRecipient;
use ElasticEmail\Model\EmailContent;
use ElasticEmail\Model\BodyPart;

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
            'personal_email' => 'required|string|email|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Construct the email address
        $email = $request->personal_email;

        // Generate a verification token
        $token = strtoupper(Str::random(5));

        // Calculate token expiration time (25 minutes from now)
        $expiration = Carbon::now()->addMinutes(25);

        // Store the token and expiration time in the database
        VerifyStudent::create([
            'personal_email' => $request->personal_email,
            'verification_code' => $token,
            'expiration' => $expiration,
        ]);

        // Send the verification email
        $this->sendVerificationEmail($email, $token);

        // Return a success response
        return response()->json(['message' => 'Verification email sent'], 200);
    }

    /**
     * Send verification email.
     *
     * @param string $email
     * @param string $token
     * @return void
     */
    private function sendVerificationEmail($email, $token)
    {
        $apiKey = config('services.elastic_email.api_key');

        $config = Configuration::getDefaultConfiguration()->setApiKey('X-ElasticEmail-ApiKey', $apiKey);
        $apiInstance = new EmailsApi(new Client(), $config);

        // Construct email message data
        $email_message_data = new EmailTransactionalMessageData([
            "recipients" => new TransactionalRecipient([
                "to" => [$email],
            ]),
            "content" => new EmailContent([
                "body" => [new BodyPart([
                    "content_type" => "HTML",
                    "content" => "<p>Your verification token is: <strong>$token</strong></p>",
                ])],
                "from" => "Salihat from BookStore <no-reply@use-api-services.com>",
                "subject" => "Your Bookstore OTP is $token",
            ]),
        ]);

        try {
            // Send the email
            $apiInstance->emailsTransactionalPost($email_message_data);
        } catch (\Exception $e) {
            // Handle exception if needed
            Log::error('Failed to send email: ' . $e->getMessage());
        }
    }
}
