<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Service;
use App\Services\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class);
    }

    public function boot(): void
    {
        // Tek adres (TD-062): bağlantılar, kanonik, sitemap ve OG adresleri istek host'undan değil APP_URL'den
        // üretilir (Host başlığı taklidiyle başka alan adı yazdırılamaz). Yalnızca canonical_redirect açıkken.
        if (config('app.canonical_redirect') && filled(config('app.url'))) {
            URL::forceRootUrl(rtrim((string) config('app.url'), '/'));
            if (str_starts_with((string) config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }
        }

        // Geliştirmede: lazy loading, tanımsız attribute ve fillable dışı atamalar hata versin.
        Model::shouldBeStrict(! $this->app->isProduction());

        Paginator::defaultView('vendor.pagination.gab');

        // İletişim formu: IP başına 10 dakikada 5 gönderim.
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinutes(10, 5)->by($request->ip()));

        // Super Admin her yetkiye sahiptir.
        // Yalnızca izin adları ("projects.manage") için geçerli; policy'lerdeki sabit yasaklar
        // (ör. web formu oluşturma, sayfa silme, kendini silme) Super Admin için de geçerli kalır.
        Gate::before(fn ($user, string $ability) => str_contains($ability, '.') && $user->hasRole(Role::SuperAdmin->value) ? true : null);

        // Kendi görseli olmayan sayfaların paylaşım görseli: ana sayfa kapağı (Page kaydedilince temizlenir)
        View::composer('components.layouts.site', fn ($view) => $view->with(
            'defaultShareImage',
            Cache::rememberForever('seo.share_image', fn () => Page::with('cover')->where('key', 'home')->first()?->cover?->shareImage() ?? []) ?: null,
        ));

        View::composer(['components.site.header', 'components.site.footer'], function ($view) {
            // Not: cache'e yalnızca düz diziler konur (Laravel 13 cache nesne geri yüklemez).
            $menus = Cache::rememberForever('menus.tree', fn () => Menu::query()
                ->with('tree.children')
                ->get()
                ->mapWithKeys(fn (Menu $menu) => [$menu->key => $menu->tree->map->toNavArray()->values()->all()])
                ->all());

            $view->with([
                'navigation' => $menus['header'] ?? [],
                'legalNavigation' => $menus['legal'] ?? [],
                // Zamanlanmış hizmetler yayına girince görünsün diye kısa süreli cache
                'footerServices' => Cache::remember('footer.services', now()->addMinutes(10), fn () => Service::published()
                    ->orderBy('sort_order')->limit(6)->get(['title', 'slug'])->toArray()),
            ]);
        });
    }
}
