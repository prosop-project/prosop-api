<?php

declare(strict_types=1);

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;

/**
 * RoleRequest is the form request that handles the validation of the create role.
 *
 * @property string $name
 *
 * @class RoleRequest
 */
final class RoleRequest extends BaseRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles'],
        ];
    }
}
