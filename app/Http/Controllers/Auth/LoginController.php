<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use stdClass;

class LoginController extends Controller {

    /* 
    * Login methods derived from here
    */
    use AuthenticatesUsers;
    private $sessionGuards;
    private $authInfo;
    private $guard;
    private $authGuard;

    public function __construct(Request $request) {
        $this->sessionGuards = Arr::where(config('auth.guards'), function ($value, $key) {
            return $key == 'web' ? false : $value['driver'] == 'session';
        });
        $this->guard = $request->guard ?: '';
        $this->authGuard = Auth::guard($this->guard);
        $this->authInfo = new AuthInfo($this->guard);
    }

    public function checkIsAuthenticated() {
        $isAuthenticated = false;
        /*
        * Default guard ('web')
        */
        if (Auth::guard()->check()) {
            $isAuthenticated = true;
            $username = Auth::user()->display_name;
            $this->authInfo->setAuthenticated($username);
            return response()->json($this->authInfo);
        }

        /*
        * Other session guards
        */
        foreach ($this->sessionGuards as $key => $value) {
            if (Auth::guard($key)->check()) {
                $isAuthenticated = true;
                $username = Auth::guard($key)->user()->display_name;
                $this->authInfo->setAuthenticated($username, $key);
                break;
            }
        }
        if ($isAuthenticated) return response()->json($this->authInfo);

        else {
            return response()->json($this->authInfo->invalidate());
        }
    }

    public function login(Request $request) {
        $this->validateLogin($request);
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request) {
        $this->authGuard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json($this->authInfo->invalidate());
    }

    private function attemptLogin(Request $request) {
        return $this->authGuard->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }


    private function sendLoginResponse(Request $request) {
        $username = $this->authGuard->user()->display_name;
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        $this->authInfo->setAuthenticated($username, $this->guard);
        return response()->json($this->authInfo);
    }

    private function sendFailedLoginResponse(Request $request) {
        return response()->json($this->authInfo->invalidate()->setError([trans('auth.failed')]));
    }

    private function sendLockoutResponse(Request $request) {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        return response()->json($this->authInfo->invalidate()->setError([Lang::get('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ])]));
    }
}

class AuthInfo {
    public $status = false;
    public $guard;
    public $username = '';
    public $role = '';
    public $error = '';

    public function __construct($guard = '') {
        $this->guard = $guard;
    }

    public function setAuthenticated($username, $guard = '', $role = '') {
        $this->status = true;
        $this->username = $username;
        $this->guard = $guard;
        $this->role = $role;
        return $this;
    }

    public function invalidate() {
        $this->status = false;
        $this->username = '';
        $this->role = '';
        $this->error = '';
        return $this;
    }

    public function setError($error) {
        $this->error = $error;
        return $this;
    }
}
