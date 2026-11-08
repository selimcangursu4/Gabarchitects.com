<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Property;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();   // içerik + ayarlar (örnek ilanlar testlerde yüklenmez: APP_ENV=testing)
    }

    private function listing(array $attrs): Property
    {
        return Property::create($attrs + [
            'title' => 'İlan '.uniqid(),
            'listing_type' => 'sale',
            'property_type' => 'daire',
            'city' => 'İstanbul',
            'district' => 'Beşiktaş',
            'status' => PublishStatus::Published,
            'price' => 10_000_000,
        ]);
    }

    public function test_sale_and_rent_pages_only_show_their_type(): void
    {
        $sale = $this->listing(['title' => 'Satılık Daire Test']);
        $rent = $this->listing(['title' => 'Kiralık Daire Test', 'listing_type' => 'rent']);

        $this->get('/satilik')->assertOk()->assertSee($sale->title)->assertDontSee($rent->title);
        $this->get('/kiralik')->assertOk()->assertSee($rent->title)->assertDontSee($sale->title);
        $this->get('/portfoy')->assertOk()->assertSee($sale->title)->assertSee($rent->title);
    }

    public function test_filters_by_type_location_price_area_and_rooms(): void
    {
        $match = $this->listing(['title' => 'Uygun Villa', 'property_type' => 'villa', 'city' => 'Muğla', 'district' => 'Bodrum', 'price' => 30_000_000, 'area_m2' => 300, 'rooms' => '5+2']);
        $this->listing(['title' => 'Pahalı Villa', 'property_type' => 'villa', 'city' => 'Muğla', 'district' => 'Bodrum', 'price' => 90_000_000, 'area_m2' => 300, 'rooms' => '5+1']);
        $this->listing(['title' => 'Küçük Villa', 'property_type' => 'villa', 'city' => 'Muğla', 'district' => 'Bodrum', 'price' => 20_000_000, 'area_m2' => 120, 'rooms' => '3+1']);
        $this->listing(['title' => 'İstanbul Villa', 'property_type' => 'villa', 'price' => 30_000_000, 'area_m2' => 300, 'rooms' => '5+1']);
        $this->listing(['title' => 'Bodrum Daire', 'city' => 'Muğla', 'district' => 'Bodrum', 'price' => 30_000_000, 'area_m2' => 300, 'rooms' => '5+1']);

        $response = $this->get('/satilik?'.http_build_query([
            'tip' => 'villa', 'il' => 'Muğla', 'ilce' => 'Bodrum',
            'fiyat_min' => '25.000.000', 'fiyat_max' => '50000000',   // binlik ayırıcı da kabul edilir
            'm2_min' => 200, 'oda' => [5],
        ]));

        $response->assertOk()->assertSee($match->title);
        foreach (['Pahalı Villa', 'Küçük Villa', 'İstanbul Villa', 'Bodrum Daire'] as $excluded) {
            $response->assertDontSee($excluded);
        }
    }

    public function test_room_count_is_derived_from_rooms_text(): void
    {
        $this->assertSame(3, $this->listing(['rooms' => '3+1'])->room_count);
        $this->assertNull($this->listing(['rooms' => null, 'property_type' => 'arsa'])->room_count);
    }

    public function test_invalid_filter_values_are_ignored_instead_of_erroring(): void
    {
        $listing = $this->listing(['title' => 'Her Koşulda Görünen']);

        $this->get('/portfoy?tip=gecersiz&fiyat_min=abc&oda[]=99&siralama=hack')
            ->assertOk()
            ->assertSee($listing->title);
    }

    public function test_draft_and_scheduled_listings_are_hidden(): void
    {
        $draft = $this->listing(['title' => 'Taslak İlan', 'status' => PublishStatus::Draft]);
        $future = $this->listing(['title' => 'Gelecek İlan', 'published_at' => now()->addDay()]);

        $this->get('/portfoy')->assertDontSee($draft->title)->assertDontSee($future->title);
        $this->get('/portfoy/'.$draft->slug)->assertNotFound();
    }

    public function test_home_shows_latest_nine_featured_listings_only(): void
    {
        $featured = collect(range(1, 10))->map(function (int $i) {
            $p = $this->listing(['title' => "Öne Çıkan {$i}", 'is_featured' => true]);
            $p->forceFill(['created_at' => now()->subDays(20 - $i)])->saveQuietly();

            return $p;
        });
        $notFeatured = $this->listing(['title' => 'Normal İlan']);

        $response = $this->get('/');

        // En eski öne çıkan (1) listelenmez; son 9 (2..10) listelenir
        $response->assertDontSee('Öne Çıkan 1<', false);
        foreach (range(2, 10) as $i) {
            $response->assertSee("Öne Çıkan {$i}");
        }
        $response->assertDontSee($notFeatured->title);
        $this->assertSame(10, $featured->count());
    }

    public function test_real_estate_module_can_be_disabled(): void
    {
        app(SettingService::class)->set('modules.real_estate', false, 'modules');

        $this->get('/satilik')->assertNotFound();
        $this->get('/portfoy')->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee('role="search"', false);
    }

    /** Kullanıcı talebi: hero'da fiyat / m² / oda "Daha fazla seçenek" arkasında; yan panelde hep açık */
    public function test_hero_hides_advanced_filters_behind_more_options(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertMatchesRegularExpression('/<button[^>]*aria-controls="f-more-hero"[^>]*aria-expanded="false"/s', $html);
        $this->assertStringContainsString('Daha fazla seçenek', $html);

        // Gelişmiş alanlar açılır bölümün içinde ve ilk boyamada gizli (data-cloak)
        preg_match('/<div id="f-more-hero"[^>]*>/s', $html, $open);
        $this->assertNotEmpty($open);
        $this->assertStringContainsString('data-cloak', $open[0]);
        $section = substr($html, strpos($html, 'id="f-more-hero"'));
        foreach (['name="fiyat_min"', 'name="fiyat_max"', 'name="m2_min"', 'name="m2_max"', 'name="oda[]"'] as $field) {
            $this->assertStringContainsString($field, $section, "{$field} açılır bölümde olmalı");
        }
        // Temel alanlar dışarıda kalır
        $this->assertLessThan(strpos($html, 'id="f-more-hero"'), strpos($html, 'name="il"'));

        // Portföy yan paneli: düğme yok, alanlar gizlenmez
        $panel = $this->get('/portfoy')->getContent();
        $this->assertStringNotContainsString('data-more-toggle', $panel);
        $this->assertStringNotContainsString('data-cloak', $panel);
        $this->assertStringContainsString('name="fiyat_min"', $panel);
    }

    public function test_price_hidden_listings_show_contact_text(): void
    {
        $listing = $this->listing(['title' => 'Fiyatsız İlan', 'show_price' => false]);

        $this->get('/portfoy')->assertSee('Fiyat için arayın');
        $this->assertNull($listing->formattedPrice());
    }
}
