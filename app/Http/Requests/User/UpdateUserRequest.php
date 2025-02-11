<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Models\User;
use App\Rules\Username;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * UpdateUserRequest is the form request that handles the validation of the update user.
 *
 * @property string|null $name
 * @property string $username
 * @property string|null $description
 * @property string|null $email
 *
 * @class UpdateUserRequest
 */
final class UpdateUserRequest extends BaseRequest
{
    /**
     * @inheritDoc
     */
    public function authorize(): bool
    {
        // TODO: also add permission_bits logic for admin
        return Auth::check() && Auth::user()?->is($this->route('user'));
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $user = Auth::user();

        return [
            'name' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', Rule::unique(User::class)->ignore($user->id), new Username($user)],
            'description' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ];
    }
}
