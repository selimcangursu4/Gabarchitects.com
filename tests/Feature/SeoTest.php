<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\Property;
use App\Services\MediaService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** PHASE 06: meta, Open Graph, kanonik adres, robots, sitemap, schema.org */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function fakeImage(int $width = 2000, int $height = 1000): Media
    {
        Storage::fake('media');
        Storage::fake('local');
        config(['media.avif' => false]);

        $path = tempnam(sys_get_temp_dir(), 'gab').'.jpg';
        imagejpeg(imagecreatetruecolor($width, $height), $path);

        return app(MediaService::class)->importFile($path);
    }

    /** @return array<int, array> sayfadaki tüm JSON-LD blokları */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.+?)</script>#s', $html, $m);

        return array_map(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $m[1]);
    }

    private function meta(string $html, string $attr, string $name): ?string
    {
        return preg_match('#<meta '.$attr.'="'.preg_quote($name, '#').'" content="([^"]*)"#', $html, $m)
            ? html_entity_decode($m[1])
            : null;
    }

    private function graph(array $graphs, string $type): ?array
    {
        return collect($graphs)->firstWhere('@type', $type);
    }

    public function test_every_public_page_has_title_description_canonical_and_open_graph(): void
    {
        $project = Project::published()->firstOrFail();

        foreach (['/', '/hakkimizda', '/referanslar', '/hizmetler', '/projeler', $project->url(), '/blog', '/iletisim', '/portfoy', '/satilik'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('#<title>[^<]+GAB Architects</title>|<title>GAB Architects</title>#u', $html, $url);
            $this->assertNotEmpty($this->meta($html, 'name', 'description'), "meta description yok: {$url}");
            $this->assertStringContainsString('<link rel="canonical" href="'.url($url === '/' ? '' : $url).'">', $html, $url);
            $this->assertStringContainsString('hreflang="tr"', $html, $url);
            $this->assertSame('tr_TR', $this->meta($html, 'property', 'og:locale'), $url);
            $this->assertNotEmpty($this->meta($html, 'property', 'og:title'), $url);
            $this->assertNotEmpty($this->meta($html, 'name', 'twitter:card'), $url);
        }
    }

    public function test_canonical_drops_filters_but_keeps_pagination(): void
    {
        $this->get('/satilik?tip=daire&siralama=fiyat-artan')
            ->assertSee('<link rel="canonical" href="'.url('/satilik').'">', false);

        $this->get('/projeler?page=2&utm_source=x')
            ->assertSee('<link rel="canonical" href="'.url('/projeler').'?page=2">', false);
    }

    public function test_site_is_closed_to_search_engines_outside_production(): void
    {
        config(['seo.indexable' => false]);

        $this->assertSame('noindex, follow', $this->meta($this->get('/')->getContent(), 'name', 'robots'));

        $this->get('/robots.txt')->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSeeText("User-agent: *\nDisallow: /\n", false);
    }

    /** Boş "SEO_INDEXABLE=" production'da siteyi yanlışlıkla Google'a kapatmamalı */
    public function test_indexable_setting_defaults_by_environment(): void
    {
        $cases = [
            // [APP_ENV, SEO_INDEXABLE (null = tanımsız), beklenen]
            ['production', null, true],
            ['production', '', true],
            ['production', 'false', false],   // staging
            ['local', null, false],
            ['local', 'true', true],
        ];

        $backup = [$_SERVER['APP_ENV'] ?? null, $_SERVER['SEO_INDEXABLE'] ?? null, $_ENV['APP_ENV'] ?? null, $_ENV['SEO_INDEXABLE'] ?? null];

        try {
            foreach ($cases as [$env, $value, $expected]) {
                $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env;
                unset($_SERVER['SEO_INDEXABLE'], $_ENV['SEO_INDEXABLE']);
                if ($value !== null) {
                    $_SERVER['SEO_INDEXABLE'] = $_ENV['SEO_INDEXABLE'] = $value;
                }

                $config = require config_path('seo.php');
                $this->assertSame($expected, $config['indexable'], "APP_ENV={$env}, SEO_INDEXABLE=".var_export($value, true));
            }
        } finally {
            [$_SERVER['APP_ENV'], $seoServer, $_ENV['APP_ENV'], $seoEnv] = $backup;
            unset($_SERVER['SEO_INDEXABLE'], $_ENV['SEO_INDEXABLE']);
        }
    }

    public function test_production_robots_blocks_admin_and_points_to_sitemap(): void
    {
        config(['seo.indexable' => true]);

        $robots = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringNotContainsString("Disallow: /\n", $robots);
        $this->assertStringContainsString('Sitemap: '.route('sitemap'), $robots);

        $this->assertStringStartsWith('index, follow', $this->meta($this->get('/')->getContent(), 'name', 'robots'));
    }

    public function test_filtered_listings_sample_properties_and_empty_legal_pages_are_noindex(): void
    {
        config(['seo.indexable' => true]);

        $this->assertStringStartsWith('index', $this->meta($this->get('/satilik')->getContent(), 'name', 'robots'));
        $this->assertSame('noindex, follow', $this->meta($this->get('/satilik?tip=daire')->getContent(), 'name', 'robots'));

        $sample = Property::create([
            'title' => 'ÖRNEK ilan', 'listing_type' => 'sale', 'property_type' => 'daire',
            'status' => 'published', 'is_sample' => true,
        ]);
        $this->assertSame('noindex, follow', $this->meta($this->get($sample->url())->getContent(), 'name', 'robots'));

        // Hukuki metin girilmediyse "hazırlanıyor" sayfası dizine girmez; girilince açılır
        $this->assertSame('noindex, follow', $this->meta($this->get(route('legal.kvkk'))->getContent(), 'name', 'robots'));
        Page::where('key', 'kvkk')->update(['body' => '<p>Onaylı metin</p>', 'status' => 'published']);
        $this->assertStringStartsWith('index', $this->meta($this->get(route('legal.kvkk'))->getContent(), 'name', 'robots'));
    }

    public function test_sitemap_lists_published_content_only(): void
    {
        $draft = Project::create(['title' => 'Taslak Proje', 'status' => 'draft']);
        $scheduled = BlogPost::create(['title' => 'Gelecek Yazı', 'status' => 'published', 'published_at' => now()->addWeek()]);
        $post = BlogPost::create(['title' => 'Yayındaki Yazı', 'status' => 'published', 'published_at' => now()->subDay()]);
        $real = Property::create(['title' => 'Gerçek İlan', 'listing_type' => 'rent', 'property_type' => 'daire', 'status' => 'published']);
        $sample = Property::create(['title' => 'Örnek İlan', 'listing_type' => 'rent', 'property_type' => 'daire', 'status' => 'published', 'is_sample' => true]);

        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'Geçerli XML değil');
        // iterator_to_array(..., false): SimpleXML öğelerinin hepsi aynı "url" anahtarını taşır
        $locs = array_map(fn ($u) => (string) $u->loc, iterator_to_array($xml->url, false));

        // Sayfada üretilen bağlantılarla aynı adresler
        foreach ([route('home'), route('about'), route('projects.index'), route('services.index'), route('contact'),
            route('properties.sale'), route('properties.rent'), $real->url(), route('blog.show', $post)] as $expected) {
            $this->assertContains($expected, $locs);
        }
        foreach (Project::published()->get() as $project) {
            $this->assertContains($project->url(), $locs);
        }

        $this->assertNotContains($draft->url(), $locs);
        $this->assertNotContains(route('blog.show', $scheduled), $locs);
        $this->assertNotContains($sample->url(), $locs);
        $this->assertNotContains(route('legal.kvkk'), $locs, 'Metni olmayan hukuki sayfa haritada olmamalı');
        $this->assertSame(count($locs), count(array_unique($locs)), 'Yinelenen adres var');

        // Haritadaki her adres gerçekten açılıyor
        foreach ($locs as $loc) {
            $this->get(parse_url($loc, PHP_URL_PATH) ?: '/')->assertOk();
        }
    }

    public function test_sitemap_hides_portfolio_when_module_is_off(): void
    {
        app(SettingService::class)->set('modules.real_estate', false, 'modules');

        $this->get('/sitemap.xml')->assertOk()
            ->assertDontSee(route('properties.index'), false)
            ->assertDontSee(route('properties.sale'), false);
    }

    public function test_share_image_uses_an_existing_variant_and_absolute_url(): void
    {
        $media = $this->fakeImage();
        $post = BlogPost::create([
            'title' => 'Paylaşım Görselli Yazı', 'excerpt' => 'Kısa özet', 'status' => 'published',
            'published_at' => now()->subHour(), 'cover_media_id' => $media->id,
        ]);

        $html = $this->get(route('blog.show', $post))->assertOk()->getContent();

        $image = $this->meta($html, 'property', 'og:image');
        $this->assertSame(url(Storage::disk('media')->url("{$media->uuid}/1600.webp")), $image);
        $this->assertStringStartsWith('http', $image);
        Storage::disk('media')->assertExists("{$media->uuid}/1600.webp");
        $this->assertSame('800', $this->meta($html, 'property', 'og:image:height'));
        $this->assertSame('article', $this->meta($html, 'property', 'og:type'));
        $this->assertSame('summary_large_image', $this->meta($html, 'name', 'twitter:card'));
    }

    public function test_pages_without_image_fall_back_to_home_cover(): void
    {
        $media = $this->fakeImage();
        $this->assertNull($this->meta($this->get('/iletisim')->getContent(), 'property', 'og:image'));   // cache dolar (görselsiz)

        Page::where('key', 'home')->firstOrFail()->update(['cover_media_id' => $media->id]);   // cache temizlenmeli

        $this->assertSame(url(Storage::disk('media')->url("{$media->uuid}/1600.webp")), $this->meta($this->get('/iletisim')->getContent(), 'property', 'og:image'));
    }

    public function test_home_has_organization_and_website_schema(): void
    {
        $graphs = $this->jsonLd($this->get('/')->getContent());

        $org = $this->graph($graphs, 'Organization');
        $this->assertNotNull($org);
        $this->assertSame('GAB Architects', $org['name']);
        $this->assertSame(settings('contact.address') ? 'TR' : null, $org['address']['addressCountry'] ?? null);
        $this->assertArrayNotHasKey('telephone', $org, 'Telefon girilmemişken uydurulmamalı');
        $this->assertNotNull($this->graph($graphs, 'WebSite'));
    }

    public function test_breadcrumb_schema_matches_visible_breadcrumb(): void
    {
        $project = Project::published()->firstOrFail();
        $html = $this->get($project->url())->getContent();

        $crumbs = $this->graph($this->jsonLd($html), 'BreadcrumbList');
        $this->assertSame(['Anasayfa', 'Projeler', $project->title], array_column($crumbs['itemListElement'], 'name'));
        $this->assertSame([1, 2, 3], array_column($crumbs['itemListElement'], 'position'));
        $this->assertSame($project->url(), end($crumbs['itemListElement'])['item']);
        $this->assertStringContainsString('aria-current="page"', $html);

        // Bağlantısız ara başlık ("Kurumsal") şemaya girmez
        $about = $this->graph($this->jsonLd($this->get('/hakkimizda')->getContent()), 'BreadcrumbList');
        $this->assertSame(['Anasayfa', 'Hakkımızda'], array_column($about['itemListElement'], 'name'));
    }

    public function test_content_schemas(): void
    {
        $post = BlogPost::create(['title' => 'Şema Yazısı', 'excerpt' => 'Özet', 'status' => 'published', 'published_at' => now()->subDay()]);
        $article = $this->graph($this->jsonLd($this->get(route('blog.show', $post))->getContent()), 'BlogPosting');
        $this->assertSame('Şema Yazısı', $article['headline']);
        $this->assertSame($post->published_at->toAtomString(), $article['datePublished']);

        $property = Property::create([
            'title' => 'Kadıköy Daire', 'listing_type' => 'sale', 'property_type' => 'daire', 'city' => 'İstanbul',
            'district' => 'Kadıköy', 'status' => 'published', 'price' => 5250000, 'currency' => 'TRY', 'show_price' => true,
        ]);
        $listing = $this->graph($this->jsonLd($this->get($property->url())->getContent()), 'RealEstateListing');
        $this->assertSame(5250000, (int) $listing['offers']['price']);
        $this->assertSame('TRY', $listing['offers']['priceCurrency']);

        // Fiyat gizliyse şemada da yok
        $property->update(['show_price' => false]);
        $listing = $this->graph($this->jsonLd($this->get($property->url())->getContent()), 'RealEstateListing');
        $this->assertArrayNotHasKey('offers', $listing);
    }

    /** Apache, public/robots.txt dosyası varsa route'a hiç gelmez (ortama göre üretim bozulur) */
    public function test_no_static_robots_file_and_htaccess_has_performance_rules(): void
    {
        $this->assertFileDoesNotExist(public_path('robots.txt'));
        $this->assertFileDoesNotExist(public_path('sitemap.xml'));

        $htaccess = file_get_contents(public_path('.htaccess'));
        $this->assertStringContainsString('mod_deflate', $htaccess);
        $this->assertStringContainsString('max-age=31536000, immutable', $htaccess);
        $this->assertStringContainsString('RewriteRule ^ index.php [L]', $htaccess, 'Laravel yönlendirmesi korunmalı');
    }

    /** Core Web Vitals: LCP öğesi (hero başlığı) JS beklemeden görünür olmalı */
    public function test_hero_heading_is_not_hidden_until_javascript_runs(): void
    {
        preg_match('#<h1 id="hero-title"[^>]*>#', $this->get('/')->getContent(), $m);

        $this->assertNotEmpty($m);
        $this->assertStringNotContainsString('data-reveal', $m[0]);
    }

    public function test_json_ld_cannot_break_out_of_script_tag(): void
    {
        $post = BlogPost::create([
            'title' => 'Başlık </script><script>alert(1)</script>', 'status' => 'published', 'published_at' => now()->subDay(),
        ]);

        $html = $this->get(route('blog.show', $post))->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertSame('Başlık </script><script>alert(1)</script>', $this->graph($this->jsonLd($html), 'BlogPosting')['headline']);
    }
}
