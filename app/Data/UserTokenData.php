<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * UserTokenData is a Data transfer object (DTO) that represents the data related to user token.
 *
 * @property mixed $user
 * @property string|null $message
 * @property mixed $token
 *
 * @class UserTokenData
 */
final class UserTokenData extends Data
{
    public function __construct(
        public readonly mixed $user,
        public readonly ?string $message = null,
        public readonly mixed $token = null,
    ) {}
}
