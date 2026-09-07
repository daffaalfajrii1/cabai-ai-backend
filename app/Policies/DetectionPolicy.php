<?php

namespace App\Policies;

use App\Models\Detection;
use App\Models\User;

class DetectionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Detection $detection): bool
    {
        return $user->isAdmin() || $detection->user_id === $user->id;
    }
}
