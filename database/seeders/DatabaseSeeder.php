<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            SiteContentSeeder::class,
        ]);

        // Örnek ilanlar yalnızca lokal tasarım içindir (gerçek ilan yok). Silmek: php artisan gab:purge-samples
        // Test/önizleme sunucusunda GAB_SEED_SAMPLES=true ile açılabilir (TD-060)
        if (app()->environment('local') || config('gab.seed_samples')) {
            $this->call(SamplePropertySeeder::class);
        }
    }
}
