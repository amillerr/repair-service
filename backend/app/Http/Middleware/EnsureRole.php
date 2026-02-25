<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Проверка роли пользователя. Использование: ->middleware('role:master') или ->middleware('role:dispatcher').
     *
     * @param  string  ...$roles  Роли, допускаемые через запятую в маршруте (role:master,dispatcher).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (! in_array($request->user()->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
