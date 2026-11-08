<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * İlk Super Admin hesabı. Bilgiler config/gab.php üzerinden .env'den okunur (ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD).
 * Şifre verilmezse rastgele güçlü bir şifre üretilip yalnızca konsola bir kez yazılır.
 * Hesap zaten varsa dokunulmaz (şifre ezilmez).
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('gab.admin.email');
        $configuredPassword = config('gab.admin.password');

        if (! $email) {
            $this->command?->warn('ADMIN_EMAIL tanımlı değil; yönetici hesabı oluşturulmadı.');

            return;
        }

        if (User::withTrashed()->where('email', $email)->exists()) {
            $this->command?->info("Yönetici hesabı zaten var: {$email}");

            return;
        }

        $password = $configuredPassword ?: Str::password(20);

        $user = User::create([
            'name' => config('gab.admin.name'),
            'email' => $email,
            'password' => $password,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole(Role::SuperAdmin->value);

        $this->command?->info("Super Admin oluşturuldu: {$email}");

        if (! $configuredPassword) {
            $this->command?->warn("Üretilen şifre (bir kez gösterilir): {$password}");
        }
    }
}
