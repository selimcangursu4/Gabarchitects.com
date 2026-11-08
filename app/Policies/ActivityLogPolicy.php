<?php

namespace App\Policies;

use App\Models\User;

/** İşlem kayıtları salt okunur: kimse oluşturamaz, değiştiremez, silemez. */
class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('activity_logs.view');
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function create(): bool
    {
        return false;
    }

    public function update(): bool
    {
        return false;
    }

    public function delete(): bool
    {
        return false;
    }

    public function deleteAny(): bool
    {
        return false;
    }
}
