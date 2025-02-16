<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * ValidateUserIsAdmin middleware is used to check if the user is an admin (or environment is not production).
 *
 * @class ValidateUserIsAdmin
 */
final readonly class ValidateUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (is_admin() || ! is_production()) {
            return $next($request);
        }

        throw new UnauthorizedException;
    }
}
