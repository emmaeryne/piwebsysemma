<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private Client $twilio;
    private string $fromNumber;

    public function __construct(string $accountSid, string $authToken, string $fromNumber)
    {
        $this->twilio = new Client($accountSid, $authToken);
        $this->fromNumber = $fromNumber;
    }

    public function sendSms(string $to, string $message): void
    {
        $this->twilio->messages->create($to, [
            'from' => $this->fromNumber,
            'body' => $message,
        ]);
    }
}