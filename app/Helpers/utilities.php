<?php

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
