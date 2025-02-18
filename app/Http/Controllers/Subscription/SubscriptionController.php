<?php

declare(strict_types=1);

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\SubscribeRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @class SubscriptionController
 */
final readonly class SubscriptionController extends Controller
{
    /**
     * Create a subscription (authenticated user subscribes to provided user)
     *
     * @param SubscribeRequest $_
     * @param User $user
     *
     * @return SubscriptionResource
     */
    public function subscribe(SubscribeRequest $_, User $user): SubscriptionResource
    {
        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'subscriber_id' => auth()->id(),
        ]);

        return new SubscriptionResource($subscription);
    }

    /**
     * Delete subscription (unsubscribe authenticated user to provided user)
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function unsubscribe(User $user): JsonResponse
    {
        Subscription::query()
            ->where(['subscriber_id' => auth()->id()])
            ->where(['user_id' => $user->id])
            ->firstOrFail()
            ->delete();

        return response()->json(['message' => 'Subscription is removed successfully!']);
    }

    /**
     * Get all subscriptions of provided user.
     *
     * @param User $user
     *
     * @return AnonymousResourceCollection
     */
    public function subscriptions(User $user): AnonymousResourceCollection
    {
        // We get all users that is subscribed by provided user.
        $subscriptions = Subscription::query()->where(['subscriber_id' => $user->id])->get();

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Get all subscribers of provided user.
     *
     * @param User $user
     *
     * @return AnonymousResourceCollection
     */
    public function subscribers(User $user): AnonymousResourceCollection
    {
        // We get all users that subscribed to provided user.
        $subscribers =  Subscription::query()->where(['user_id' => $user->id])->get();

        return SubscriptionResource::collection($subscribers);
    }
}
