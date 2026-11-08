<?php

namespace App\Policies;

class PropertyPolicy extends PermissionPolicy
{
    protected string $permission = 'properties.manage';
}
