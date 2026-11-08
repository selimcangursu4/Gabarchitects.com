<?php

namespace App\Policies;

class TeamMemberPolicy extends PermissionPolicy
{
    protected string $permission = 'team.manage';
}
