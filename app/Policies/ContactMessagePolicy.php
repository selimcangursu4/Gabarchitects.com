<?php

namespace App\Policies;

use App\Models\User;

/** Web formları yalnızca siteden gelir; panelden oluşturulamaz. */
class ContactMessagePolicy extends PermissionPolicy
{
    protected string $permission = 'contact_messages.manage';

    public function create(User $user): bool
    {
        return false;
    }
}
