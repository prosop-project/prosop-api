<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

/**
 * DeleteUserRequest is the form request that handles the validation of the delete user and authorization including the admin.
 *
 * @property string $password
 *
 * @class DeleteUserRequest
 */
final class DeleteUserRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): bool
    {
        return (auth()->user()?->id === $this->route('user')->id)
            || is_admin();
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
