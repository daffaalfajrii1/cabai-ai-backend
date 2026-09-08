<?php

namespace App\Support;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class AdminAudit
{
    public static function record(?User $actor, string $action, ?Model $subject = null, ?string $description = null): void
    {
        try {
            AdminAuditLog::create([
                'user_id' => $actor?->id,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'description' => $description,
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
