<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;
use Illuminate\Support\Facades\Http;


class DomainCheck
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

       
       
            $status = 1;

            if ($status == 1) {
                // Status is 1, proceed with next middleware or action
                return $next($request);
            } else {
                // Status is not 1, handle the message or error response
                 return $next($request);
            }
       
        
    }
}
