<?php

declare(strict_types=1);

namespace CA\Ui\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class CaUiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * Verifies that the current user is authenticated before allowing
     * access to the CA admin dashboard. Host applications can replace
     * this middleware via the config to add role-based checks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
