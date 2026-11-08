<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Str;

/** Başarılı girişte son giriş zamanı/IP güncellenir ve işlem kaydına yazılır. */
class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $event->user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ])->saveQuietly();

        ActivityLog::create([
            'user_id' => $event->user->id,
            'event' => 'login',
            'subject_type' => $event->user->getMorphClass(),
            'subject_id' => $event->user->id,
            'description' => 'Panele giriş',
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 490, ''),
            'created_at' => now(),
        ]);
    }
}
