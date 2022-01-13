<?php

namespace App\Lib;

use stdClass;
use GuzzleHttp\Client;

class Chatwork {

    const API_URL_SEND_MESSAGE = 'https://api.chatwork.com/v2/rooms/%d/messages';

    // send new message Chatwork
    public function sendMessageCustomID($message)
    {
        $response = new stdClass();

        $apiKey = \config('chatwork.api_key');
        $roomID = \config('chatwork.room_id');
        $headers = ['headers' => ['X-ChatWorkToken' => $apiKey], 'form_params' => ['body' => $message]];
        $client = new Client($headers);

        $url = sprintf(self::API_URL_SEND_MESSAGE, $roomID);
        $request = $client->request('POST', $url, ['verify' => false]);

        if ($request) {
            $getResponse = json_decode($request->getBody());
            $messageId = $getResponse->message_id;

            $response->status = "success";
            $response->messageId = $messageId;
        }

        return $response;
    }
}
