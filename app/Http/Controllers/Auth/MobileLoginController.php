<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileLoginController extends Controller
{
    /**
     * get login user information
     * 
     */
    public function loginUser(Request $request) {

        return $request->user();
    }

    /**
     * make token from mobile login request
     * 
     */
    public function makeToken(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    
        return $user->createToken($request->device_name)->plainTextToken;
    }

    /**
     * delete old token after logout request from mobile
     * 
     */
    public function revokeToken(Request $request) {

        $user = $request->user();
        $user->token()->delete();
        return 'token deleted';
    }
}
