<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Media;
use App\Models\Property;
use App\Services\MediaService;
use Illuminate\Database\Seeder;

/**
 * YALNIZCA LOKAL TASARIM İÇİN ÖRNEK İLANLAR.
 *
 * Mevcut sitede gerçek ilan yok. Kart ve filtre tasarımının görülebilmesi için,
 * başlığı "ÖRNEK" ile başlayan, is_sample=true işaretli ilanlar üretir.
 * Görseller üzerinde "ÖRNEK GÖRSEL" yazan nötr, üretilmiş görsellerdir (gerçek proje görseli kullanılmaz).
 *
 * Production'da çalışmaz. Silmek için: php artisan gab:purge-samples
 */
class SamplePropertySeeder extends Seeder
{
    /** [işlem, tip, il, ilçe, oda, m², fiyat, öne çıkan] */
    private const LISTINGS = [
        ['sale', 'villa', 'İstanbul', 'Sarıyer', '5+2', 420, 48_500_000, true],
        ['sale', 'daire', 'İstanbul', 'Beşiktaş', '3+1', 165, 21_750_000, true],
        ['rent', 'daire', 'İstanbul', 'Beşiktaş', '2+1', 110, 85_000, true],
        ['sale', 'arsa', 'Sakarya', 'Sapanca', null, 1850, 14_200_000, true],
        ['sale', 'villa', 'Muğla', 'Bodrum', '4+1', 310, 62_000_000, true],
        ['rent', 'ofis', 'İstanbul', 'Şişli', null, 240, 180_000, true],
        ['sale', 'rezidans', 'İstanbul', 'Kadıköy', '1+1', 72, 9_400_000, true],
        ['rent', 'villa', 'İzmir', 'Çeşme', '4+1', 280, 250_000, true],
        ['sale', 'dukkan', 'İstanbul', 'Beşiktaş', null, 95, 16_900_000, true],
        ['sale', 'mustakil-ev', 'Sakarya', 'Sapanca', '3+1', 190, 12_300_000, true],
        ['rent', 'rezidans', 'İstanbul', 'Sarıyer', '3+1', 150, 140_000, false],
        ['sale', 'arsa', 'Muğla', 'Bodrum', null, 3200, 38_000_000, false],
    ];

    public function run(MediaService $media): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('SamplePropertySeeder production ortamında çalıştırılmaz.');

            return;
        }

        $types = collect(config('real_estate.property_types'))->collapse();

        foreach (self::LISTINGS as $i => [$listing, $type, $city, $district, $rooms, $area, $price, $featured]) {
            $title = sprintf('ÖRNEK — %s %s %s', $district, $types[$type], $listing === 'sale' ? 'Satılık' : 'Kiralık');

            $property = Property::updateOrCreate(['slug' => 'ornek-ilan-'.($i + 1)], [
                'title' => $title,
                'listing_type' => $listing,
                'property_type' => $type,
                'city' => $city,
                'district' => $district,
                'rooms' => $rooms,
                'area_m2' => $area,
                'price' => $price,
                'currency' => 'TRY',
                'show_price' => true,
                'summary' => 'Bu bir örnek ilandır; tasarım ve filtre testleri için oluşturulmuştur. Gerçek bir portföyü temsil etmez.',
                'cover_media_id' => $this->placeholder($media, $i)->id,
                'status' => PublishStatus::Published,
                'is_featured' => $featured,
                'is_sample' => true,
            ]);

            // Farklı eklenme zamanları: "son eklenen" sıralaması görülebilsin (created_at fillable değil)
            $property->forceFill(['created_at' => now()->subDays(count(self::LISTINGS) - $i)])->saveQuietly();
        }

        $this->command?->info(count(self::LISTINGS).' örnek ilan oluşturuldu (is_sample=true).');
    }

    /** Nötr, üzerinde "ÖRNEK GÖRSEL" yazan üretilmiş görsel. */
    private function placeholder(MediaService $media, int $i): Media
    {
        $name = 'ornek-ilan-'.($i + 1).'.jpg';

        if ($existing = Media::where('original_name', $name)->first()) {
            return $existing;
        }

        $w = 1600;
        $h = 1200;
        $img = imagecreatetruecolor($w, $h);
        $tones = [[54, 52, 48], [72, 68, 61], [43, 45, 47], [88, 80, 70]];
        [$r, $g, $b] = $tones[$i % count($tones)];
        imagefill($img, 0, 0, imagecolorallocate($img, $r, $g, $b));

        // İnce mimari çizgiler (pafta hissi)
        $line = imagecolorallocatealpha($img, 245, 243, 239, 110);
        for ($x = 0; $x <= $w; $x += 200) {
            imageline($img, $x, 0, $x, $h, $line);
        }
        imageline($img, 0, (int) ($h * 0.62), $w, (int) ($h * 0.62), $line);

        $text = imagecolorallocate($img, 245, 243, 239);
        imagestring($img, 5, 60, 60, 'ORNEK GORSEL', $text);
        imagestring($img, 3, 60, 90, 'GAB Architects - tasarim testi', $text);

        $path = tempnam(sys_get_temp_dir(), 'gab').'.jpg';
        imagejpeg($img, $path, 85);
        imagedestroy($img);

        return $media->importFile($path, $name, ['alt' => 'Örnek görsel']);
    }
}
