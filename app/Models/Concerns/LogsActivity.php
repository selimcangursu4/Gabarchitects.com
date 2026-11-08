<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Panel kullanıcısının yaptığı değişiklikleri activity_logs tablosuna yazar (brief §7.8).
 * Oturum açmış kullanıcı yoksa (seeder, site ziyaretçisi) kayıt tutulmaz.
 */
trait LogsActivity
{
    /** Loglanmayacak alanlar */
    protected static array $activityIgnored = ['updated_at', 'created_at', 'password', 'remember_token', 'last_login_at', 'last_login_ip'];

    protected static function bootLogsActivity(): void
    {
        static::created(fn (Model $model) => static::recordActivity($model, 'created'));
        static::updated(fn (Model $model) => static::recordActivity($model, 'updated'));
        static::deleted(fn (Model $model) => static::recordActivity($model, 'deleted'));

        if (method_exists(static::class, 'restored')) {
            static::restored(fn (Model $model) => static::recordActivity($model, 'restored'));
        }
    }

    protected static function recordActivity(Model $model, string $event): void
    {
        if (! auth()->check()) {
            return;
        }

        $properties = null;

        if ($event === 'updated') {
            $changed = array_diff(array_keys($model->getChanges()), static::$activityIgnored);

            if ($changed === []) {
                return;
            }

            $properties = [
                'old' => array_intersect_key($model->getPrevious(), array_flip($changed)),
                'new' => array_intersect_key($model->getAttributes(), array_flip($changed)),
            ];

            // Uzun HTML gövdeleri logu şişirmesin
            array_walk_recursive($properties, function (&$value) {
                $value = is_string($value) ? Str::limit($value, 500) : $value;
            });
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'subject_type' => $model->getMorphClass(),
            'subject_id' => $model->getKey(),
            'description' => static::activityLabel($model),
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 490, ''),
            'created_at' => now(),
        ]);
    }

    protected static function activityLabel(Model $model): string
    {
        // Strict mode: tabloda olmayan alana erişim hata verir → yalnızca var olan alanlara bak
        $attributes = $model->getAttributes();
        $name = collect(['title', 'name', 'label', 'question', 'original_name'])
            ->map(fn (string $key) => $attributes[$key] ?? null)
            ->first(fn ($value) => filled($value)) ?? '#'.$model->getKey();

        return Str::limit(class_basename($model).': '.$name, 250, '');
    }
}
