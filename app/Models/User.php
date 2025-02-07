<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $name
 * @property string $username
 * @property string|null $description
 * @property string|null $password
 * @property int $views
 * @property string|null $avatar
 * @property Carbon|null $avatar_updated_at
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property string|null $phone
 * @property Carbon|null $phone_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, Link> $links
 */
final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'phone_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
