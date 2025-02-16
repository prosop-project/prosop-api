<?php

declare(strict_types=1);

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;

/**
 * PermissionRequest is the form request that handles the validation of the create permission.
 *
 * @property string $name
 *
 * @class PermissionRequest
 */
final class PermissionRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:permissions'],
        ];
    }
}
