<?php

namespace App\Http\Controllers\Auth;

use App\Data\UserTokenData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserTokenResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @class AuthController
 */
final readonly class AuthController extends Controller
{
    /**
     * Register a new user and return the token.
     *
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'description' => $request->description,
            'email' => $request->email,
        ]);

        // TODO:  event(new Registered($user)); and SendEmailVerificationNotification can be set in case email is set,

        $token = JWTAuth::fromUser($user);

        // Login the user who just registered.
        Auth::login($user);

        // TODO: update user avatar job can be added here and other places after implemented UpdateUserAvatar::dispatch($user);

        // Set the user token data.
        $userTokenData = new UserTokenData(
            user: $user,
            message: 'User registered successfully!',
            token: $token
        );

        return $this->respondWithToken(
            userTokenData: $userTokenData,
            status: 201
        );
    }

    /**
     * Login the user with username and password, and return the token.
     *
     * @param LoginRequest $request
     *
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Check if the user is already logged in.
        if ($token = JWTAuth::getToken()) {
            if ($user = JWTAuth::authenticate($token)) {

                // Set the user token data.
                $userTokenData = new UserTokenData(
                    user: $user,
                    message: 'User already logged in!',
                    token: $token,
                );

                // Early return the user and token.
                return $this->respondWithToken($userTokenData);
            }
        }

        // Get the validated credentials.
        $credentials = $request->validated();

        // Attempt to login the user with the provided credentials.
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Get the authenticated user.
        $user = Auth::getUser();

        $userTokenData = new UserTokenData(
            user: $user,
            message: 'Login successful!',
            token: $token,
        );

        return $this->respondWithToken($userTokenData);
    }

    /**
     * Logout the authenticated user.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        // Invalidate the token if it exists.
        if ($token = JWTAuth::getToken()) {
            JWTAuth::invalidate($token);
        }

        return response()->json(['message' => 'Successfully logged out!']);
    }

    /**
     * Respond with the user, token and message by wrapping it in the UserTokenResource.
     *
     * @param UserTokenData $userTokenData
     * @param int $status
     *
     * @return JsonResponse
     */
    private function respondWithToken(UserTokenData $userTokenData, int $status = 200): JsonResponse
    {
        return (new UserTokenResource($userTokenData))->response()->setStatusCode($status);
    }
}
