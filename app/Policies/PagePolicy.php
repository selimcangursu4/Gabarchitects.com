<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Sayfalar sabit anahtarlıdır (home, about, contact, kvkk...): oluşturulamaz, silinemez. */
class PagePolicy extends PermissionPolicy
{
    protected string $permission = 'pages.manage';

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
