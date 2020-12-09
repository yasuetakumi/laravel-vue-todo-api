<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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


Route::get('/sanctum/csrf-cookie', function (Request $request) {
    /*
    * Set xsrf cookie for login
    */
    return response()->json();
});

Route::get('/auth-check', 'Auth\LoginController@checkIsAuthenticated');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout');

Route::get('/hello', function (Request $request) {
    $res = Auth::check();
    $minutes = 60;
    $path = "";
    $domain = "localhost";
    $response =  response()
        ->json($res)
        ->cookie('testCookie2', 'testCookie2', $minutes, $path, $domain)
        ->withHeaders([
            'X-Header-One' => 'Header Value',
            'X-Header-Two' => 'Header Value',
        ]);
    // dd($response);
    return $response;
});
