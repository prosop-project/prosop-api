<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\LowercaseStatusCast;
use App\Models\Traits\LogsActivityTrait;
use Carbon\Carbon;
use Database\Factories\AwsUserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property-read Collection<int, AwsFace> $awsFaces
 * @property-read Collection<int, AwsSimilarityResult> $awsSimilarityResults
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
            'external_user_status' => LowercaseStatusCast::class,
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

    /**
     * Get the AWS faces that belong to this AWS user.
     *
     * @return HasMany<AwsFace, covariant $this>
     */
    public function awsFaces(): HasMany
    {
        return $this->hasMany(AwsFace::class);
    }

    /**
     * Get the AWS similarity results for this AWS user (AWS user has many AWS similarity results).
     *
     * @return HasMany<AwsSimilarityResult, covariant $this>
     */
    public function awsSimilarityResults(): HasMany
    {
        return $this->hasMany(AwsSimilarityResult::class);
    }
}
