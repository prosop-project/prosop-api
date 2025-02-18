<?php

declare(strict_types=1);

namespace App\Http\Requests\Link;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Validator;

/**
 * CreateLinkRequest is the form request that handles the validation of the create link request.
 *
 * @property string|null $type
 * @property string|null $description
 * @property string $value
 * @property bool $is_visible
 *
 * @class CreateLinkRequest
 */
final class CreateLinkRequest extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): bool
    {
        return (auth()->id() === $this->route('user')->id)
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

    /**
     * The validation is called after the rules method.
     *
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $user = auth()->user();

            // Add condition to check user link count.
            if ($user->links()->count() >= 5) {
                $validator->errors()->add('link', 'You can have a maximum of 5 links.');
            }
        });
    }
}
