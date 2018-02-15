<?php

namespace App\Http\Middleware;

use Closure;

class ExampleMiddleware
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS');
        $response->header('Access-Control-Allow-Credentials', 'true');
        //$response->header('another header', 'another value');


        return $response;
    
    }
}
