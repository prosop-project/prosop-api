<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\LogsActivityTrait;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property string $public_uuid
 * @property string|null $name
 * @property string $username
 * @property string|null $description
 * @property string|null $password
 * @property int $views
 * @property string|null $avatar
 * @property Carbon|null $avatar_updated_at
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, Link> $links
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read Collection<int, Subscription> $subscribers
 * @property-read Collection<int, AwsUser> $awsUsers
 * @property-read Collection<int, AnalysisOperation> $analysisOperations
 * @property-read Collection<int, Role> $roles
 *
 * @class User
 */
final class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, KeepsDeletedModels, LogsActivityTrait, Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'views' => 'integer',
            'avatar_updated_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user's links.
     *
     * @return HasMany<Link, covariant $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the user's subscriptions. (Users that this user has subscribed to)
     *
     * @return HasMany<Subscription, covariant $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'subscriber_id');
    }

    /**
     * Get the user's subscribers. (Users who have subscribed to this user)
     *
     * @return HasMany<Subscription, covariant $this>
     */
    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    /**
     * Get the user's AWS users (User has many AWS users).
     *
     * @return HasMany<AwsUser, covariant $this>
     */
    public function awsUsers(): HasMany
    {
        return $this->hasMany(AwsUser::class);
    }

    /**
     * Get the user's analysis operations (User has many analysis operations).
     *
     * @return HasMany<AnalysisOperation, covariant $this>
     */
    public function analysisOperations(): HasMany
    {
        return $this->hasMany(AnalysisOperation::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function customLogOptions(LogOptions $options): LogOptions
    {
        return $options
            ->logExcept(['password'])
            ->dontLogIfAttributesChangedOnly(['views']);
    }

    /**
     * {@inheritDoc}
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
