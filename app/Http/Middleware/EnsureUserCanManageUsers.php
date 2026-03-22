<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageUsers
{
    private const MANAGER_ALLOWED_ROUTES = [
        'admin.users.show',
        'admin.users.edit',
        'admin.users.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user();
        if (! $actor instanceof User || ! $actor->is_admin) {
            abort(403);
        }

        if ($actor->isAdministrator()) {
            return $next($request);
        }

        if (! $actor->isManager()) {
            abort(403);
        }

        $routeName = $request->route()?->getName();
        if (! in_array($routeName, self::MANAGER_ALLOWED_ROUTES, true)) {
            abort(403);
        }

        $target = $request->route('user');
        if (! $target instanceof User || (int) $target->getKey() !== (int) $actor->getKey()) {
            abort(403);
        }

        return $next($request);
    }
}
