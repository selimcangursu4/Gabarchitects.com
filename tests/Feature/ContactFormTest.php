<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Ayşe Yılmaz',
            'email' => 'ayse@example.com',
            'phone' => '+90 (532) 000 00 00',
            'subject' => 'Villa projesi',
            'message' => 'Sapanca\'daki arsamız için proje geliştirme danışmanlığı almak istiyoruz.',
            'privacy_consent' => '1',
            'source' => 'home_cta',
            'website' => '',
            '_t' => encrypt(now()->subSeconds(10)->timestamp),
        ];
    }

    public function test_home_cta_form_is_rendered(): void
    {
        $this->get('/')
            ->assertSee('id="iletisim-formu"', false)
            ->assertSee('name="source" value="home_cta"', false)
            ->assertSee('name="website"', false);   // honeypot
    }

    public function test_valid_submission_is_stored_for_admin_webforms_and_notifies(): void
    {
        $this->from('/')->post('/iletisim', $this->payload())
            ->assertRedirect(url('/').'#iletisim-formu')
            ->assertSessionHas('contact_success', 'home_cta');

        $message = ContactMessage::sole();
        $this->assertSame('Ayşe Yılmaz', $message->name);
        $this->assertSame('home_cta', $message->source);
        $this->assertSame('new', $message->status->value);
        $this->assertTrue($message->privacy_consent);
        $this->assertNotNull($message->consented_at);
        $this->assertNull($message->read_at);   // panelde "okunmamış"

        Mail::assertSent(ContactMessageReceived::class);
    }

    public function test_success_message_shows_only_on_submitted_form(): void
    {
        $this->from('/')->post('/iletisim', $this->payload());

        $this->get('/')->assertSee('Mesajınız bize ulaştı');
    }

    public function test_property_inquiry_is_linked_to_listing(): void
    {
        $property = Property::create([
            'title' => 'Test Villa', 'listing_type' => 'sale', 'property_type' => 'villa', 'status' => 'published',
        ]);

        $this->from('/portfoy/'.$property->slug)
            ->post('/iletisim', $this->payload(['source' => 'property', 'property_id' => $property->id]))
            ->assertSessionHasNoErrors();

        $this->assertTrue(ContactMessage::sole()->property->is($property));
    }

    public function test_requires_privacy_consent_and_valid_fields(): void
    {
        $this->from('/iletisim')->post('/iletisim', $this->payload([
            'privacy_consent' => null, 'email' => 'gecersiz', 'message' => 'kısa', 'phone' => 'abc<script>',
        ]))->assertSessionHasErrors(['privacy_consent', 'email', 'message', 'phone']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->from('/')->post('/iletisim', $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_too_fast_or_tampered_submissions_are_rejected(): void
    {
        $this->from('/')->post('/iletisim', $this->payload(['_t' => encrypt(now()->timestamp)]))
            ->assertSessionHasErrors('_t');
        $this->from('/')->post('/iletisim', $this->payload(['_t' => 'sahte-deger']))
            ->assertSessionHasErrors('_t');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_rate_limit_after_five_submissions(): void
    {
        foreach (range(1, 5) as $i) {
            $this->from('/')->post('/iletisim', $this->payload())->assertRedirect();
        }

        $this->from('/')->post('/iletisim', $this->payload())->assertStatus(429);
        $this->assertSame(5, ContactMessage::count());
    }

    public function test_submission_is_saved_even_if_mail_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP kapalı'));

        $this->from('/')->post('/iletisim', $this->payload())->assertSessionHasNoErrors();

        $this->assertSame(1, ContactMessage::count());
    }
}
