<?php

namespace App\Policies;

class BlogPostPolicy extends PermissionPolicy
{
    protected string $permission = 'blog.manage';
}
