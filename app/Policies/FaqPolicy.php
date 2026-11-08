<?php

namespace App\Policies;

class FaqPolicy extends PermissionPolicy
{
    protected string $permission = 'faqs.manage';
}
