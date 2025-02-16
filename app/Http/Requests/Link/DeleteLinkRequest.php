<?php

declare(strict_types=1);

namespace App\Http\Requests\Link;

use App\Http\Requests\BaseRequest;

/**
 * DeleteLinkRequest is the form request that handles the validation of the delete link request.
 *
 * @class DeleteLinkRequest
 */
final class DeleteLinkRequest extends BaseRequest
{
    /**
     * @inheritDoc
     */
    public function authorize(): bool
    {
        return (auth()->user()?->id === $this->route('link')->user_id)
            || is_admin();
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return []; // No validation needed for deletion
    }
}
