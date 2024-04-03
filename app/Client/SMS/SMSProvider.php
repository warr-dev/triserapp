<?php

namespace App\Client\SMS;

interface SMSProvider
{
    public function sendSMS(string $recipient, string $message): bool;
}
