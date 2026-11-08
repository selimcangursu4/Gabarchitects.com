<?php

namespace App\Policies;

class ProjectCategoryPolicy extends PermissionPolicy
{
    protected string $permission = 'projects.manage';
}
