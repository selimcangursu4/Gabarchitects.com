<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Sitede slug ile çalışan modeller (getRouteKeyName = 'slug') için panel adreslerini ID ile üretir.
 *
 * Filament, kaydı bulurken $recordRouteKeyName ('id') kullanır; ancak adres üretirken modeli
 * Laravel'e verir ve Laravel getRouteKey() (slug) koyar → /admin/properties/ornek-ilan-12/edit → 404.
 * Bu trait, adres üretiminde model yerine ID geçirerek ikisini tutarlı hale getirir (BUG-017).
 */
trait UsesIdRecordUrls
{
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (($parameters['record'] ?? null) instanceof Model) {
            $parameters['record'] = $parameters['record']->getKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }
}
