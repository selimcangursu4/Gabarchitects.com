<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MediaPolicy extends PermissionPolicy
{
    protected string $permission = 'media.manage';

    /** Kullanımdaki (kapak, galeri, logo...) görsel silinemez. */
    public function delete(User $user, Model $model): bool
    {
        return $this->allowed($user) && $model->usageCount() === 0;
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->delete($user, $model);
    }
}
