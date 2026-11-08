<?php

namespace Tests\Feature;

use App\Enums\PublicationState;
use App\Enums\PublishStatus;
use App\Models\BlogPost;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_published_and_scheduled_states(): void
    {
        $draft = BlogPost::create(['title' => 'Taslak yazı', 'status' => PublishStatus::Draft]);
        $live = BlogPost::create(['title' => 'Yayındaki yazı', 'status' => PublishStatus::Published]);
        $scheduled = BlogPost::create(['title' => 'Zamanlanmış yazı', 'status' => PublishStatus::Published, 'published_at' => now()->addDay()]);

        $this->assertSame(PublicationState::Draft, $draft->publicationState());
        $this->assertSame(PublicationState::Published, $live->publicationState());
        $this->assertSame(PublicationState::Scheduled, $scheduled->publicationState());

        $this->assertEquals([$live->id], BlogPost::published()->pluck('id')->all());
        $this->assertEquals([$scheduled->id], BlogPost::scheduled()->pluck('id')->all());
    }

    public function test_scheduled_post_becomes_visible_without_cron(): void
    {
        $post = BlogPost::create(['title' => 'Yarın', 'status' => PublishStatus::Published, 'published_at' => now()->addHour()]);

        $this->assertFalse(BlogPost::published()->whereKey($post->id)->exists());

        $this->travel(2)->hours();

        $this->assertTrue(BlogPost::published()->whereKey($post->id)->exists());
    }

    public function test_publishing_without_date_sets_now(): void
    {
        $service = Service::create(['title' => 'Test Hizmet', 'status' => PublishStatus::Published]);

        $this->assertNotNull($service->published_at);
        $this->assertTrue($service->isPubliclyVisible());
    }

    public function test_turkish_slugs_are_generated_and_unique(): void
    {
        $a = Project::create(['title' => 'Çeşme Işıklı Otel', 'status' => PublishStatus::Draft]);
        $b = Project::create(['title' => 'Çeşme Işıklı Otel', 'status' => PublishStatus::Draft]);

        $this->assertSame('cesme-isikli-otel', $a->slug);
        $this->assertSame('cesme-isikli-otel-2', $b->slug);
    }

    public function test_slug_stays_unique_against_soft_deleted_records(): void
    {
        $a = Project::create(['title' => 'Bodrum Villa', 'status' => PublishStatus::Draft]);
        $a->delete();

        $b = Project::create(['title' => 'Bodrum Villa', 'status' => PublishStatus::Draft]);

        $this->assertSame('bodrum-villa-2', $b->slug);
        $this->assertSoftDeleted($a);
    }

    public function test_project_previous_and_next_skip_unpublished(): void
    {
        $first = Project::create(['title' => 'Bir', 'status' => PublishStatus::Published, 'sort_order' => 1]);
        Project::create(['title' => 'Taslak', 'status' => PublishStatus::Draft, 'sort_order' => 2]);
        $third = Project::create(['title' => 'Üç', 'status' => PublishStatus::Published, 'sort_order' => 3]);

        $this->assertTrue($first->next()->is($third));
        $this->assertTrue($third->previous()->is($first));
        $this->assertNull($first->previous());
        $this->assertNull($third->next());
    }
}
