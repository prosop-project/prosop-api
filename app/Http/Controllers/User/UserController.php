<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;

/**
 * @class UserController
 */
final readonly class UserController extends Controller
{
    /**
     * Display a single user by wrapping it in the UserResource.
     *
     * @param User $user
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        return UserResource::make($user);
    }
}
