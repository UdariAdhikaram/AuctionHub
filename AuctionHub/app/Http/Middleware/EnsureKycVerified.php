<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isKycVerified()) {
            abort(403, 'KYC verification required to place bids.');
        }

        return $next($request);
    }
}
