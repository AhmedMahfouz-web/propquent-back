<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Services\JwtAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    protected JwtAuthService $jwtAuthService;

    public function __construct(JwtAuthService $jwtAuthService)
    {
        $this->jwtAuthService = $jwtAuthService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Validate token and check blacklist
            if (!$this->jwtAuthService->validateToken()) {
                return ApiResponse::unauthorized('Invalid or expired token');
            }

            return $next($request);
            
        } catch (\Exception $e) {
            return ApiResponse::unauthorized('Authentication failed');
        }
    }
}
