<?php

declare(strict_types=1);

namespace App\Http\Controllers\Follower;

use App\Actions\Follower\CreateFollowerAction;
use App\Actions\Follower\DeleteFollowerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Follower\FollowRequest;
use App\Http\Resources\GenericResponseResource;
use App\Http\Resources\FollowerResource;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @class FollowerController
 */
final readonly class FollowerController extends Controller
{
    /**
     * Follow the provided user (authenticated user follows the provided user)
     *
     * @param FollowRequest $_
     * @param User $user
     * @param CreateFollowerAction $createFollowerAction
     *
     * @return FollowerResource
     */
    public function follow(
        FollowRequest $_,
        User $user,
        CreateFollowerAction $createFollowerAction
    ): FollowerResource {
        $follower = $createFollowerAction->handle($user);

        return new FollowerResource($follower);
    }

    /**
     * Unfollow the provided user (authenticated user unfollows the provided user)
     *
     * @param User $user
     * @param DeleteFollowerAction $deleteFollowerAction
     *
     * @return GenericResponseResource
     */
    public function unfollow(
        User $user,
        DeleteFollowerAction $deleteFollowerAction
    ): GenericResponseResource {
        $deleteFollowerAction->handle($user);

        return new GenericResponseResource('Follower is removed successfully!');
    }

    /**
     * Get the all users that are followed by the provided user.
     *
     * @param User $user
     *
     * @return AnonymousResourceCollection
     */
    public function following(User $user): AnonymousResourceCollection
    {
        // We get all users that is followed by the provided user.
        $followers = Follower::query()->where('follower_id', $user->id)->get();

        return FollowerResource::collection($followers);
    }

    /**
     * Get the all users that are following the provided user.
     *
     * @param User $user
     *
     * @return AnonymousResourceCollection
     */
    public function followers(User $user): AnonymousResourceCollection
    {
        // We get all users that are following the provided user.
        $followers = Follower::query()->where('user_id', $user->id)->get();

        return FollowerResource::collection($followers);
    }
}
