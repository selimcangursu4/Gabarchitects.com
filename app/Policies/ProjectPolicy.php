<?php

namespace App\Policies;

class ProjectPolicy extends PermissionPolicy
{
    protected string $permission = 'projects.manage';
}
