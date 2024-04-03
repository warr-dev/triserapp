<?php

namespace App\Client\SMS;

use App\Client\SMSProvider;
use Illuminate\Support\Facades\Http;

class ItexmoSMS implements SMSProvider
{

    public function __construct(private $email = null, private $password = null, private $apicode = null)
    {
        $this->email = $email ?? config('sms.providers.itexmo.email');
        $this->password = $password ?? config('sms.providers.itexmo.password');
        $this->apicode = $apicode ?? config('sms.providers.itexmo.apicode');
    }

    public function sendSMS(string $recipient, string $message): bool
    {
        $params = [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->apicode,
            'Recipients' => [$recipient],
            'Message' => $message
        ];
        $response = Http::post('https://api.itexmo.com/api/broadcast', $params);

        return $response->ok();
    }
}
