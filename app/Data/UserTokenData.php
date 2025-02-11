<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\LaravelData\Data;

/**
 * UserTokenData is a Data transfer object (DTO) that represents the data related to user token.
 *
 * @property User|Authenticatable $user
 * @property string|null $message
 * @property string|null $token
 *
 * @class UserTokenData
 */
final class UserTokenData extends Data
{
    public function __construct(
        public User|Authenticatable $user,
        public ?string $message = null,
        public ?string $token = null,
    ) {}
}
