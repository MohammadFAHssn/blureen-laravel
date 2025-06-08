<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $fullUrl = parse_url(
            urldecode(
                $request->fullUrl()
            )
        );

        $url =
            substr($fullUrl['path'], 4) // remove /api
            .'?'.$fullUrl['query'];

        $permission = Permission::where('url', $url)->pluck('name')->first();

        if (! $permission) {
            throw new \Exception('Error: no permission is defined for this URL.');
        }

        $user = $request->user();

        if (! $user->can($permission)) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        return $next($request);
    }
}
