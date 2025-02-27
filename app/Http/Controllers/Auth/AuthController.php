<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\RegisterUserAction;
use App\Data\UserTokenData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\UserTokenResource;
use Illuminate\Http\JsonResponse;
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
     * @param RegisterUserAction $registerUserAction
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request, RegisterUserAction $registerUserAction): JsonResponse
    {
        $userTokenData = $registerUserAction->handle($request);

        return $this->respondWithToken(
            userTokenData: $userTokenData,
            status: 201
        );
    }

    /**
     * Login the user with username and password, and return the token.
     *
     * @param LoginRequest $request
     * @param LoginAction $loginAction
     *
     * @return JsonResponse
     */
    public function login(LoginRequest $request, LoginAction $loginAction): JsonResponse
    {
        // Check if the user is already authenticated.
        if (($token = JWTAuth::getToken()) && ($user = JWTAuth::authenticate())) {
            // Set the user token data.
            $userTokenData = new UserTokenData(
                user: $user,
                message: 'User already logged in!',
                token: $token,
            );

            // Early return the user and token.
            return $this->respondWithToken($userTokenData);
        }

        // Get the validated credentials.
        $credentials = $request->validated();

        // Attempt to login the user with the provided credentials.
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Handle the login action.
        $userTokenData = $loginAction->handle($token);

        return $this->respondWithToken($userTokenData);
    }

    /**
     * Logout the authenticated user.
     *
     * @return GenericResponseResource
     */
    public function logout(): GenericResponseResource
    {
        // Invalidate the token if it exists.
        if (JWTAuth::getToken()) {
            JWTAuth::parseToken()->invalidate(true);
        }

        return new GenericResponseResource('Successfully logged out!');
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
