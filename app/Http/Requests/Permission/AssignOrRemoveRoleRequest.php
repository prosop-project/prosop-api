<?php

declare(strict_types=1);

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * AssignOrRemoveRoleRequest is the form request that handles the validation of the assign or remove role.
 *
 * @property string $role
 *
 * @class AssignOrRemoveRoleRequest
 */
final class AssignOrRemoveRoleRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(Role::query()->pluck('name')->toArray())],
        ];
    }
}
