<?php

use App\Lib\Chatwork;
use App\Mail\ReportMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

if (!function_exists('successResponse')) {
  /**
   * Greeting a person
   *
   * @param  string $person Name
   * @return string
   */
  function successResponse($data = '', $message = "Successfully process the request", $code = 200)
  {
    $res = [
      'data' => $data,
      'message' => $message
    ];

    return response()->json($res, $code);
  }
}

if (!function_exists('errorResponse')) {
  /**
   * Greeting a person
   *
   * @param  string $person Name
   * @return string
   */
  function errorResponse($message = "Unable to process the request", $code = 400)
  {
    $res = [
      'message' => $message
    ];

    return response()->json($res, $code);
  }
}

if (!function_exists('sendError')) {
    /**
     * Greeting a person
     *
     * @param  string $person Name
     * @return string
     */
    function sendError($message = "Unable to process the request", $code = 500)
    {
        $messages = [
            'url' => url()->full(),
            'error' => $message
        ];
        Mail::to(\config('mail.to.address'))->send(new ReportMail($messages));
        $chatwork = new Chatwork();
        $chatwork->sendMessageCustomID($message);
        Log::error($message);

      return response()->json($message, $code);
    }
}

