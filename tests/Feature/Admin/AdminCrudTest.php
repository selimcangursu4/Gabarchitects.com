<?php

namespace Tests\Feature\Admin;

use App\Enums\ContactStatus;
use App\Enums\ListingType;
use App\Enums\Role;
use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Pages\EditPage;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Properties\Pages\CreateProperty;
use App\Filament\Resources\Properties\Pages\ListProperties;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use App\Services\MediaService;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('media');
        Storage::fake('local');

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole(Role::SuperAdmin->value);
        $this->actingAs($this->admin);
    }

    private function media(): Media
    {
        $path = tempnam(sys_get_temp_dir(), 'gab').'.jpg';
        imagejpeg(imagecreatetruecolor(900, 600), $path);

        return app(MediaService::class)->importFile($path);
    }

    public function test_create_property_with_uploaded_cover_and_sanitized_body(): void
    {
        Livewire::test(CreateProperty::class)
            ->fillForm([
                'title' => 'Bebek Boğaz Manzaralı Daire',
                'listing_type' => 'sale',
                'property_type' => 'daire',
                'listing_status' => 'available',
                'rooms' => '3+1',
                'area_m2' => 180,
                'city' => 'İstanbul',
                'district' => 'Beşiktaş',
                'price' => 45000000,
                'currency' => 'TRY',
                'show_price' => true,
                'status' => 'published',
                'is_featured' => true,
                'body' => '<p>Güzel daire</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>',
                'cover_media_id' => UploadedFile::fake()->image('kapak.jpg', 1200, 800),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $property = Property::where('title', 'Bebek Boğaz Manzaralı Daire')->sole();

        $this->assertSame('bebek-bogaz-manzarali-daire', $property->slug);
        $this->assertSame(3, $property->room_count);
        $this->assertTrue($property->is_featured);
        $this->assertTrue($property->isPubliclyVisible());

        // Görsel medya kütüphanesine girdi, WebP türevleri üretildi
        $this->assertNotNull($property->cover);
        $this->assertSame('kapak.jpg', $property->cover->original_name);
        $this->assertNotEmpty($property->cover->widths('webp'));

        // XSS temizliği
        $this->assertStringContainsString('Güzel daire', $property->body);
        $this->assertStringNotContainsString('<script', $property->body);
        $this->assertStringNotContainsString('javascript:', $property->body);

        // İşlem kaydı
        $this->assertTrue(ActivityLog::where('event', 'created')->where('subject_type', $property->getMorphClass())->where('user_id', $this->admin->id)->exists());

        // Sitede görünür (talep 2: öne çıkan)
        $this->get('/')->assertSee('Bebek Boğaz Manzaralı Daire');
    }

    public function test_listing_type_offers_sale_rent_and_floor_exchange(): void
    {
        $this->assertSame(
            ['sale' => 'Satılık', 'rent' => 'Kiralık', 'exchange' => 'Kat Karşılığı'],
            collect(ListingType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all(),
        );

        Livewire::test(CreateProperty::class)
            ->fillForm([
                'title' => 'Kat Karşılığı Arsa',
                'listing_type' => 'exchange',
                'property_type' => 'arsa',
                'listing_status' => 'available',
                'city' => 'İstanbul',
                'district' => 'Sarıyer',
                'currency' => 'TRY',
                'status' => 'published',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(ListingType::Exchange, Property::where('title', 'Kat Karşılığı Arsa')->sole()->listing_type);

        Livewire::test(CreateProperty::class)
            ->fillForm(['title' => 'Geçersiz', 'listing_type' => 'takas', 'property_type' => 'arsa', 'listing_status' => 'available', 'status' => 'draft'])
            ->call('create')
            ->assertHasFormErrors(['listing_type']);

        Livewire::test(ListProperties::class)->loadTable()->assertSee('Kat Karşılığı');
    }

    public function test_property_validation(): void
    {
        Livewire::test(CreateProperty::class)
            ->fillForm(['title' => '', 'listing_type' => null, 'property_type' => null, 'city' => '', 'rooms' => 'üç oda'])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'listing_type' => 'required', 'property_type' => 'required', 'city' => 'required', 'rooms']);
    }

    public function test_edit_project_gallery_order_and_logs_changes(): void
    {
        $project = Project::firstOrFail();
        $a = $this->media();
        $b = $this->media();
        $project->syncGallery([$a->id, $b->id]);

        // Galeriyi ters sıraya çevir + durum değiştir
        Livewire::test(EditProject::class, ['record' => $project->id])
            ->fillForm([
                'gallery_media' => [(string) $b->id, (string) $a->id],
                'progress' => 'ongoing',
                'is_featured' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $project->refresh();
        $this->assertEquals([$b->id, $a->id], $project->gallery()->pluck('media.id')->all());
        $this->assertSame('ongoing', $project->progress->value);

        $log = ActivityLog::where('event', 'updated')->where('subject_id', $project->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertArrayHasKey('progress', $log->properties['new']);

        // Talep 3: devam eden projeler bölümü
        $this->get('/projeler')->assertSeeInOrder(['Devam eden projeler', $project->title]);
    }

    public function test_create_service_appears_on_site(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm(['title' => 'Peyzaj Tasarımı', 'excerpt' => 'Açık alan ve bahçe tasarımı.', 'status' => 'published'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/hizmetler')->assertSee('Peyzaj Tasarımı');
        $this->get('/hizmetler/peyzaj-tasarimi')->assertOk();
    }

    public function test_home_page_content_is_editable(): void
    {
        $home = Page::findByKey('home');

        Livewire::test(EditPage::class, ['record' => $home->id])
            ->fillForm([
                'content.hero.title' => 'Yeni hero başlığı.',
                // Basit repeater'ın iç durumu: [['step' => ...]] (arayüz bunu kendisi dönüştürür)
                'content.process.steps' => [['step' => 'Analiz'], ['step' => 'Tasarım'], ['step' => 'Uygulama']],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $home->refresh();
        $this->assertSame('Yeni hero başlığı.', $home->field('hero.title'));
        $this->assertSame(['Analiz', 'Tasarım', 'Uygulama'], $home->field('process.steps'));
        $this->assertNotNull($home->field('statement'), 'Formdaki diğer alanlar korunmalı');

        $this->get('/')->assertSee('Yeni hero başlığı.');
    }

    public function test_menu_items_can_be_renamed_and_child_added(): void
    {
        $menu = Menu::where('key', 'header')->firstOrFail();

        $component = Livewire::test(EditMenu::class, ['record' => $menu->id]);
        $state = $component->get('data.rootItems');
        $kurumsalKey = collect($state)->search(fn ($item) => $item['label'] === 'Kurumsal');
        $state[$kurumsalKey]['children'][] = ['label' => 'Ekibimiz', 'url' => '/hakkimizda#ekip', 'is_active' => true, 'open_in_new_tab' => false];

        $component->set('data.rootItems', $state)->call('save')->assertHasNoFormErrors();

        $child = $menu->items()->where('label', 'Ekibimiz')->firstOrFail();
        $this->assertSame($menu->id, $child->menu_id);
        $this->assertNotNull($child->parent_id);

        $this->get('/')->assertSee('Ekibimiz');
    }

    public function test_settings_save_updates_site_and_logs(): void
    {
        Livewire::test(ManageSettings::class)
            ->fillForm([
                'contact' => ['phone' => '+90 212 555 00 00', 'email' => 'info@gabarchitects.com'],
                'modules' => ['real_estate' => false],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('+90 212 555 00 00', settings('contact.phone'));
        $this->get('/')->assertSee('+90 212 555 00 00')->assertDontSee('role="search"', false);
        $this->get('/satilik')->assertNotFound();
        $this->assertTrue(ActivityLog::where('description', 'like', 'Site ayarları:%')->exists());
    }

    public function test_opening_web_form_marks_read_and_status_can_change(): void
    {
        $message = ContactMessage::create([
            'name' => 'Ali Veli', 'email' => 'ali@example.com', 'message' => 'Proje hakkında görüşmek istiyorum.',
            'source' => 'home_cta', 'status' => ContactStatus::New, 'privacy_consent' => true,
        ]);

        Livewire::test(ViewContactMessage::class, ['record' => $message->id])
            ->assertSee('Ali Veli')
            ->callAction('update_status', data: ['status' => 'answered', 'internal_notes' => 'Telefonla dönüldü.']);

        $message->refresh();
        $this->assertNotNull($message->read_at);
        $this->assertSame(ContactStatus::Answered, $message->status);
        $this->assertSame('Telefonla dönüldü.', $message->internal_notes);
        $this->assertSame($this->admin->id, $message->handled_by);
    }

    public function test_create_editor_user_and_role_rules(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Editör Kullanıcı', 'email' => 'editor@gab.test', 'role' => 'editor', 'is_active' => true,
                'password' => 'GucluSifre123', 'password_confirmation' => 'GucluSifre123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $editor = User::where('email', 'editor@gab.test')->sole();
        $this->assertTrue($editor->hasRole('editor'));
        $this->assertTrue(Hash::check('GucluSifre123', $editor->password));

        // Zayıf şifre reddedilir
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'X', 'email' => 'x@gab.test', 'role' => 'editor', 'password' => '123', 'password_confirmation' => '123'])
            ->call('create')
            ->assertHasFormErrors(['password']);

        // Boş şifreyle düzenleme mevcut şifreyi değiştirmez
        Livewire::test(EditUser::class, ['record' => $editor->id])
            ->fillForm(['name' => 'Editör Yeni Ad', 'password' => null, 'password_confirmation' => null])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertTrue(Hash::check('GucluSifre123', $editor->fresh()->password));

        // Kişi kendini silemez
        $this->assertFalse($this->admin->can('delete', $this->admin));
        $this->assertTrue($this->admin->can('delete', $editor));
    }

    public function test_media_in_use_cannot_be_deleted_but_unused_can(): void
    {
        $used = $this->media();
        Project::firstOrFail()->update(['cover_media_id' => $used->id]);
        $unused = $this->media();

        $this->assertFalse($this->admin->can('delete', $used));
        $this->assertTrue($this->admin->can('delete', $unused));

        Livewire::test(ListMedia::class)
            ->callTableAction('purge', $unused);

        $this->assertNull(Media::withTrashed()->find($unused->id));
        Storage::disk('media')->assertMissing("{$unused->uuid}/480.webp");
        $this->assertNotNull(Media::find($used->id));
    }

    public function test_media_bulk_upload(): void
    {
        $before = Media::count();

        Livewire::test(ListMedia::class)
            ->callAction('upload', data: ['files' => [
                UploadedFile::fake()->image('a.jpg', 800, 600),
                UploadedFile::fake()->image('b.png', 640, 480),
            ]]);

        $this->assertSame($before + 2, Media::count());
    }

    public function test_editor_cannot_open_users_or_settings_components(): void
    {
        $editor = User::factory()->create(['is_active' => true]);
        $editor->assignRole('editor');
        $this->actingAs($editor);

        $this->assertFalse(ManageSettings::canAccess());
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
    }

    /** BUG-030: panel listelerinde görsel sütunu boş görünüyordu (göreli adres → Filament diskte arıyordu) */
    public function test_admin_lists_show_cover_thumbnails_with_existing_files(): void
    {
        $media = $this->media();
        $property = Property::create([
            'title' => 'Görselli İlan', 'listing_type' => 'sale', 'property_type' => 'daire',
            'city' => 'İstanbul', 'status' => 'published', 'cover_media_id' => $media->id,
        ]);
        Project::query()->update(['cover_media_id' => $media->id]);   // hangisi ilk sayfada olursa olsun

        $expected = $media->absoluteUrl(480);
        $this->assertStringStartsWith('http', $expected);
        Storage::disk('media')->assertExists(str_replace(Storage::disk('media')->url(''), '', $media->url(480)));

        Livewire::test(ListProperties::class)->assertSeeHtml('src="'.$expected.'"')->assertSee($property->title);
        Livewire::test(ListProjects::class)->loadTable()->assertSeeHtml('src="'.$expected.'"');   // tablo ertelemeli yüklenir
        Livewire::test(ListMedia::class)->assertSeeHtml('src="'.$expected.'"');
    }

    public function test_media_url_never_points_to_a_missing_variant(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'gab').'.jpg';
        imagejpeg(imagecreatetruecolor(400, 300), $path);   // 480'den küçük: tek türev 400
        $small = app(MediaService::class)->importFile($path);

        $this->assertSame([400], $small->widths());
        foreach ([null, 480, 960, 2400, 100] as $width) {
            $relative = str_replace(Storage::disk('media')->url(''), '', $small->url($width));
            Storage::disk('media')->assertExists($relative);
        }
    }

    public function test_login_records_last_login_and_activity(): void
    {
        auth()->logout();
        $user = User::factory()->create(['is_active' => true, 'password' => 'GirisSifresi123']);
        $user->assignRole('admin');

        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'GirisSifresi123'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertTrue(ActivityLog::where('user_id', $user->id)->where('event', 'login')->exists());
    }
}
