<?php

namespace App\Policies;

class ServicePolicy extends PermissionPolicy
{
    protected string $permission = 'services.manage';
}
