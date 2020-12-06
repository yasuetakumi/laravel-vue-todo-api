<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('hello', function (Request $request) {
    $minutes = 60;
    $path = "";
    $domain = "localhost";
    $response =  response()
        ->json($_COOKIE)
        ->cookie('testCookie2', 'testCookie2', $minutes, $path, $domain)
        ->withHeaders([
            'X-Header-One' => 'Header Value',
            'X-Header-Two' => 'Header Value',
        ]);
    // dd($response);
    return $response;
});
