<?php

namespace Tests\Feature;

use App\Enums\ProjectProgress;
use App\Enums\PublishStatus;
use App\Models\BlogPost;
use App\Models\Project;
use App\Models\Reference;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_all_public_pages_render(): void
    {
        $project = Project::published()->firstOrFail();
        $service = Service::published()->firstOrFail();

        foreach ([
            '/', '/hakkimizda', '/referanslar', '/hizmetler', "/hizmetler/{$service->slug}",
            '/projeler', "/projeler/{$project->slug}", '/portfoy', '/satilik', '/kiralik',
            '/blog', '/iletisim', '/kvkk', '/gizlilik-politikasi', '/cerez-politikasi',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_header_has_home_and_dropdown_menus(): void
    {
        $html = $this->get('/hakkimizda')->assertOk()->getContent();

        $this->assertStringContainsString('>Anasayfa', $html);
        foreach (['Kurumsal', 'Portföyümüz'] as $dropdown) {
            $this->assertMatchesRegularExpression('/<button[^>]*aria-haspopup="true"[^>]*>\s*'.$dropdown.'/u', $html);
        }
        foreach (['/hakkimizda', '/satilik', '/kiralik'] as $child) {
            $this->assertStringContainsString('href="'.url($child).'"', $html);
        }
    }

    public function test_home_featured_projects_are_latest_four_marked_featured(): void
    {
        Project::query()->update(['is_featured' => false]);

        $projects = collect(range(1, 5))->map(function (int $i) {
            $p = Project::create(['title' => "Öne Çıkan Proje {$i}", 'status' => PublishStatus::Published, 'is_featured' => true]);
            $p->forceFill(['created_at' => now()->subDays(10 - $i)])->saveQuietly();

            return $p;
        });

        $response = $this->get('/');

        $response->assertDontSee($projects[0]->url(), false);   // en eski
        foreach ($projects->slice(1) as $project) {
            $response->assertSee($project->url(), false);
        }
    }

    public function test_home_shows_latest_sixteen_featured_references_and_page_shows_all_active(): void
    {
        Reference::query()->delete();

        foreach (range(1, 18) as $i) {
            $r = Reference::create(['name' => "Referans {$i}", 'is_featured' => $i <= 17, 'is_active' => true, 'sort_order' => $i]);
            $r->forceFill(['created_at' => now()->subDays(30 - $i)])->saveQuietly();
        }
        Reference::create(['name' => 'Pasif Referans', 'is_active' => false]);

        $home = $this->get('/');
        $home->assertDontSee('Referans 1<', false);          // öne çıkanların en eskisi → 17'li listede 17. sırada, gösterilmez
        $home->assertSee('Referans 17');
        $home->assertDontSee('Referans 18');                 // öne çıkan değil

        $page = $this->get('/referanslar');
        foreach (range(1, 18) as $i) {
            $page->assertSee("Referans {$i}<", false);
        }
        $page->assertDontSee('Pasif Referans');
    }

    public function test_projects_page_separates_ongoing_projects(): void
    {
        $ongoing = Project::create(['title' => 'Devam Eden Test', 'status' => PublishStatus::Published, 'progress' => ProjectProgress::Ongoing]);

        $this->get('/projeler')
            ->assertSeeInOrder(['Devam eden projeler', $ongoing->title, 'Tamamlanan projeler']);
    }

    public function test_draft_service_and_project_return_404(): void
    {
        $service = Service::create(['title' => 'Taslak Hizmet', 'status' => PublishStatus::Draft]);
        $project = Project::create(['title' => 'Taslak Proje', 'status' => PublishStatus::Draft]);

        $this->get("/hizmetler/{$service->slug}")->assertNotFound();
        $this->get("/projeler/{$project->slug}")->assertNotFound();
        $this->get('/hizmetler')->assertDontSee('Taslak Hizmet');
    }

    public function test_project_detail_has_previous_and_next_navigation(): void
    {
        $projects = Project::published()->ordered()->get();
        $middle = $projects[1];

        $this->get($middle->url())
            ->assertSee('Önceki proje')
            ->assertSee('Sonraki proje')
            ->assertSee($projects[0]->title)
            ->assertSee($projects[2]->title);
    }

    public function test_blog_lists_only_published_posts(): void
    {
        $live = BlogPost::create(['title' => 'Yayındaki Yazı', 'status' => PublishStatus::Published, 'body' => '<p>İçerik</p>']);
        BlogPost::create(['title' => 'Taslak Yazı', 'status' => PublishStatus::Draft]);

        $this->get('/blog')->assertSee('Yayındaki Yazı')->assertDontSee('Taslak Yazı');
        $this->get("/blog/{$live->slug}")->assertOk()->assertSee('İçerik');
    }

    public function test_legal_pages_show_placeholder_instead_of_invented_text(): void
    {
        $this->get('/kvkk')->assertOk()->assertSee('Bu metin hazırlanmaktadır.');
    }
}
