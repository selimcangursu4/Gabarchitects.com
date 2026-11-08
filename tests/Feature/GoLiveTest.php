<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Canlıya geçiş: test içeriğinin silinmesi, yönetici hesabı, tek alan adı (TD-062) */
class GoLiveTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = 'migrations/2026_11_09_020000_prune_test_projects_and_properties.php';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        Storage::fake('local');
        $this->seed();
    }

    private function image(): Media
    {
        $path = tempnam(sys_get_temp_dir(), 'gab').'.jpg';
        $img = imagecreatetruecolor(500, 400);
        imagejpeg($img, $path, 80);
        imagedestroy($img);

        return app(MediaService::class)->importFile($path);
    }

    private function property(string $slug, array $attributes = []): Property
    {
        return Property::create(array_merge([
            'title' => $slug, 'slug' => $slug, 'listing_type' => 'sale', 'property_type' => 'land',
            'city' => 'Konya', 'currency' => 'TRY', 'status' => 'published', 'published_at' => now(),
        ], $attributes));
    }

    private function runPrune(): void
    {
        (require database_path(self::MIGRATION))->up();
    }

    public function test_prune_keeps_only_the_railport_project_and_konya_listing(): void
    {
        $shared = $this->image();        // silinen bir projede ve kalan ilanda kullanılıyor → kalmalı
        $orphan = $this->image();        // yalnızca silinen projede → dosyalarıyla silinmeli
        $galleryOnly = $this->image();   // yalnızca silinen ilanın galerisinde → silinmeli

        $keepProject = Project::create(['title' => 'Arkas Railport Uygulama Projemiz', 'slug' => 'arkas-railport-uygulama-projemiz', 'status' => 'published', 'published_at' => now()]);
        $keepProperty = $this->property('konyada-firsat-tarla', ['cover_media_id' => $shared->id]);

        $testProject = Project::create(['title' => 'Selimcan Test Proje', 'slug' => 'selimcan-test-proje', 'status' => 'published', 'cover_media_id' => $orphan->id]);
        $testProject->syncGallery([$shared->id]);
        $testProperty = $this->property('test-ilani');
        $testProperty->syncGallery([$galleryOnly->id]);
        $trashed = Project::create(['title' => 'Çöpteki', 'slug' => 'copteki', 'status' => 'draft']);
        $trashed->delete();

        $home = Page::where('key', 'home')->firstOrFail();
        $home->update(['content' => array_replace_recursive($home->content, ['approach' => ['project_id' => $testProject->id]])]);

        $this->assertGreaterThan(3, Project::withTrashed()->count());   // seed'deki projeler de var

        $this->runPrune();

        $this->assertSame([$keepProject->id], Project::withTrashed()->pluck('id')->all());
        $this->assertSame([$keepProperty->id], Property::withTrashed()->pluck('id')->all());

        $this->assertNotNull(Media::find($shared->id));
        Storage::disk('media')->assertExists("{$shared->uuid}/480.webp");
        $this->assertNull(Media::withTrashed()->find($orphan->id));
        Storage::disk('media')->assertMissing("{$orphan->uuid}/480.webp");
        Storage::disk('local')->assertMissing($orphan->original_path);
        $this->assertNull(Media::withTrashed()->find($galleryOnly->id));
        $this->assertSame(0, \DB::table('mediables')->whereIn('mediable_id', [$testProject->id, $testProperty->id])->count());

        $this->assertSame($keepProject->id, (int) $home->fresh()->content['approach']['project_id']);

        // Site çalışmaya devam eder; silinen adresler 404
        $this->get('/')->assertOk()->assertSee('data-stat="projects">1 proje<', false);
        $this->get($keepProject->url())->assertOk();
        $this->get('/portfoy/konyada-firsat-tarla')->assertOk();
        $this->get('/projeler/selimcan-test-proje')->assertNotFound();
        $this->get('/portfoy')->assertOk()->assertDontSee('test-ilani');
    }

    public function test_prune_does_nothing_on_a_fresh_install(): void
    {
        $projects = Project::withTrashed()->count();
        $this->property('konyada-firsat-tarla');   // proje yok → çalışmaz
        $this->property('baska-ilan');

        $this->runPrune();

        $this->assertSame($projects, Project::withTrashed()->count());
        $this->assertSame(2, Property::withTrashed()->count());
    }

    public function test_sync_admin_moves_super_admin_to_env_credentials_once(): void
    {
        $admin = User::where('email', 'admin@test.local')->firstOrFail();
        config(['gab.admin.email' => 'info@gabarchitects.com', 'gab.admin.password' => 'yeni-sifre-123']);

        $this->artisan('gab:sync-admin')->assertSuccessful();

        $admin->refresh();
        $this->assertSame('info@gabarchitects.com', $admin->email);
        $this->assertTrue(Hash::check('yeni-sifre-123', $admin->password));
        $this->assertTrue($admin->hasRole(Role::SuperAdmin->value));
        $this->assertSame(1, User::count());

        // Sonraki çalıştırmalar panelden değiştirilen şifreyi ezmez
        $admin->update(['password' => 'panelden-degisti']);
        $this->artisan('gab:sync-admin')->assertSuccessful();
        $this->assertTrue(Hash::check('panelden-degisti', $admin->fresh()->password));
    }

    public function test_sync_admin_without_env_values_changes_nothing(): void
    {
        config(['gab.admin.email' => 'info@gabarchitects.com', 'gab.admin.password' => null]);

        $this->artisan('gab:sync-admin')->assertSuccessful();

        $this->assertTrue(User::where('email', 'admin@test.local')->exists());
        $this->assertFalse(User::where('email', 'info@gabarchitects.com')->exists());
    }

    public function test_new_admin_credentials_can_log_in(): void
    {
        config(['gab.admin.email' => 'info@gabarchitects.com', 'gab.admin.password' => 'yeni-sifre-123']);
        $this->artisan('gab:sync-admin');

        $this->assertTrue(auth()->attempt(['email' => 'info@gabarchitects.com', 'password' => 'yeni-sifre-123']));
        $this->assertTrue(auth()->user()->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_www_and_http_redirect_to_the_canonical_domain(): void
    {
        config(['app.url' => 'https://gabarchitects.com', 'app.canonical_redirect' => true]);

        $this->get('https://www.gabarchitects.com/projeler?sayfa=2')
            ->assertStatus(301)->assertRedirect('https://gabarchitects.com/projeler?sayfa=2');
        $this->get('http://www.gabarchitects.com/')
            ->assertStatus(301)->assertRedirect('https://gabarchitects.com/');
        $this->get('http://gabarchitects.com/iletisim')
            ->assertStatus(301)->assertRedirect('https://gabarchitects.com/iletisim');

        $this->get('https://gabarchitects.com/')->assertOk();
        // Başka host'lar (sağlık kontrolü, geçici adres) yönlendirilmez → döngü yok
        $this->get('http://localhost/up')->assertOk();
        // Form gönderimleri yönlendirilmez
        $this->post('http://gabarchitects.com/iletisim', [])->assertStatus(302)->assertRedirect();
    }

    public function test_canonical_redirect_is_off_outside_production_by_default(): void
    {
        config(['app.url' => 'https://gabarchitects.com']);

        $this->assertFalse(config('app.canonical_redirect'));
        $this->get('https://www.gabarchitects.com/')->assertOk();
    }

    public function test_sitemap_and_canonical_use_the_real_domain(): void
    {
        config(['app.url' => 'https://gabarchitects.com', 'app.canonical_redirect' => true]);
        \URL::forceRootUrl('https://gabarchitects.com');

        $this->get('https://gabarchitects.com/')
            ->assertSee('<link rel="canonical" href="https://gabarchitects.com"', false);
        $this->get('https://gabarchitects.com/sitemap.xml')
            ->assertSee('<loc>https://gabarchitects.com/projeler</loc>', false);
    }
}
