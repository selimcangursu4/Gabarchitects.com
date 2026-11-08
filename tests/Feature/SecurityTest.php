<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use App\Services\MediaService;
use App\Services\SettingService;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

/** PHASE 07 — brief §10 güvenlik maddeleri, hata sayfaları, KVKK çerez bildirimi, dil */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
    }

    private function contactPayload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Ayşe Yılmaz', 'email' => 'ayse@example.com', 'phone' => '0532 000 00 00',
            'subject' => 'Proje', 'message' => 'Arsamız için görüşmek istiyoruz, lütfen dönüş yapın.',
            'privacy_consent' => '1', 'source' => 'contact_page', 'website' => '',
            '_t' => encrypt(now()->subSeconds(10)->timestamp),
        ];
    }

    /** config dosyasını verilen ortam değişkenleriyle yeniden değerlendirir */
    private function evaluateConfig(string $file, array $env): array
    {
        $keys = array_keys($env);
        $backup = [];
        foreach ($keys as $key) {
            $backup[$key] = [$_SERVER[$key] ?? null, $_ENV[$key] ?? null];
        }

        try {
            foreach ($env as $key => $value) {
                unset($_SERVER[$key], $_ENV[$key]);
                if ($value !== null) {
                    $_SERVER[$key] = $_ENV[$key] = $value;
                }
            }

            return require config_path($file);
        } finally {
            foreach ($backup as $key => [$server, $envValue]) {
                unset($_SERVER[$key], $_ENV[$key]);
                if ($server !== null) {
                    $_SERVER[$key] = $server;
                }
                if ($envValue !== null) {
                    $_ENV[$key] = $envValue;
                }
            }
        }
    }

    // ------------------------------------------------------------------ Başlıklar

    public function test_public_pages_send_security_headers_and_nonce_based_csp(): void
    {
        foreach (['/', '/iletisim', '/olmayan-sayfa'] as $url) {
            $response = $this->get($url);

            $response->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
                ->assertHeaderMissing('Strict-Transport-Security');   // http → HSTS yok

            $csp = $response->headers->get('Content-Security-Policy');
            $this->assertNotNull($csp, "CSP yok: {$url}");
            $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([A-Za-z0-9+\/=]+)'/", $csp);
            $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
            $this->assertStringContainsString("object-src 'none'", $csp);
            $this->assertStringContainsString("frame-ancestors 'self'", $csp);

            // Sayfadaki her çalıştırılabilir <script> aynı nonce'u taşımalı (JSON-LD veri bloğu hariç)
            preg_match("/'nonce-([^']+)'/", $csp, $nonce);
            preg_match_all('/<script\b[^>]*>/', $response->getContent(), $scripts);
            $executable = array_filter($scripts[0], fn (string $tag) => ! str_contains($tag, 'application/ld+json'));
            $this->assertNotEmpty($executable, $url);
            foreach ($executable as $tag) {
                $this->assertStringContainsString('nonce="'.$nonce[1].'"', $tag, "Nonce'suz script: {$tag}");
            }
        }
    }

    public function test_panel_has_no_csp_but_is_hidden_from_search_engines(): void
    {
        $this->get('/admin/login')->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeaderMissing('Content-Security-Policy');   // Filament/Livewire satır içi script kullanır
    }

    public function test_hsts_only_on_https_in_production(): void
    {
        $this->app['env'] = 'production';

        try {
            $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
            $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    // ------------------------------------------------------------------ Oturum ve giriş

    public function test_session_cookie_is_http_only_and_same_site(): void
    {
        $cookie = collect($this->get('/')->headers->getCookies())->firstWhere(fn ($c) => $c->getName() === config('session.cookie'));

        $this->assertNotNull($cookie, 'Oturum çerezi yok');
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_session_cookie_is_secure_by_default_in_production(): void
    {
        $this->assertTrue($this->evaluateConfig('session.php', ['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => null])['secure']);
        $this->assertTrue($this->evaluateConfig('session.php', ['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => ''])['secure']);
        $this->assertFalse($this->evaluateConfig('session.php', ['APP_ENV' => 'local', 'SESSION_SECURE_COOKIE' => null])['secure']);
        $this->assertTrue($this->evaluateConfig('session.php', ['APP_ENV' => 'local', 'SESSION_SECURE_COOKIE' => 'true'])['secure']);
    }

    public function test_panel_login_is_rate_limited(): void
    {
        $user = User::factory()->create(['is_active' => true, 'password' => 'DogruSifre123!']);
        $user->assignRole(Role::Admin->value);

        foreach (range(1, 5) as $_) {
            Livewire::test(Login::class)
                ->fillForm(['email' => $user->email, 'password' => 'yanlis-sifre'])
                ->call('authenticate')
                ->assertHasFormErrors(['email']);
        }

        // 6. deneme doğru şifreyle bile reddedilir (dakikada 5 deneme)
        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'DogruSifre123!'])
            ->call('authenticate')
            ->assertNotified();

        $this->assertGuest();
    }

    public function test_passwords_are_hashed(): void
    {
        $user = User::factory()->create(['password' => 'GizliSifre123!']);
        $raw = DB::table('users')->where('id', $user->id)->value('password');

        $this->assertNotSame('GizliSifre123!', $raw);
        $this->assertTrue(Hash::check('GizliSifre123!', $raw));
        $this->assertMatchesRegularExpression('/^\$(2y|argon2id)\$/', $raw);
    }

    // ------------------------------------------------------------------ Form güvenliği

    public function test_forms_reject_requests_without_csrf_token(): void
    {
        // Laravel CSRF kontrolünü yalnızca "testing" ortamında atlar → bu test için devre dışı bırak
        $this->app['env'] = 'local';

        try {
            $this->post('/iletisim', $this->contactPayload())
                ->assertStatus(419)
                ->assertSee('Sayfanın süresi doldu.');
        } finally {
            $this->app['env'] = 'testing';
        }

        $this->assertSame(0, ContactMessage::count());
    }

    /** TRUSTED_PROXIES tanımsızken (Turhost) sahte X-Forwarded-For ile deneme sınırı atlatılamaz */
    public function test_rate_limit_cannot_be_bypassed_with_spoofed_forwarded_ip(): void
    {
        $this->assertEmpty(env('TRUSTED_PROXIES'), 'Test ortamında proxy güveni kapalı olmalı');

        foreach (range(1, 5) as $i) {
            $this->withHeader('X-Forwarded-For', "203.0.113.{$i}")->post('/iletisim', $this->contactPayload())->assertRedirect();
        }

        $this->withHeader('X-Forwarded-For', '198.51.100.77')->post('/iletisim', $this->contactPayload())->assertStatus(429);
        $this->assertSame(5, ContactMessage::count());
    }

    public function test_seed_if_empty_does_not_touch_existing_content(): void
    {
        $page = Page::where('key', 'home')->firstOrFail();
        $page->update(['title' => 'Panelden değiştirildi']);

        $this->artisan('gab:seed-if-empty')->expectsOutputToContain('seed atlandı')->assertSuccessful();

        $this->assertSame('Panelden değiştirildi', $page->fresh()->title);
    }

    public function test_contact_form_ignores_protected_fields(): void
    {
        $this->post('/iletisim', $this->contactPayload([
            'status' => 'replied', 'read_at' => now()->toDateTimeString(), 'internal_notes' => 'enjekte', 'handled_by' => 1,
        ]))->assertRedirect();

        $message = ContactMessage::sole();
        $this->assertSame('new', $message->status->value);
        $this->assertNull($message->read_at);
        $this->assertNull($message->internal_notes);
        $this->assertNull($message->handled_by);
    }

    public function test_user_input_is_escaped_in_panel_and_site(): void
    {
        $xss = '<script>alert("xss")</script>';
        $this->post('/iletisim', $this->contactPayload(['name' => 'Kötü '.$xss, 'message' => 'Mesaj içeriği '.$xss.' uzun metin']))->assertRedirect();

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(Role::SuperAdmin->value);
        $this->actingAs($admin);

        $html = Livewire::test(ViewContactMessage::class, ['record' => ContactMessage::sole()->getKey()])->html();
        $this->assertStringNotContainsString($xss, $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);

        $project = Project::create(['title' => 'Proje '.$xss, 'status' => 'published']);
        $this->get($project->url())->assertOk()->assertDontSee($xss, false)->assertSee('&lt;script&gt;', false);
    }

    public function test_filter_parameters_cannot_inject_sql(): void
    {
        $before = Property::count();

        foreach (["' OR '1'='1", "'; DROP TABLE properties; --", '1 UNION SELECT password FROM users'] as $payload) {
            $this->get('/portfoy?'.http_build_query(['il' => $payload, 'tip' => $payload, 'min_fiyat' => $payload, 'siralama' => $payload]))
                ->assertOk()
                ->assertDontSee('SQLSTATE', false);
        }

        $this->assertSame($before, Property::count());
    }

    // ------------------------------------------------------------------ Dosya yükleme

    public function test_upload_rejects_svg_and_decompression_bombs(): void
    {
        Storage::fake('media');
        Storage::fake('local');

        $svg = tempnam(sys_get_temp_dir(), 'gab');
        file_put_contents($svg, '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><rect width="10" height="10"/></svg>');
        try {
            app(MediaService::class)->importFile($svg, 'logo.svg');
            $this->fail('SVG kabul edilmemeli');
        } catch (InvalidArgumentException) {
            // beklenen
        }

        // Piksel sınırı: küçük dosya ama sınırın üstünde piksel → görsel açılmadan reddedilir
        config(['media.max_pixels' => 5_000]);
        $big = tempnam(sys_get_temp_dir(), 'gab').'.png';
        imagepng(imagecreatetruecolor(100, 100), $big);   // 10.000 piksel
        try {
            app(MediaService::class)->importFile($big, 'buyuk.png');
            $this->fail('Piksel sınırı aşan görsel kabul edilmemeli');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('piksel', $e->getMessage());
        }

        Storage::disk('media')->assertDirectoryEmpty('/');
        Storage::disk('local')->assertDirectoryEmpty('/');
    }

    public function test_image_with_embedded_php_is_reencoded_and_original_stays_private(): void
    {
        Storage::fake('media');
        Storage::fake('local');

        $path = tempnam(sys_get_temp_dir(), 'gab').'.jpg';
        imagejpeg(imagecreatetruecolor(600, 400), $path);
        file_put_contents($path, '<?php system($_GET["c"]); ?>', FILE_APPEND);   // poliglot dosya

        $media = app(MediaService::class)->importFile($path, '../../shell.php.jpg');

        // Dosya adı kullanıcıdan alınmaz; orijinal web'den erişilemeyen diskte
        $this->assertStringNotContainsString('shell', $media->original_path);
        $this->assertSame('local', $media->original_disk);
        $this->assertStringStartsWith(storage_path('app/private'), config('filesystems.disks.local.root'));
        $this->assertStringEndsWith('.jpg', $media->original_path);

        // Herkese açık türevler yeniden kodlanmıştır: gömülü kod taşımaz
        foreach (Storage::disk('media')->allFiles($media->uuid) as $file) {
            $this->assertStringNotContainsString('<?php', Storage::disk('media')->get($file));
            $this->assertMatchesRegularExpression('/\.(webp|avif)$/', $file);
        }
    }

    public function test_panel_upload_errors_are_shown_on_the_field_not_as_server_error(): void
    {
        Storage::fake('media');
        Storage::fake('local');
        config(['media.max_pixels' => 5_000]);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(Role::SuperAdmin->value);
        $this->actingAs($admin);

        Livewire::test(CreateService::class)
            ->fillForm(['title' => 'Yeni Hizmet', 'status' => 'draft', 'cover_media_id' => UploadedFile::fake()->image('dev.jpg', 200, 200)])
            ->call('create')
            ->assertHasFormErrors(['cover_media_id']);

        $this->assertDatabaseMissing('services', ['title' => 'Yeni Hizmet']);
    }

    public function test_sensitive_files_are_not_public(): void
    {
        $this->assertFileDoesNotExist(public_path('.env'));
        $this->assertFileExists(base_path('.env.example'));
        $this->get('/.env')->assertNotFound();
        $this->get('/storage/media/originals/x.jpg')->assertNotFound();
        $this->put('/storage/media/originals/x.jpg')->assertNotFound();   // Laravel'in local disk route'ları kapalı
        $this->assertFalse(Route::has('storage.local'));

        $htaccess = file_get_contents(public_path('.htaccess'));
        $this->assertStringContainsString('Require all denied', $htaccess);
        $this->assertStringContainsString('unset X-Powered-By', $htaccess);
    }

    // ------------------------------------------------------------------ Hata sayfaları

    public function test_production_errors_hide_system_details_and_do_not_need_database(): void
    {
        config(['app.debug' => false]);
        Route::get('/_test/hata', fn () => throw new RuntimeException('GIZLI_DETAY '.base_path('app/Secret.php')));

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $response = $this->get('/_test/hata');

        $response->assertStatus(500)
            ->assertSee('Beklenmeyen bir sorun oluştu.')
            ->assertDontSee('GIZLI_DETAY')
            ->assertDontSee('RuntimeException')
            ->assertDontSee(base_path(), false)
            ->assertDontSee('Stack trace');

        // Veritabanı çökmüşken de açılabilmeli: hata sayfası sorgu yapmaz
        $this->assertSame([], $queries, 'Hata sayfası veritabanına erişti: '.implode(' | ', $queries));
    }

    public function test_maintenance_page(): void
    {
        $this->artisan('down')->assertSuccessful();

        try {
            $response = $this->get('/')->assertStatus(503)->assertSee('Kısa bir bakım çalışması yapıyoruz.')
                ->assertDontSee('/iletisim', false)   // bakımda bağlantı yok
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN');

            // Bakım yanıtı da CSP + dolu nonce almalı (middleware en dışta)
            preg_match("/'nonce-([^']+)'/", (string) $response->headers->get('Content-Security-Policy'), $nonce);
            $this->assertNotEmpty($nonce);
            $response->assertSee('nonce="'.$nonce[1].'"', false);
        } finally {
            $this->artisan('up');
        }

        $this->get('/')->assertOk();
    }

    public function test_rate_limited_and_forbidden_pages_are_branded(): void
    {
        foreach (range(1, 5) as $_) {
            $this->post('/iletisim', $this->contactPayload());
        }
        $this->post('/iletisim', $this->contactPayload())->assertStatus(429)->assertSee('Çok fazla istek gönderildi.');

        $noRole = User::factory()->create(['is_active' => true]);
        $this->actingAs($noRole)->get('/admin')->assertForbidden()->assertSee('Bu sayfaya erişim yetkiniz yok.');
    }

    // ------------------------------------------------------------------ KVKK çerez bildirimi

    public function test_cookie_notice_links_policy_and_uses_panel_text(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('data-cookie-notice', $html);
        $this->assertStringContainsString('yalnızca sitenin çalışması için zorunlu çerezler', $html);
        $this->assertStringContainsString('href="'.route('legal.cookies').'"', $html);
        preg_match("/cookieNotice\('([a-f0-9]{8})'\)/", $html, $defaultVersion);

        app(SettingService::class)->set('legal.cookie_banner_text', 'Onaylı çerez metni.', 'legal');
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('Onaylı çerez metni.', $html);
        preg_match("/cookieNotice\('([a-f0-9]{8})'\)/", $html, $newVersion);
        $this->assertNotSame($defaultVersion[1], $newVersion[1], 'Metin değişince bant yeniden gösterilmeli');

        $this->get('/admin/login')->assertDontSee('data-cookie-notice', false);
    }

    /** Bildirimdeki "üçüncü taraf çerezi yok" ifadesi doğru kalmalı: sayfalar dış kaynak yüklemez */
    public function test_pages_load_no_third_party_resources(): void
    {
        $project = Project::published()->firstOrFail();

        foreach (['/', '/hakkimizda', '/iletisim', '/projeler', $project->url(), '/portfoy', '/blog'] as $url) {
            preg_match_all('/<(script|link|img|iframe|source)\b[^>]*\s(?:src|href|srcset)="([^"]+)"/i', $this->get($url)->getContent(), $m, PREG_SET_ORDER);

            foreach ($m as [, $tag, $src]) {
                if ($tag === 'link' && preg_match('/rel="(canonical|alternate)"/', $m[0][0] ?? '')) {
                    continue;
                }
                $host = parse_url(explode(' ', trim($src))[0], PHP_URL_HOST);
                $this->assertTrue($host === null || $host === parse_url(config('app.url'), PHP_URL_HOST) || $host === 'localhost',
                    "Dış kaynak bulundu ({$url}): <{$tag}> {$src} — çerez bildirimi ve CSP güncellenmeli");
            }
        }
    }

    // ------------------------------------------------------------------ Dil (yalnızca Türkçe, TD-003)

    public function test_site_and_panel_are_turkish_only(): void
    {
        $this->get('/')->assertSee('<html lang="tr"', false);
        $this->get('/admin/login')->assertSee('lang="tr"', false);
        $this->get('/en')->assertNotFound();
        $this->get('/en/projects')->assertNotFound();

        $post = BlogPost::create(['title' => 'Tarih Testi', 'status' => 'published', 'published_at' => '2026-11-01 10:00:00']);
        $this->get(route('blog.show', $post))->assertSee('01 Kasım 2026');
    }
}
