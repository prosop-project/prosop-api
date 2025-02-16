<?php

declare(strict_types=1);

/**
 * Check if the current environment is the given value (values).
 */
if (! function_exists('is_env')) {
    function is_env(string ...$envs): bool
    {
        return app()->environment($envs);
    }
}

/**
 * Check if the current environment is production.
 */
if (! function_exists('is_production')) {
    function is_production(): bool
    {
        return is_env('production');
    }
}

/**
 * Check if the current environment is testing.
 */
if (! function_exists('is_testing')) {
    function is_testing(): bool
    {
        return is_env('testing');
    }
}

/**
 * Check if authenticated user has admin role (full_access permission).
 */
if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return auth()->check() && auth()->user()?->hasRole('admin');
    }
}
