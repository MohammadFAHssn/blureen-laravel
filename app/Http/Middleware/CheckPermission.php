<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
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
            . '?' . $fullUrl['query'];

        $permissionName = Permission::whereUrl($url)->pluck('name')->first();

        if (!$permissionName) {
            throw new CustomException('خطا: هیچ مجوزی برای این مسیر تعریف نشده‌است.');
        }

        $user = $request->user();

        if (!$user->can($permissionName)) {
            throw UnauthorizedException::forPermissions([$permissionName]);
        }

        return $next($request);
    }
}
