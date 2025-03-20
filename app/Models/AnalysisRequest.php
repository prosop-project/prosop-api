<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\LogsActivityTrait;
use Carbon\Carbon;
use Database\Factories\AnalysisRequestFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $aws_collection_id
 * @property string $operation
 * @property string|null $status
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read AwsCollection $awsCollection
 * @property-read Collection<int, AwsSimilarityResult> $awsSimilarityResults
 *
 * @class AnalysisRequest
 */
final class AnalysisRequest extends Model
{
    /** @use HasFactory<AnalysisRequestFactory> */
    use HasFactory, LogsActivityTrait;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who owns this analysis request.
     *
     * @return BelongsTo<User, covariant $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the AWS collection that this analysis request belongs to.
     *
     * @return BelongsTo<AwsCollection, covariant $this>
     */
    public function awsCollection(): BelongsTo
    {
        return $this->belongsTo(AwsCollection::class);
    }

    /**
     * Get the AWS similarity results for this analysis request.
     *
     * @return HasMany<AwsSimilarityResult, covariant $this>
     */
    public function awsSimilarityResults(): HasMany
    {
        return $this->hasMany(AwsSimilarityResult::class);
    }
}
