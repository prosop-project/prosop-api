<?php

declare(strict_types=1);

namespace App\Http\Requests\ActivityLog;

use App\Http\Requests\BaseRequest;

/**
 * DeleteActivityLogRequest is the form request that handles the validation of the delete activity log request.
 *
 * @property int|null $days
 * @property string|null $log_name
 *
 * @class DeleteActivityLogRequest
 */
final class DeleteActivityLogRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', 'min:1'],
            'log_name' => ['nullable', 'string'],
        ];
    }
}
