<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'disease_id',
        'image_path',
        'ai_class_id',
        'ai_class_name',
        'confidence',
        'confidence_percent',
        'needs_retake',
        'message',
        'top_predictions',
        'raw_ai_response',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:6',
            'confidence_percent' => 'decimal:2',
            'needs_retake' => 'boolean',
            'top_predictions' => 'array',
            'raw_ai_response' => 'array',
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
}
