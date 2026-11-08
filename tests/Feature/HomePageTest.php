<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Reference;
use App\Models\Service;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    /*
     * Not: $seed = true kullanılmıyor. O yöntem süreç başına bir kez, transaction dışında seed'ler
     * ve veri sonraki test sınıflarına sızar. setUp'ta seed → her test kendi transaction'ında.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_page_renders_with_brand_and_turkish_locale(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="tr" data-theme="dark">', false)
            ->assertSee('<title>GAB Gayrimenkul Proje Geliştirme</title>', false)
            ->assertSee('Arsadan anahtar teslime.');
    }

    public function test_home_page_contains_all_brief_sections(): void
    {
        $response = $this->get('/');

        foreach ([
            'id="hero-title"', 'aria-label="Yaklaşımımız"', 'id="intro-title"', 'id="projects-title"',
            'id="services-title"', 'id="approach-title"', 'id="references-title"', 'id="process-title"',
            'id="cta-title"', '<footer',
        ] as $marker) {
            $response->assertSee($marker, false);
        }
    }

    public function test_home_page_reads_content_from_database(): void
    {
        $response = $this->get('/');

        foreach (Reference::active()->where('is_featured', true)->pluck('name') as $name) {
            $response->assertSee($name);
        }

        foreach (Project::published()->featured()->pluck('slug') as $slug) {
            $response->assertSee('/projeler/'.$slug, false);
        }

        $response->assertSee('Mehmet Bıyıklı Sokak');
    }

    public function test_draft_projects_are_not_shown_on_home_page(): void
    {
        $project = Project::published()->featured()->firstOrFail();
        $project->update(['status' => 'draft']);

        $this->get('/')->assertDontSee('/projeler/'.$project->slug, false);
    }

    public function test_menu_changes_are_reflected_after_cache_flush(): void
    {
        $this->get('/')->assertSee('Hakkımızda');

        MenuItem::where('url', '/hakkimizda')->firstOrFail()->update(['label' => 'Biz Kimiz']);

        $this->get('/')->assertSee('Biz Kimiz');
    }

    public function test_missing_contact_fields_are_not_rendered_as_empty_links(): void
    {
        $this->get('/')
            ->assertDontSee('href="tel:"', false)
            ->assertDontSee('href="mailto:"', false);
    }

    public function test_theme_is_applied_before_render_to_avoid_flash(): void
    {
        $html = $this->get('/')->getContent();

        $themeScript = strpos($html, "localStorage.getItem('gab-theme')");
        $stylesheet = strpos($html, 'rel="stylesheet"');

        $this->assertNotFalse($themeScript);
        $this->assertTrue($stylesheet === false || $themeScript < $stylesheet, 'Tema script\'i CSS\'ten önce gelmeli.');
    }

    public function test_every_image_has_alt_text_or_is_marked_decorative(): void
    {
        // Testlerde görsel seed'lenmez; kapak ve hizmet görselleri için sahte medya üret.
        Storage::fake('media');
        Storage::fake('local');
        $path = tempnam(sys_get_temp_dir(), 'gab').'.jpg';
        imagejpeg(imagecreatetruecolor(800, 600), $path);
        $media = app(MediaService::class)->importFile($path);

        Project::query()->update(['cover_media_id' => $media->id]);
        Service::query()->update(['cover_media_id' => $media->id]);
        Page::where('key', 'home')->update(['cover_media_id' => $media->id]);

        $html = $this->get('/')->getContent();

        preg_match_all('/<img\b[^>]*>/', $html, $images);

        $this->assertGreaterThan(5, count($images[0]), 'Sayfada kontrol edilecek görsel bulunamadı.');
        $this->assertStringContainsString('fetchpriority="high"', $html, 'Hero görseli öncelikli yüklenmeli.');

        foreach ($images[0] as $img) {
            $hasAlt = preg_match('/\salt="[^"]+"/', $img) === 1;
            $isDecorative = str_contains($img, 'alt=""') && str_contains($img, 'role="presentation"');

            $this->assertTrue($hasAlt || $isDecorative, "Alt metni eksik: {$img}");
        }
    }

    /** Kullanıcı talebi: Hakkımızda bölümündeki proje / hizmet sayıları panelden eklenen-silinen kayıtlarla değişir */
    public function test_about_section_counts_follow_published_projects_and_services(): void
    {
        $stat = function (string $key): int {
            preg_match('/data-stat="'.$key.'">(\d+)/', $this->get('/')->getContent(), $m);

            return (int) ($m[1] ?? -1);
        };

        $projects = Project::published()->count();
        $services = Service::published()->count();
        $this->assertSame($projects, $stat('projects'));
        $this->assertSame($services, $stat('services'));

        Project::create(['title' => 'Yeni Yayındaki Proje', 'status' => 'published']);
        Project::create(['title' => 'Taslak Proje', 'status' => 'draft']);
        Project::create(['title' => 'Zamanlanmış Proje', 'status' => 'published', 'published_at' => now()->addWeek()]);
        $this->assertSame($projects + 1, $stat('projects'), 'Yalnızca yayındaki proje sayılmalı');

        Service::published()->firstOrFail()->delete();
        Service::create(['title' => 'Taslak Hizmet', 'status' => 'draft']);
        $this->assertSame($services - 1, $stat('services'), 'Silinen hizmet düşmeli, taslak sayılmamalı');
    }

    /**
     * Kullanıcı talebi: hero görseli Boğaziçi sahili (5120×3413) ve "çözünürlüğü asla düşürme":
     * en büyük türev orijinal genişlikte, sayfa srcset'i tam çözünürlüğü sunar. Migration bir kez içe aktarır.
     */
    public function test_hero_bosphorus_shore_image_keeps_full_resolution(): void
    {
        Storage::fake('media');
        Storage::fake('local');
        config(['media.avif' => false]);

        $migration = require database_path('migrations/2026_11_09_000000_set_home_hero_bosphorus_shore_image.php');
        $migration->up();
        $migration->up();   // tekrar çalışınca kopya oluşturmamalı

        $this->assertSame(1, Media::where('original_name', 'bogazici-sahil.jpg')->count());

        $home = Page::where('key', 'home')->firstOrFail();
        $cover = $home->cover;
        $this->assertSame('bogazici-sahil.jpg', $cover->original_name);
        $this->assertSame([5120, 3413], [$cover->width, $cover->height]);
        $this->assertSame(5120, max($cover->widths('webp')), 'Tam çözünürlüklü türev üretilmeli');
        Storage::disk('media')->assertExists("{$cover->uuid}/5120.webp");
        [$w, $h] = getimagesizefromstring(Storage::disk('media')->get("{$cover->uuid}/5120.webp"));
        $this->assertSame([5120, 3413], [$w, $h]);
        $this->assertNotEmpty($cover->alt);
        $this->assertSame('Boğaziçi', $home->field('hero.image_caption'));

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString("/{$cover->uuid}/5120.webp 5120w", $html);   // srcset tam çözünürlük
        $this->assertStringContainsString('Görsel: Boğaziçi', $html);
        $this->assertStringContainsString('bg-[#111111]/30', $html);   // hafif karartma
    }

    /** Kullanıcı talepleri: hero etiketi yok, "Entegre" yok, "Portföylerimiz" (menü + bölüm); eski veritabanı migration'la güncellenir */
    public function test_hero_texts_and_portfolio_label(): void
    {
        // Eski içerikli veritabanını taklit et
        $home = Page::where('key', 'home')->firstOrFail();
        $content = $home->content;
        $content['hero']['eyebrow'] = 'Mimarlık · Proje Geliştirme · İnşaat';
        $content['hero']['text'] = 'Entegre gayrimenkul proje geliştirme ve inşaat çözümleri.';
        $home->update(['content' => $content]);
        MenuItem::where('label', 'Portföylerimiz')->update(['label' => 'Portföyümüz']);

        $migration = require database_path('migrations/2026_11_08_235000_update_home_hero_texts_and_portfolio_label.php');
        $migration->up();

        $html = $this->get('/')->getContent();
        $this->assertStringNotContainsString('Mimarlık · Proje Geliştirme · İnşaat', $html);
        $this->assertStringNotContainsString('Entegre', $html);
        $this->assertStringContainsString('Gayrimenkul proje geliştirme ve inşaat çözümleri.', $html);
        $this->assertStringNotContainsString('Portföyümüz', $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'Portföylerimiz'));   // menü + ana sayfa bölümü
        $this->assertMatchesRegularExpression('/<h1 id="hero-title" class="max-w-\[12ch\] text-display">/', $html);   // etiket yokken üst boşluk yok

        $this->get('/portfoy')->assertSee('Portföylerimiz')->assertDontSee('Portföyümüz');
    }

    /** Kullanıcı talebi: hero alt metni "Mimarlık | Gayrimenkul Proje Geliştirme | İnşaat" (yeni kurulum + var olan veritabanı) */
    public function test_hero_subtitle(): void
    {
        $this->get('/')->assertSee('Mimarlık | Gayrimenkul Proje Geliştirme | İnşaat');

        // Eski metinli veritabanı migration ile güncellenir
        $home = Page::where('key', 'home')->firstOrFail();
        $content = $home->content;
        $content['hero']['text'] = 'Gayrimenkul proje geliştirme ve inşaat çözümleri.';
        $home->update(['content' => $content]);

        (require database_path('migrations/2026_11_09_010000_update_home_hero_subtitle.php'))->up();
        $this->get('/')->assertSee('Mimarlık | Gayrimenkul Proje Geliştirme | İnşaat')
            ->assertDontSee('Gayrimenkul proje geliştirme ve inşaat çözümleri.');

        // Panelden özel metin girilmişse migration dokunmaz
        $content['hero']['text'] = 'Panelden özel metin';
        $home->update(['content' => $content]);
        (require database_path('migrations/2026_11_09_010000_update_home_hero_subtitle.php'))->up();
        $this->assertSame('Panelden özel metin', $home->fresh()->field('hero.text'));
    }

    public function test_unknown_page_returns_branded_404(): void
    {
        $this->get('/olmayan-sayfa')
            ->assertNotFound()
            ->assertSee('Aradığınız sayfa bulunamadı.');
    }
}
