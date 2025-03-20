<?php

declare(strict_types=1);

namespace App\Casts;

use App\Enums\ExternalUserStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom cast for the external user status attribute which normalizes the status to lowercase (AwsUser model external_user_status attribute).
 *
 * @implements CastsAttributes<string|null, string|null>
 */
final readonly class LowercaseStatusCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value ? ExternalUserStatus::normalize($value) : null;
    }
}
