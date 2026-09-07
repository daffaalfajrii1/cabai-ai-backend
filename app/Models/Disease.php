<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disease extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_class_name',
        'slug',
        'name',
        'scientific_name',
        'description',
        'symptoms',
        'cause',
        'treatment',
        'prevention',
        'is_healthy',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_healthy' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
