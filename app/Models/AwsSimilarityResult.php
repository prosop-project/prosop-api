<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\LogsActivityTrait;
use Carbon\Carbon;
use Database\Factories\AwsSimilarityResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $analysis_operation_id
 * @property int|null $aws_user_id
 * @property int|null $aws_face_id
 * @property float $similarity
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read AnalysisOperation $analysisOperation
 * @property-read AwsUser $awsUser
 * @property-read AwsFace $awsFace
 *
 * @class AwsSimilarityResult
 */
final class AwsSimilarityResult extends Model
{
    /** @use HasFactory<AwsSimilarityResultFactory> */
    use HasFactory, LogsActivityTrait;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'similarity' => 'float',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the analysis operation that this AWS similarity result belongs to.
     *
     * @return BelongsTo<AnalysisOperation, covariant $this>
     */
    public function analysisOperation(): BelongsTo
    {
        return $this->belongsTo(AnalysisOperation::class);
    }

    /**
     * Get the AWS user that this AWS similarity result belongs to.
     *
     * @return BelongsTo<AwsUser, covariant $this>
     */
    public function awsUser(): BelongsTo
    {
        return $this->belongsTo(AwsUser::class);
    }

    /**
     * Get the AWS face that this AWS similarity result belongs to.
     *
     * @return BelongsTo<AwsFace, covariant $this>
     */
    public function awsFace(): BelongsTo
    {
        return $this->belongsTo(AwsFace::class);
    }
}
