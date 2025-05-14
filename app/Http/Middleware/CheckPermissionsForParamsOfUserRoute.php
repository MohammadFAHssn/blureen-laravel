<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

use Spatie\Permission\Exceptions\UnauthorizedException;

class CheckPermissionsForParamsOfUserRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $permission = 'read active-users';
        if (($request->query('filter', [])['active'] ?? '') === 'true') {
            if (!$user->can($permission)) {
                throw UnauthorizedException::forPermissions([$permission]);
            }
        }

        return $next($request);
    }
}
