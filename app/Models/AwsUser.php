<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\LogsActivityTrait;
use Carbon\Carbon;
use Database\Factories\AwsUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $aws_collection_id
 * @property string $external_user_id
 * @property string|null $external_user_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read AwsCollection $awsCollection
 *
 * @class AwsUser
 */
final class AwsUser extends Model
{
    /** @use HasFactory<AwsUserFactory> */
    use HasFactory, LogsActivityTrait;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who owns this AWS user.
     *
     * @return BelongsTo<User, covariant $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the AWS collection that this AWS user belongs to.
     *
     * @return BelongsTo<AwsCollection, covariant $this>
     */
    public function awsCollection(): BelongsTo
    {
        return $this->belongsTo(AwsCollection::class);
    }
}
