<?php

use App\Enums\Role;
use App\Models\Media;
use App\Models\Page;
use App\Models\Property;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/*
| Lokal tasarım için üretilen örnek ilanları ve görsellerini kalıcı olarak siler.
| Gerçek ilanlara (is_sample=false) dokunmaz.
*/
Artisan::command('gab:purge-samples', function (MediaService $media) {
    $properties = Property::withTrashed()->where('is_sample', true)->get();

    foreach ($properties as $property) {
        $cover = $property->cover_media_id ? Media::find($property->cover_media_id) : null;
        $property->forceDelete();

        if ($cover && $cover->usageCount() === 0) {
            $media->purge($cover);
        }
    }

    $this->info($properties->count().' örnek ilan silindi.');
})->purpose('Örnek (is_sample) ilanları ve görsellerini siler');

/*
| İlk kurulum: veritabanı boşsa (sayfa kaydı yoksa) seed çalıştırır; doluysa hiçbir şeye dokunmaz.
| Konteyner her başladığında güvenle çağrılabilir (docker/entrypoint.sh). Panelden girilen içerik ezilmez.
*/
Artisan::command('gab:seed-if-empty', function () {
    if (Page::query()->exists()) {
        $this->info('Veritabanı dolu; seed atlandı.');

        return;
    }

    $this->info('Boş veritabanı: ilk içerik yükleniyor…');
    $this->call('db:seed', ['--force' => true]);
})->purpose('Veritabanı boşsa ilk içeriği yükler (idempotent)');

/*
| Test/önizleme sunucusu (GAB_SEED_SAMPLES=true): ÖRNEK ilan hiç yoksa yükler; varsa dokunmaz.
| Kapalıyken hiçbir şey yapmaz. Silmek için: gab:purge-samples (silindikten sonra bayrak kapatılmalı).
*/
Artisan::command('gab:seed-samples-if-missing', function () {
    if (! config('gab.seed_samples')) {
        $this->info('GAB_SEED_SAMPLES kapalı; örnek ilan yüklenmedi.');

        return;
    }

    if (Property::withTrashed()->where('is_sample', true)->exists()) {
        $this->info('Örnek ilanlar zaten var.');

        return;
    }

    $this->call('db:seed', ['--class' => 'SamplePropertySeeder', '--force' => true]);
})->purpose('Test sunucusunda ÖRNEK ilan yoksa yükler (idempotent)');

/*
| Yönetici hesabını .env'deki ADMIN_EMAIL / ADMIN_PASSWORD'e taşır (şifre kodda tutulmaz).
| - Bu e-postayla bir hesap zaten varsa hiçbir şey yapmaz (panelden değiştirilen şifre ezilmez).
| - Yoksa ilk Super Admin hesabının e-postası ve şifresi bu değerlerle güncellenir.
| - Hiç Super Admin yoksa AdminUserSeeder çalışır.
| Taşınan veritabanında (Railway → Turhost) ve her konteyner açılışında güvenle çağrılabilir.
*/
Artisan::command('gab:sync-admin', function () {
    $email = config('gab.admin.email');
    $password = config('gab.admin.password');

    if (! $email || ! $password) {
        $this->info('ADMIN_EMAIL / ADMIN_PASSWORD tanımlı değil; yönetici hesabına dokunulmadı.');

        return;
    }

    if (User::withTrashed()->where('email', $email)->exists()) {
        $this->info("Yönetici hesabı zaten güncel: {$email}");

        return;
    }

    $admin = User::role(Role::SuperAdmin->value)->orderBy('id')->first();

    if (! $admin) {
        $this->call('db:seed', ['--class' => 'AdminUserSeeder', '--force' => true]);

        return;
    }

    $admin->forceFill([
        'email' => $email,
        'password' => $password,
        'email_verified_at' => now(),
    ])->save();

    // Eski oturumlar ve "beni hatırla" belirteçleri geçersiz olsun
    $admin->setRememberToken(Str::random(60));
    $admin->save();
    if (Schema::hasTable('sessions')) {
        DB::table('sessions')->where('user_id', $admin->id)->delete();
    }

    $this->info("Yönetici hesabı güncellendi: {$email}");
})->purpose('Yönetici e-posta/şifresini .env değerlerine taşır (idempotent)');
