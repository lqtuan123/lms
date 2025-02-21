<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Auth;
class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if (!auth()->check() || !auth()->user()->isAdmin()) {
        //     return redirect('/login');
        // }
        // dd(Auth::check());

        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            } else {
                return redirect()->route('admin.login'); // Custom login route
            }
        }
        return $next($request);
    }
}
