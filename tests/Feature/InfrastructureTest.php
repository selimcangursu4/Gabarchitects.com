<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class InfrastructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();   // her test kendi transaction'ında (bkz. HomePageTest notu)
    }

    public function test_seeded_admin_is_super_admin_and_can_do_everything(): void
    {
        $admin = User::where('email', 'admin@test.local')->firstOrFail();

        $this->assertTrue($admin->hasRole(Role::SuperAdmin->value));
        $this->assertTrue(Gate::forUser($admin)->allows('users.manage'));
        $this->assertTrue(Gate::forUser($admin)->allows('settings.manage'));
    }

    public function test_editor_can_manage_content_but_not_users_or_settings(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor->value);

        $this->assertTrue($editor->can('projects.manage'));
        $this->assertTrue($editor->can('blog.manage'));
        $this->assertFalse($editor->can('users.manage'));
        $this->assertFalse($editor->can('settings.manage'));
        $this->assertFalse($editor->can('menus.manage'));
    }

    public function test_admin_can_manage_settings_but_not_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin->value);

        $this->assertTrue($admin->can('settings.manage'));
        $this->assertFalse($admin->can('users.manage'));
    }

    public function test_legacy_urls_redirect_permanently(): void
    {
        $this->get('/about/')->assertRedirect('/hakkimizda')->assertStatus(301);
        $this->get('/contact-2')->assertRedirect('/iletisim')->assertStatus(301);

        $this->assertSame(1, Redirect::where('from_path', '/about')->value('hits'));
    }

    public function test_new_redirect_takes_effect_immediately(): void
    {
        $this->get('/eski-proje')->assertNotFound();

        Redirect::create(['from_path' => 'eski-proje/', 'to_url' => '/projeler', 'status_code' => 302]);

        $this->get('/eski-proje')->assertRedirect('/projeler')->assertStatus(302);
    }

    public function test_settings_are_cached_and_refreshed_on_change(): void
    {
        $this->assertSame('GAB Architects', settings('site.brand'));

        app(SettingService::class)->set('contact.phone', '+90 212 000 00 00', 'contact');

        $this->assertSame('+90 212 000 00 00', settings('contact.phone'));
    }

    public function test_legal_pages_are_seeded_as_empty_drafts(): void
    {
        foreach (['kvkk', 'privacy', 'cookies'] as $key) {
            $page = Page::findByKey($key);
            $this->assertNotNull($page);
            $this->assertSame('draft', $page->status->value);
            $this->assertNull($page->body, 'Hukuki metin uydurulmamalı.');
        }
    }
}
