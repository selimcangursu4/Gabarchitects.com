<?php

namespace Tests\Feature;

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

    public function test_unknown_page_returns_branded_404(): void
    {
        $this->get('/olmayan-sayfa')
            ->assertNotFound()
            ->assertSee('Aradığınız sayfa bulunamadı.');
    }
}
