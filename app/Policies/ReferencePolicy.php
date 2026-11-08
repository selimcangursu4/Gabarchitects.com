<?php

namespace App\Policies;

class ReferencePolicy extends PermissionPolicy
{
    protected string $permission = 'references.manage';
}
