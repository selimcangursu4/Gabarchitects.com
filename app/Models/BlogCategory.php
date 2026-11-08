<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'description', 'sort_order', 'meta_title', 'meta_description'])]
class BlogCategory extends Model
{
    use HasFactory, HasSlug, LogsActivity, SoftDeletes;

    protected string $slugFrom = 'name';

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
