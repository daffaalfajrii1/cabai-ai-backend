<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detection extends Model
{
    use HasFactory;

    public const REVIEW_PENDING = 'pending';

    public const REVIEW_VERIFIED = 'verified';

    public const REVIEW_CORRECTED = 'corrected';

    public const REVIEW_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'disease_id',
        'image_path',
        'ai_class_id',
        'ai_class_name',
        'confidence',
        'confidence_percent',
        'needs_retake',
        'valid_input',
        'classification_source',
        'message',
        'top_predictions',
        'raw_ai_response',
        'status',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'corrected_disease_id',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:6',
            'confidence_percent' => 'decimal:2',
            'needs_retake' => 'boolean',
            'valid_input' => 'boolean',
            'top_predictions' => 'array',
            'raw_ai_response' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    public function correctedDisease(): BelongsTo
    {
        return $this->belongsTo(Disease::class, 'corrected_disease_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function reviewStatuses(): array
    {
        return [
            self::REVIEW_PENDING,
            self::REVIEW_VERIFIED,
            self::REVIEW_CORRECTED,
            self::REVIEW_REJECTED,
        ];
    }

    public static function reviewStatusLabels(): array
    {
        return [
            self::REVIEW_PENDING => 'Belum Ditinjau',
            self::REVIEW_VERIFIED => 'Terverifikasi',
            self::REVIEW_CORRECTED => 'Dikoreksi',
            self::REVIEW_REJECTED => 'Ditolak',
        ];
    }

    public function reviewStatus(): string
    {
        return $this->review_status ?: self::REVIEW_PENDING;
    }

    public function reviewStatusLabel(): string
    {
        return self::reviewStatusLabels()[$this->reviewStatus()] ?? 'Belum Ditinjau';
    }

    public function isLowConfidence(): bool
    {
        if ($this->confidence !== null) {
            return (float) $this->confidence < 0.70;
        }

        return $this->confidence_percent !== null && (float) $this->confidence_percent < 70;
    }

    public function isVeryLowConfidence(): bool
    {
        if ($this->confidence !== null) {
            return (float) $this->confidence < 0.40;
        }

        return $this->confidence_percent !== null && (float) $this->confidence_percent < 40;
    }

    public function reviewPriority(): string
    {
        if ($this->valid_input === false || $this->isVeryLowConfidence()) {
            return 'HIGH';
        }

        if ($this->needs_retake || $this->isLowConfidence()) {
            return 'MEDIUM';
        }

        return 'NORMAL';
    }

    public function reviewReasons(): array
    {
        $reasons = [];

        if ($this->reviewStatus() === self::REVIEW_PENDING) {
            $reasons[] = 'Belum ditinjau';
        }

        if ($this->valid_input === false) {
            $reasons[] = 'Invalid input';
        }

        if ($this->needs_retake) {
            $reasons[] = 'Needs retake';
        }

        if ($this->isLowConfidence()) {
            $reasons[] = 'Confidence < 70%';
        }

        return $reasons ?: ['Normal'];
    }

    public function scopeNeedsAdminReview(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('review_status', self::REVIEW_PENDING)
                ->orWhereNull('review_status')
                ->orWhere('needs_retake', true)
                ->orWhere('valid_input', false)
                ->orWhere('confidence', '<', 0.70)
                ->orWhere('confidence_percent', '<', 70);
        });
    }

    public function scopeValidDiseasePrediction(Builder $query): Builder
    {
        return $query->where('valid_input', true)->whereNotNull('disease_id');
    }
}
