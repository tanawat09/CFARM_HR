<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: Route::middleware('permission:hr.employees,hr.departments')
     * User needs ANY of the listed permissions to pass.
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (! $request->user()) {
            return redirect('login');
        }

        foreach ($permissions as $permission) {
            if ($request->user()->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ (Unauthorized Action)');
    }
}
