<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\LogsActivityTrait;
use Carbon\Carbon;
use Database\Factories\AwsFaceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

/**
 * @property int $id
 * @property int|null $aws_user_id
 * @property int $aws_collection_id
 * @property string $external_face_id
 * @property float $confidence
 * @property string|null $external_image_id
 * @property string $image_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read AwsUser $awsUser
 * @property-read AwsCollection $awsCollection
 * @property-read Collection<int, AwsSimilarityResult> $awsSimilarityResults
 *
 * @class AwsFace
 */
final class AwsFace extends Model
{
    /** @use HasFactory<AwsFaceFactory> */
    use HasFactory, KeepsDeletedModels, LogsActivityTrait;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'confidence' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the AWS user that this AWS face belongs to.
     *
     * @return BelongsTo<AwsUser, covariant $this>
     */
    public function awsUser(): BelongsTo
    {
        return $this->belongsTo(AwsUser::class);
    }

    /**
     * Get the AWS collection that this AWS face belongs to.
     *
     * @return BelongsTo<AwsCollection, covariant $this>
     */
    public function awsCollection(): BelongsTo
    {
        return $this->belongsTo(AwsCollection::class);
    }

    /**
     * Get the AWS similarity results for this AWS face (AWS face has many AWS similarity results).
     *
     * @return HasMany<AwsSimilarityResult, covariant $this>
     */
    public function awsSimilarityResults(): HasMany
    {
        return $this->hasMany(AwsSimilarityResult::class);
    }
}
