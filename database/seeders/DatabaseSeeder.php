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
        if (app()->environment('local')) {
            $this->call(SamplePropertySeeder::class);
        }
    }
}
