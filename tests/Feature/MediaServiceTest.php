<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Project;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        Storage::fake('local');
    }

    private function makeJpeg(int $w, int $h): string
    {
        $path = tempnam(sys_get_temp_dir(), 'gab').'.jpg';
        $img = imagecreatetruecolor($w, $h);
        imagefill($img, 0, 0, imagecolorallocate($img, 120, 110, 90));
        imagejpeg($img, $path, 85);
        imagedestroy($img);

        return $path;
    }

    public function test_import_keeps_original_and_creates_webp_variants_without_upscaling(): void
    {
        $media = app(MediaService::class)->importFile($this->makeJpeg(1200, 800), 'Sapanca Villa.jpg');

        $this->assertSame(1200, $media->width);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertSame([480, 960, 1200], $media->widths('webp'));   // 1600/2400 üretilmez (büyütme yok)
        $this->assertFalse($media->hasFormat('avif'));                  // testlerde MEDIA_AVIF=false

        Storage::disk('local')->assertExists($media->original_path);
        foreach ([480, 960, 1200] as $w) {
            Storage::disk('media')->assertExists("{$media->uuid}/{$w}.webp");
        }

        // Dosya adı kullanıcıdan alınmaz
        $this->assertStringNotContainsString('Sapanca', $media->original_path);
    }

    public function test_rejects_non_image_files(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'gab');
        file_put_contents($path, '<?php echo "zararlı"; ?>');

        $this->expectException(InvalidArgumentException::class);
        app(MediaService::class)->importFile($path, 'resim.jpg');
    }

    public function test_rejects_disallowed_image_types(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'gab').'.gif';
        $img = imagecreatetruecolor(10, 10);
        imagegif($img, $path);

        $this->expectException(InvalidArgumentException::class);
        app(MediaService::class)->importFile($path, 'anim.gif');
    }

    public function test_gallery_order_and_usage_count(): void
    {
        $service = app(MediaService::class);
        $a = $service->importFile($this->makeJpeg(500, 400));
        $b = $service->importFile($this->makeJpeg(500, 400));

        $project = Project::create(['title' => 'Galeri Testi', 'status' => 'draft', 'cover_media_id' => $a->id]);
        $project->syncGallery([$b->id, $a->id]);

        $this->assertEquals([$b->id, $a->id], $project->gallery()->pluck('media.id')->all());
        $this->assertSame(2, $a->usageCount());   // kapak + galeri
        $this->assertSame(1, $b->usageCount());
    }

    public function test_purge_removes_all_files(): void
    {
        $media = app(MediaService::class)->importFile($this->makeJpeg(600, 400));

        app(MediaService::class)->purge($media);

        Storage::disk('local')->assertMissing($media->original_path);
        Storage::disk('media')->assertMissing("{$media->uuid}/480.webp");
        $this->assertNull(Media::withTrashed()->find($media->id));
    }
}
