<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Owner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
// fungsi untuk memproses dan memastikan hanya user dengan role 'owner' yang dapat mengakses
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // ini akan otomatis ambil user dari token (misal Sanctum)

        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized. Only owner can access.'], 403);
        }

        return $next($request);
    }
}