<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /** Pakai: ->middleware('role:owner') atau 'role:owner,kasir' */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        abort_unless(in_array($request->user()?->role, $roles, true), 403, 'Anda tidak punya akses ke halaman ini.');

        return $next($request);
    }
}
