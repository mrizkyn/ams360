<?php

namespace App\Http\Middleware;

use Closure;

class CheckForAdmin
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
        if ($request->user()->role != 'admin'){
            return abort(403);
        }

        return $next($request);
    }
}
