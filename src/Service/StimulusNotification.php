<?php

namespace App\Service;

use GuzzleHttp\Client;

class StimulusNotification
{
    const PUSHER_NOTIFICATION_URL = 'http://stimulus.mutuelleawoundjo.com';

    const COMMANDE_CHANNEL = 'zlboutik-commande';


    public function sendCommandeNotification($data): void
    {
        $_data = json_encode($data, JSON_FORCE_OBJECT);

        $fields = [
            'channel' => self::COMMANDE_CHANNEL,
            'data' => $_data,
        ];

        $client = new Client(['base_uri' => self::PUSHER_NOTIFICATION_URL]);

        $response = $client->post('/pusher', [
            'form_params' => $fields
        ]);

        $body = $response->getBody();
        //echo($body);
        //die();
    }
}