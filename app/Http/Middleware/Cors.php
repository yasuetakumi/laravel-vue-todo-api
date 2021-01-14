<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request)
            ->header('Access-Control-Allow-Origin', config('cors.allowed_origins','http://localhost:3000') )
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Credentials', config('cors.supports_credentials', false )? "true" : "false")
            // ->header('Access-Control-Allow-Headers', 'Content-Type,Authorization,X-Socket-ID,X-Requested-With,X-CSRF-Token');
            ->header('Access-Control-Allow-Headers', 'Origin,Content-Type,Authorization,X-Socket-ID,X-Requested-With,X-CSRF-Token,X-XSRF-TOKEN');
    }
}
