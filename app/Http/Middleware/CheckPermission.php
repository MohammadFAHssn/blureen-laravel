<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
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

        $user = $request->user();

        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        $fullUrl = parse_url(
            urldecode(
                $request->fullUrl()
            )
        );

        $url =
            substr($fullUrl['path'], 4) // remove /api
            . (!empty($fullUrl['query']) ? ('?' . $fullUrl['query']) : '');

        $url = preg_replace('/\$\{[^}]+\}/', '$', $url);

        Log::info('Checking permission for URL: ' . $url);

        $permissionName = Permission::whereUrl($url)->pluck('name')->first();

        if (!$permissionName) {
            throw new CustomException('هیچ مجوزی برای این مسیر تعریف نشده‌است.', 403);
        }

        if (!$user->can($permissionName)) {
            throw new CustomException('دسترسی به این مسیر مجاز نمی‌باشد.', 403);
        }

        $request = $this->resolveUrl($request);

        return $next($request);
    }

    private function resolveUrl($request)
    {
        $filters = $request->query('filter', []);

        foreach ($filters as $key => $value) {
            $filters[$key] = preg_replace('/\$\{([^}]+)\}/', '$1', $value);

            if ($key === 'user_id' && $value === 'current') {
                $filters[$key] = $request->user()->id;
            }
        }

        $request->query->set('filter', $filters);
        return $request;

    }
}
