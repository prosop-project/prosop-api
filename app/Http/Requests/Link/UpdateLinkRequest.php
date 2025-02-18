<?php

declare(strict_types=1);

namespace App\Http\Requests\Link;

use App\Http\Requests\BaseRequest;

/**
 * UpdateLinkRequest is the form request that handles the validation of the update link request.
 *
 * @property string|null $type
 * @property string|null $description
 * @property string $value
 * @property bool $is_visible
 *
 * @class UpdateLinkRequest
 */
final class UpdateLinkRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): bool
    {
        return (auth()->id() === $this->route('link')->user_id)
            || is_admin();
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'is_visible' => ['required', 'boolean'],
        ];
    }
}
