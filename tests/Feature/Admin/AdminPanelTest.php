<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\BlogCategories\BlogCategoryResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Menus\MenuResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\ProjectCategories\ProjectCategoryResource;
use App\Filament\Resources\Redirects\RedirectResource;
use App\Filament\Resources\TeamMembers\TeamMemberResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Project;
use App\Models\Property;
use App\Models\Reference;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('media');
        Storage::fake('local');
    }

    private function userWithRole(Role $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role->value);

        return $user;
    }

    /** Tüm panel sayfaları Super Admin için hatasız açılır. */
    public function test_every_admin_page_renders_for_super_admin(): void
    {
        $admin = $this->userWithRole(Role::SuperAdmin);

        $property = Property::create(['title' => 'Panel Testi', 'listing_type' => 'sale', 'property_type' => 'villa', 'city' => 'İstanbul', 'status' => 'published']);
        $post = BlogPost::create(['title' => 'Panel Yazısı', 'status' => 'draft']);
        $message = ContactMessage::create(['name' => 'Test', 'email' => 't@example.com', 'message' => 'Merhaba dünya mesajı', 'source' => 'home_cta', 'status' => 'new']);

        $urls = [
            '/admin',
            '/admin/properties', '/admin/properties/create', "/admin/properties/{$property->id}/edit",
            '/admin/projects', '/admin/projects/create', '/admin/projects/'.Project::first()->id.'/edit',
            '/admin/services', '/admin/services/create', '/admin/services/'.Service::first()->id.'/edit',
            '/admin/references', '/admin/references/create', '/admin/references/'.Reference::first()->id.'/edit',
            '/admin/blog-posts', '/admin/blog-posts/create', "/admin/blog-posts/{$post->id}/edit",
        ];

        foreach (static::extraUrls($message) as $url) {
            $urls[] = $url;
        }

        foreach ($urls as $url) {
            $status = $this->actingAs($admin)->get($url)->status();
            $this->assertSame(200, $status, "{$url} → {$status}");
        }
    }

    /** Sonradan eklenen resource sayfaları (PHASE 04 ilerledikçe genişler). */
    protected static function extraUrls(ContactMessage $message): array
    {
        return array_filter([
            class_exists(PageResource::class) ? '/admin/pages' : null,
            class_exists(PageResource::class) ? '/admin/pages/'.Page::where('key', 'home')->value('id').'/edit' : null,
            class_exists(ContactMessageResource::class) ? '/admin/contact-messages' : null,
            class_exists(ContactMessageResource::class) ? "/admin/contact-messages/{$message->id}" : null,
            class_exists(MediaResource::class) ? '/admin/media' : null,
            class_exists(MenuResource::class) ? '/admin/menus/'.Menu::where('key', 'header')->value('id').'/edit' : null,
            class_exists(UserResource::class) ? '/admin/users' : null,
            class_exists(UserResource::class) ? '/admin/users/create' : null,
            class_exists(FaqResource::class) ? '/admin/faqs' : null,
            class_exists(ProjectCategoryResource::class) ? '/admin/project-categories' : null,
            class_exists(BlogCategoryResource::class) ? '/admin/blog-categories' : null,
            class_exists(TeamMemberResource::class) ? '/admin/team-members' : null,
            class_exists(RedirectResource::class) ? '/admin/redirects' : null,
            class_exists(ActivityLogResource::class) ? '/admin/activity-logs' : null,
            class_exists(ManageSettings::class) ? '/admin/settings' : null,
        ]);
    }

    /**
     * Regresyon (BUG-017): panelin ÜRETTİĞİ bağlantılar çalışmalı.
     * Liste sayfalarındaki tüm /admin/... bağlantıları (düzenle, görüntüle, sayfalama) tek tek açılır.
     * Sitede slug kullanan modellerde düzenleme adresi ID ile üretilmeli.
     */
    public function test_links_generated_by_admin_list_pages_resolve(): void
    {
        $admin = $this->userWithRole(Role::SuperAdmin);
        $property = Property::create(['title' => 'Bağlantı Testi İlanı', 'listing_type' => 'sale', 'property_type' => 'villa', 'city' => 'İstanbul', 'status' => 'published']);
        BlogPost::create(['title' => 'Bağlantı Testi Yazısı', 'status' => 'published']);
        ContactMessage::create(['name' => 'Test', 'email' => 't@example.com', 'message' => 'Bağlantı testi mesajı', 'source' => 'home_cta', 'status' => 'new']);

        $lists = ['/admin', '/admin/properties', '/admin/projects', '/admin/services', '/admin/references', '/admin/blog-posts',
            '/admin/pages', '/admin/contact-messages', '/admin/media', '/admin/menus', '/admin/users'];

        $checked = [];
        foreach ($lists as $list) {
            $html = $this->actingAs($admin)->get($list)->assertOk()->getContent();
            preg_match_all('#href="'.preg_quote(url('/admin'), '#').'(/[^"\#?]*)"#', $html, $m);

            foreach (array_unique($m[1]) as $path) {
                if (isset($checked[$path]) || str_contains($path, 'logout')) {
                    continue;
                }
                $status = $this->actingAs($admin)->get('/admin'.$path)->status();
                $this->assertSame(200, $status, "{$list} sayfasındaki /admin{$path} bağlantısı {$status} döndü");
                $checked[$path] = true;
            }
        }

        // Slug'lı modellerde düzenleme bağlantısı ID içermeli, slug içermemeli
        $this->assertArrayHasKey("/properties/{$property->id}/edit", $checked);
        $this->assertArrayNotHasKey("/properties/{$property->slug}/edit", $checked);
        $this->assertGreaterThan(30, count($checked), 'Yeterli sayıda bağlantı kontrol edilmedi');
    }

    /** Kullanıcı kararı: Sayfalar, Medya, Menüler, Yönlendirmeler sidebar'da gösterilmez (adresle erişilebilir). */
    public function test_hidden_resources_are_not_in_sidebar_but_still_reachable(): void
    {
        $admin = $this->userWithRole(Role::SuperAdmin);
        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        foreach (['pages', 'media', 'menus', 'redirects'] as $slug) {
            $this->assertStringNotContainsString('href="'.url("/admin/{$slug}").'"', $html, "/admin/{$slug} sidebar'da görünmemeli");
            $this->actingAs($admin)->get("/admin/{$slug}")->assertOk();
        }

        // Diğer menü öğeleri yerinde
        foreach (['properties', 'projects', 'services', 'references', 'contact-messages', 'settings', 'users'] as $slug) {
            $this->assertStringContainsString('href="'.url("/admin/{$slug}").'"', $html);
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/properties')->assertRedirect('/admin/login');
    }

    public function test_users_without_role_or_inactive_cannot_access_panel(): void
    {
        $noRole = User::factory()->create(['is_active' => true]);
        $inactive = $this->userWithRole(Role::Admin);
        $inactive->update(['is_active' => false]);

        $this->actingAs($noRole)->get('/admin')->assertForbidden();
        $this->actingAs($inactive)->get('/admin')->assertForbidden();
    }

    public function test_editor_can_manage_content_but_not_users_settings_or_messages(): void
    {
        $editor = $this->userWithRole(Role::Editor);

        foreach (['/admin/properties', '/admin/projects', '/admin/services', '/admin/references', '/admin/blog-posts'] as $url) {
            $this->actingAs($editor)->get($url)->assertOk();
        }

        foreach (['/admin/users', '/admin/settings', '/admin/menus', '/admin/contact-messages', '/admin/activity-logs', '/admin/redirects'] as $url) {
            $this->actingAs($editor)->get($url)->assertForbidden();
        }
    }
}
