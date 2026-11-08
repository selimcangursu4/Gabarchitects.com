<?php

namespace App\Models;

use App\Enums\ContactStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'email', 'phone', 'subject', 'message', 'privacy_consent', 'consented_at', 'status', 'source',
    'property_id', 'source_url', 'ip_address', 'user_agent', 'read_at', 'handled_by', 'internal_notes',
])]
class ContactMessage extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ContactStatus::class,
            'privacy_consent' => 'boolean',
            'consented_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}
