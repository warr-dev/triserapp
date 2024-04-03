<?php

namespace App\Client\SMS;

class SMSClient
{
    public function __construct(private SMSProvider $smsProvider)
    {
    }

    public function sendSMS(string $recipient, string $message): bool
    {
        return $this->smsProvider->sendSMS($recipient, $message);
    }
}
