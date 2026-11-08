<?php

namespace Tests\Feature;

use App\Filament\Widgets\StatsOverview;
use App\Models\Property;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Kullanıcı kararı: ilan sayfasında web formu yok; WhatsApp + arama butonları. */
class PropertyContactTest extends TestCase
{
    use RefreshDatabase;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->property = Property::create([
            'title' => 'Bodrum Deniz Manzaralı Villa', 'listing_type' => 'sale', 'property_type' => 'villa',
            'city' => 'Muğla', 'district' => 'Bodrum', 'status' => 'published',
        ]);
    }

    private function setNumbers(?string $phone, ?string $whatsapp): void
    {
        $settings = app(SettingService::class);
        $settings->set('contact.phone', $phone, 'contact');
        $settings->set('contact.whatsapp', $whatsapp, 'contact');
    }

    public function test_property_page_has_no_web_form(): void
    {
        $this->setNumbers('0212 555 00 00', '0532 000 00 00');

        $this->get($this->property->url())
            ->assertOk()
            ->assertDontSee('id="iletisim-formu"', false)
            ->assertDontSee('name="message"', false);
    }

    public function test_whatsapp_button_opens_prefilled_message_about_the_listing(): void
    {
        $this->setNumbers('0212 555 00 00', '0532 000 00 00');

        $html = $this->get($this->property->url())->getContent();

        preg_match('#href="(https://wa\.me/905320000000\?text=[^"]+)"#', $html, $m);
        $this->assertNotEmpty($m, 'WhatsApp bağlantısı bulunamadı');

        $message = rawurldecode(html_entity_decode(explode('?text=', $m[1])[1]));
        $this->assertStringContainsString('Bodrum Deniz Manzaralı Villa', $message);
        $this->assertStringContainsString('GAB-'.str_pad($this->property->id, 5, '0', STR_PAD_LEFT), $message);
        $this->assertStringContainsString($this->property->url(), $message);
    }

    public function test_call_button_uses_phone_number(): void
    {
        $this->setNumbers('0212 555 00 00', null);

        $this->get($this->property->url())
            ->assertSee('href="tel:+902125550000"', false)
            ->assertSee('0212 555 00 00')
            ->assertDontSee('wa.me', false);   // WhatsApp numarası yoksa bağlantı yok (buton pasif)
    }

    /** Kullanıcı kararı: numara yokken de iki buton görünür (pasif); iletişim sayfası yönlendirmesi yok. */
    public function test_without_numbers_both_buttons_are_shown_disabled(): void
    {
        $this->setNumbers(null, null);

        $html = $this->get($this->property->url())->getContent();

        $this->assertStringNotContainsString('Bizimle iletişime geçin', $html);
        $this->assertStringNotContainsString('wa.me', $html);
        $this->assertStringNotContainsString('href="tel:', $html);
        // Masaüstü kartı + mobil alt çubuk: her ikisinde de 2 pasif buton
        $this->assertSame(2, substr_count($html, 'data-contact="whatsapp"'));
        $this->assertSame(2, substr_count($html, 'data-contact="phone"'));
        $this->assertSame(4, substr_count($html, 'aria-disabled="true"'));
    }

    public function test_only_missing_button_is_disabled(): void
    {
        $this->setNumbers('0212 555 00 00', null);

        $html = $this->get($this->property->url())->getContent();

        $this->assertStringContainsString('href="tel:+902125550000"', $html);
        $this->assertSame(2, substr_count($html, 'aria-disabled="true"'));   // yalnızca WhatsApp (masaüstü + mobil)
    }

    public function test_dashboard_warns_when_numbers_missing(): void
    {
        $this->setNumbers(null, null);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super_admin');

        // Dashboard widget'ları lazy yüklenir → widget doğrudan Livewire ile test edilir
        $this->actingAs($admin);
        Livewire::test(StatsOverview::class)->assertSee('İlan butonları')->assertSee('numarası girilmedi');

        $this->setNumbers('0212 555 00 00', '0532 000 00 00');
        Livewire::test(StatsOverview::class)->assertDontSee('İlan butonları');
    }
}
