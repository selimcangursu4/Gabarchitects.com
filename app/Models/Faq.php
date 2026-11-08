<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'question', 'answer', 'sort_order', 'is_active'])]
class Faq extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query, string $group = 'general'): Builder
    {
        return $query->where('group', $group)->where('is_active', true)->orderBy('sort_order');
    }
}
