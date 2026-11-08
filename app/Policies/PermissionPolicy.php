<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Tek izinle yönetilen kaynaklar için ortak policy.
 * Alt sınıf yalnızca $permission tanımlar (ör. 'projects.manage').
 * Super Admin, AppServiceProvider'daki Gate::before ile her zaman yetkilidir.
 */
abstract class PermissionPolicy
{
    protected string $permission;

    protected function allowed(User $user): bool
    {
        return $user->is_active && $user->can($this->permission);
    }

    public function viewAny(User $user): bool
    {
        return $this->allowed($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allowed($user);
    }

    public function create(User $user): bool
    {
        return $this->allowed($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allowed($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->allowed($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->allowed($user);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->allowed($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->allowed($user);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->allowed($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->allowed($user);
    }

    public function reorder(User $user): bool
    {
        return $this->allowed($user);
    }
}
