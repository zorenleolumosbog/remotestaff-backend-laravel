<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorporateRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!auth()->user()->basicInfo()->first()->where('registrant_type', 3)->exists()) {
            return response()->json(
                ['error' => 'Unauthorized.'
            ], 401);
        }

        return $next($request);
    }
}
