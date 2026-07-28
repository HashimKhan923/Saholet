<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffPermission
{
    /**
     * Gates an admin module for staff accounts (admins always pass, see
     * User::hasPermission()). The required action is inferred from the
     * route name's resourceful suffix, falling back to the HTTP method for
     * custom action routes (approve/reject/assign/etc. all read as "edit").
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        $routeName = (string) $request->route()?->getName();

        $action = match (true) {
            str_ends_with($routeName, '.create'), str_ends_with($routeName, '.store') => 'create',
            str_ends_with($routeName, '.edit'), str_ends_with($routeName, '.update') => 'edit',
            str_ends_with($routeName, '.destroy') => 'delete',
            str_ends_with($routeName, '.index'), str_ends_with($routeName, '.show') => 'view',
            default => $request->isMethod('get') ? 'view' : 'edit',
        };

        abort_unless($user?->hasPermission($module, $action), 403, 'You do not have permission to access this section.');

        return $next($request);
    }
}
