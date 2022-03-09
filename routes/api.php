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

// ---------------------------------------------------------------------
// Route for Mobile (Flutter Starter-Kit)
// ---------------------------------------------------------------------
Route::post('/sanctum/token', 'Auth\MobileLoginController@makeToken');
// ---------------------------------------------------------------------

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

Route::get('/hand-shake', function(){
    return response()->json(["result"=>"___SUCCESS___"]);
});

/*
* In case we want different user provider for our session,
* we can use the following endpoints.
*/
Route::post('/{guard}/login', 'Auth\LoginController@login');
Route::get('/{guard}/logout', 'Auth\LoginController@logout');


Route::middleware(['auth:web,admin,sanctum'])->group(function () {

    // ---------------------------------------------------------------------
    // Route for Mobile (Flutter Starter-Kit)
    // ---------------------------------------------------------------------
    Route::get('/user/revoke', 'Auth\MobileLoginController@revokeToken');
    Route::get('/login-user', 'Auth\MobileLoginController@loginUser');
    // ---------------------------------------------------------------------

    Route::get('/users', 'Api\UserController@getAll');
    Route::get('/users/downloadCSV', 'Api\UserController@downloadCSV');
    Route::get('/users/create', 'Api\UserController@create');
    Route::post('/users', 'Api\UserController@store');
    // --- import user from CSV
    Route::post('/users/import-csv', 'Api\UserController@importCsv');
    // --- END import user from CSV
    Route::get('/users/{userId}', 'Api\UserController@show');
    Route::get('/users/{userId}/edit', 'Api\UserController@edit');
    Route::post('/users/{userId}', 'Api\UserController@update');
    Route::delete('/users/{userId}', 'Api\UserController@destroy');

    // DUMMY ROUTE //
    // Route::get('/dummy-meetings', 'Api\DummyMeetingController@getAll');
    // Route::get('/dummy-meetings/create', 'Api\DummyMeetingController@create');
    // Route::post('/dummy-meetings', 'Api\DummyMeetingController@store');
    // Route::get('/dummy-meetings/{meetingId}', 'Api\DummyMeetingController@show');
    // Route::get('/dummy-meetings/{meetingId}/edit', 'Api\DummyMeetingController@edit');
    // Route::post('/dummy-meetings/{meetingId}', 'Api\DummyMeetingController@update');
    // Route::delete('/dummy-meetings/{meetingId}', 'Api\DummyMeetingController@destroy');
    // meetings route
    Route::get('/meetings', 'Api\DummyMeetingController@getAll');
    Route::get('/meetings/create', 'Api\DummyMeetingController@create');
    Route::post('/meetings', 'Api\DummyMeetingController@store');
    Route::get('/meetings/{meetingId}', 'Api\DummyMeetingController@show');
    Route::get('/meetings/{meetingId}/edit', 'Api\DummyMeetingController@edit');
    Route::post('/meetings/{meetingId}', 'Api\DummyMeetingController@update');
    Route::delete('/meetings/{meetingId}', 'Api\DummyMeetingController@destroy');
    // --------------- //


    Route::get('/customers', 'Api\CustomerController@getAll');
    Route::get('/customers/create', 'Api\CustomerController@create');
    Route::post('/customers', 'Api\CustomerController@store');
    Route::get('/customers/{customerId}', 'Api\CustomerController@show');
    Route::get('/customers/{customerId}/edit', 'Api\CustomerController@edit');
    Route::post('/customers/{customerId}', 'Api\CustomerController@update');
    Route::delete('/customers/{customerId}', 'Api\CustomerController@destroy');



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
});
