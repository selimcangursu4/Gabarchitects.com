<?php

namespace App\Policies;

class BlogCategoryPolicy extends PermissionPolicy
{
    protected string $permission = 'blog.manage';
}
