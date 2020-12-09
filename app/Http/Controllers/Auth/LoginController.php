<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use stdClass;

class LoginController extends Controller
{

    /* 
    * Login methods derived from here
    */
    use AuthenticatesUsers;

    private $isAuthenticated;

    public function __construct()
    {
        $this->isAuthenticated = new stdClass();
        $this->isAuthenticated->error = "";
    }


    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->setIsAuthenticated(false);
        return $this->loggedOut($request) ?: response()->json($this->isAuthenticated);
    }

    public function checkIsAuthenticated()
    {
        if (Auth::check()) {
            $username = Auth::user()->display_name;
            $this->setIsAuthenticated(true, $username);
        } else {
            $this->setIsAuthenticated(false);
        }
        return response()->json($this->isAuthenticated);
    }

    protected function sendLoginResponse(Request $request)
    {
        $username = Auth::user()->display_name;
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        $this->setIsAuthenticated(true, $username);
        return $this->authenticated($request, $this->guard()->user())
            ?: response()->json($this->isAuthenticated);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $this->setIsAuthenticated(false, '', [trans('auth.failed')]);
        return response()->json($this->isAuthenticated);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        $this->setIsAuthenticated(false, '', [Lang::get('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ])]);

        return response()->json($this->isAuthenticated);
    }

    private function setIsAuthenticated($status, $username = '', $error = '')
    {
        $this->isAuthenticated->status = $status;
        $this->isAuthenticated->username = $username;
        $this->isAuthenticated->error = $error;
    }
}
