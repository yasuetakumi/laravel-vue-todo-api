<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use stdClass;

class LoginController extends Controller {

    /* 
    * Login methods derived from here
    */
    use AuthenticatesUsers;

    private $isAuthenticated;
    private $guard;

    public function __construct(Request $request) {
        $this->guard = $request->guard ? Auth::guard($request->guard . 'Web') : null;
        $this->isAuthenticated = new stdClass();
        $this->isAuthenticated->error = "";
    }

    public function checkIsAuthenticated() {
        if (Auth::guard('userWeb')->check()) {
            $username = Auth::guard('userWeb')->user()->display_name;
            $this->setIsAuthenticated(true, $username);
        } else if (Auth::guard('adminWeb')->check()) {
            $username = Auth::guard('adminWeb')->user()->display_name;
            $this->setIsAuthenticated(true, $username);
        } else {
            $this->setIsAuthenticated(false);
        }
        return response()->json($this->isAuthenticated);
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
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->setIsAuthenticated(false);
        return $this->loggedOut($request) ?: response()->json($this->isAuthenticated);
    }

    private function attemptLogin(Request $request) {
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }


    private function sendLoginResponse(Request $request) {
        $username = $this->guard()->user()->display_name;
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        $this->setIsAuthenticated(true, $username);
        return $this->authenticated($request, $this->guard()->user())
            ?: response()->json($this->isAuthenticated);
    }

    private function sendFailedLoginResponse(Request $request) {
        $this->setIsAuthenticated(false, '', [trans('auth.failed')]);
        return response()->json($this->isAuthenticated);
    }

    private function sendLockoutResponse(Request $request) {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        $this->setIsAuthenticated(false, '', [Lang::get('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ])]);

        return response()->json($this->isAuthenticated);
    }

    private function guard() {
        return $this->guard;
    }

    private function setIsAuthenticated($status, $username = '', $error = '') {
        $this->isAuthenticated->status = $status;
        $this->isAuthenticated->username = $username;
        $this->isAuthenticated->error = $error;
    }
}
