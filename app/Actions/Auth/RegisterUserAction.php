<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\UserTokenData;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @class RegisterUserAction
 */
final readonly class RegisterUserAction
{
    /**
     * Handle the action.
     *
     * @param RegisterRequest $request
     *
     * @return UserTokenData
     */
    public function handle(RegisterRequest $request): UserTokenData
    {
        // Create a new user.
        $user = User::query()->create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'description' => $request->description,
            'email' => $request->email,
        ]);

        // Generate a token for the user.
        $token = JWTAuth::fromUser($user);

        // Create the user token data and return it.
        return new UserTokenData(
            user: $user,
            message: 'User registered successfully!',
            token: $token
        );
    }
}
