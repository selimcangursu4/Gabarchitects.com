<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends PermissionPolicy
{
    protected string $permission = 'users.manage';

    /** Kimse kendi hesabını silemez; son aktif Super Admin silinemez. */
    public function delete(User $user, Model $model): bool
    {
        if (! $this->allowed($user) || $user->is($model)) {
            return false;
        }

        return ! ($model->hasRole(Role::SuperAdmin->value)
            && User::role(Role::SuperAdmin->value)->where('is_active', true)->count() <= 1);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->delete($user, $model);
    }

    /** Toplu silme kapalı (yanlışlıkla kendini/son yöneticiyi silmeyi önler). */
    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
