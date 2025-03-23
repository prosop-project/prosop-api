<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\LogsActivityTrait;
use Carbon\Carbon;
use Database\Factories\AwsCollectionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $external_collection_id
 * @property string $external_collection_arn
 * @property array<string, mixed>|null $tags
 * @property string|null $face_model_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, AwsUser> $awsUsers
 * @property-read Collection<int, AwsFace> $awsFaces
 * @property-read Collection<int, AnalysisOperation> $analysisOperations
 *
 * @class AwsCollection
 */
final class AwsCollection extends Model
{
    /** @use HasFactory<AwsCollectionFactory> */
    use HasFactory, LogsActivityTrait;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'tags' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the AWS users that belong to this AWS collection.
     *
     * @return HasMany<AwsUser, covariant $this>
     */
    public function awsUsers(): HasMany
    {
        return $this->hasMany(AwsUser::class);
    }

    /**
     * Get the AWS faces that belong to this AWS collection.
     *
     * @return HasMany<AwsFace, covariant $this>
     */
    public function awsFaces(): HasMany
    {
        return $this->hasMany(AwsFace::class);
    }

    /**
     * Get the analysis operations that belong to this AWS collection.
     *
     * @return HasMany<AnalysisOperation, covariant $this>
     */
    public function analysisOperations(): HasMany
    {
        return $this->hasMany(AnalysisOperation::class);
    }
}
