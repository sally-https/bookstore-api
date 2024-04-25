<?php

namespace App\Jobs;

use App\Models\User;
use ElasticEmail\Api\EmailsApi;
use ElasticEmail\Configuration;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use ElasticEmail\Model\EmailTransactionalMessageData;
use ElasticEmail\Model\TransactionalRecipient;
use ElasticEmail\Model\EmailContent;
use ElasticEmail\Model\BodyPart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $token;

    public function __construct($email, $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    public function handle()
    {
        $apiKey = config('services.elastic_email.api_key');

        $config = Configuration::getDefaultConfiguration()->setApiKey('X-ElasticEmail-ApiKey', $apiKey);
        $apiInstance = new EmailsApi(new Client(), $config);

        // Load email template from storage
        $emailTemplate = file_get_contents('/Users/favourafula/Desktop/SALIHAT FINAL PROJECT/bookstore-api/storage/templates/verify.html');

        // Replace placeholders with actual data
        $htmlContent = str_replace('{{token}}', $this->token, $emailTemplate);

        // Construct email message data
        $email_message_data = new EmailTransactionalMessageData([
            "recipients" => new TransactionalRecipient([
                "to" => [$this->email],
            ]),
            "content" => new EmailContent([
                "body" => [new BodyPart([
                    "content_type" => "HTML",
                    "content" => $htmlContent
                ])],
                "from" => "Salihat from BookStore <201212054@nileuniversity.edu.ng>",
                "subject" => "Your Bookstore OTP is here!",
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
