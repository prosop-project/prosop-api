<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Rules\Username;
use Illuminate\Validation\Rules\Password;

/**
 * RegisterRequest is the form request that handles the validation of the register user.
 *
 * @property string|null $name
 * @property string $username
 * @property string|null $description
 * @property string $password
 * @property string|null $email
 *
 * @class RegisterRequest
 */
final class RegisterRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username', new Username],
            'description' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', Password::defaults()],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
        ];
    }
}
