<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModulePermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $module, string $level = 'read'): Response
    {
        abort_unless($request->user()?->hasModuleAccess($module, $level), 403);

        return $next($request);
    }
}