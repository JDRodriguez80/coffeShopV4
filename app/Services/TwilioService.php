<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $this->from = config('services.twilio.whatsapp_from');
    }

    public function enviarWhatsApp($to, $message)
    {
        return $this->client->messages->create(
            "whatsapp:$to",
            [
                'from' => $this->from,
                'body' => $message
            ]
        );
    }
}
