<?php

namespace App\Policies;

class RedirectPolicy extends PermissionPolicy
{
    protected string $permission = 'redirects.manage';
}
