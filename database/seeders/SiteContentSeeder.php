<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Redirect;
use App\Models\Reference;
use App\Models\Service;
use App\Services\MediaService;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

/**
 * Mevcut gabarchitects.com içeriğini (database/data/site-content.php) veritabanına aktarır.
 * Tekrar çalıştırılabilir: kayıtlar slug/key ile eşleştirilir, medya iki kez içe aktarılmaz.
 * Sonrasında içerik CMS'ten yönetilir.
 */
class SiteContentSeeder extends Seeder
{
    private array $data;

    /** @var array<string, int|null> görsel anahtarı => media.id */
    private array $media = [];

    public function run(MediaService $mediaService, SettingService $settings): void
    {
        $this->data = require database_path('data/site-content.php');

        $this->importMedia($mediaService);
        $this->seedSettings($settings);
        $this->seedServices();
        $this->seedProjects();
        $this->seedReferences();
        $this->seedFaqs();
        $this->seedPages();
        $this->seedMenus();
        $this->seedRedirects();
    }

    private function importMedia(MediaService $service): void
    {
        $dir = database_path('seeders/assets/legacy');

        if (! config('gab.seed_media')) {
            $this->media = array_fill_keys(array_keys($this->data['image_sources']), null);

            return;
        }

        foreach (array_keys($this->data['image_sources']) as $key) {
            $file = collect(['jpg', 'png'])->map(fn ($ext) => "{$dir}/{$key}.{$ext}")->first(fn ($p) => is_file($p));

            if (! $file) {
                $this->media[$key] = null;
                $this->command?->warn("Görsel yok, atlandı: {$key}");

                continue;
            }

            $existing = Media::where('original_name', basename($file))->first();

            $this->media[$key] = ($existing ?? $service->importFile($file, basename($file)))->id;
        }
    }

    private function seedSettings(SettingService $settings): void
    {
        $s = $this->data['settings'];

        $settings->set('site.brand', $s['brand'], 'general');
        $settings->set('site.tagline', $s['tagline'], 'general');
        $settings->set('site.description', 'İstanbul merkezli mimarlık, iç mimarlık, gayrimenkul proje geliştirme ve anahtar teslim inşaat hizmetleri.', 'seo');
        $settings->set('contact.address', $s['address'], 'contact');
        $settings->set('contact.address_short', $s['address_short'], 'contact');
        $settings->set('contact.phone', $s['phone'], 'contact');
        $settings->set('contact.email', $s['email'], 'contact');
        $settings->set('contact.map_url', $s['map_url'], 'contact');
        $settings->set('social.links', $s['social'], 'social');
        $settings->set('contact.whatsapp', null, 'contact');             // ilan sayfası WhatsApp butonu (müşteriden bekleniyor)
        $settings->set('contact.notification_email', null, 'contact');   // web formu bildirimleri (boşsa MAIL_FROM_ADDRESS)
        $settings->set('modules.real_estate', true, 'modules');          // talep: hero filtresi + portföy (2026-11-08)
        $settings->set('legal.cookie_banner_text', null, 'legal');   // müşteri/hukuk onaylı metin bekleniyor
    }

    private function seedServices(): void
    {
        foreach ($this->data['services'] as $order => $s) {
            Service::updateOrCreate(['slug' => $s['slug']], [
                'title' => $s['title'],
                'excerpt' => $s['excerpt'],
                'cover_media_id' => $this->media[$s['image']] ?? null,
                // Açıklaması olmayan hizmet taslak kalır (müşteri metni bekleniyor).
                'status' => $s['excerpt'] ? PublishStatus::Published : PublishStatus::Draft,
                'sort_order' => $order,
            ]);
        }
    }

    private function seedProjects(): void
    {
        $categories = [];
        foreach (array_keys($this->data['project_categories']) as $order => $slug) {
            $categories[$slug] = ProjectCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $this->data['project_categories'][$slug], 'sort_order' => $order],
            )->id;
        }

        $featured = $this->data['home']['featured_projects'];

        foreach ($this->data['projects'] as $order => $p) {
            Project::updateOrCreate(['slug' => $p['slug']], [
                'project_category_id' => $p['category'] ? $categories[$p['category']] : null,
                'title' => $p['title'],
                'subtitle' => $p['subtitle'],
                'location' => $p['location'],
                'cover_media_id' => $this->media[$p['image']] ?? null,
                'status' => PublishStatus::Published,
                'is_featured' => in_array($p['slug'], $featured, true),
                // Öne çıkanlar ana sayfadaki sırayla en başta
                'sort_order' => in_array($p['slug'], $featured, true) ? array_search($p['slug'], $featured, true) : 10 + $order,
            ]);
        }
    }

    private function seedReferences(): void
    {
        foreach ($this->data['references'] as $order => $r) {
            Reference::updateOrCreate(['name' => $r['name']], [
                'service' => $r['service'],
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => $order,
            ]);
        }
    }

    private function seedFaqs(): void
    {
        foreach ($this->data['faqs'] as $order => $f) {
            Faq::updateOrCreate(['question' => $f['q']], [
                'group' => 'general',
                'answer' => $f['a'],
                'sort_order' => $order,
            ]);
        }
    }

    private function seedPages(): void
    {
        $home = $this->data['home'];
        $approachProject = Project::where('slug', $home['approach']['project'])->value('id');

        Page::updateOrCreate(['key' => 'home'], [
            'title' => 'Ana Sayfa',
            'slug' => null,
            'cover_media_id' => $this->media[$home['hero']['image']] ?? null,
            'status' => PublishStatus::Published,
            'content' => [
                'hero' => array_diff_key($home['hero'], ['image' => true]),
                'statement' => $home['statement'],
                'intro' => $home['intro'],
                'services_intro' => $home['services_intro'],
                'approach' => [
                    'title' => $home['approach']['title'],
                    'text' => $home['approach']['text'],
                    'text_secondary' => $home['approach']['text_secondary'],
                    'project_id' => $approachProject,
                ],
                'process' => $home['process'],
                'cta' => $home['cta'],
            ],
            'meta_title' => null,
            'meta_description' => 'GAB Architects — İstanbul merkezli mimarlık, iç mimarlık, gayrimenkul proje geliştirme ve anahtar teslim inşaat hizmetleri.',
        ]);

        $about = $this->data['about'];
        Page::updateOrCreate(['key' => 'about'], [
            'title' => 'Hakkımızda',
            'slug' => 'hakkimizda',
            'status' => PublishStatus::Published,
            'content' => $about,
        ]);

        Page::updateOrCreate(['key' => 'contact'], [
            'title' => 'İletişim',
            'slug' => 'iletisim',
            'status' => PublishStatus::Published,
            'content' => ['intro' => 'İhtiyaçlarınızı değerlendirerek size özel çözüm ve teklif hazırlayalım.'],
        ]);

        // Hukuki metinler uydurulmaz: içerik boş ve taslak. Müşterinin hukuk danışmanı dolduracak.
        foreach ([
            'kvkk' => ['KVKK Aydınlatma Metni', 'kvkk'],
            'privacy' => ['Gizlilik Politikası', 'gizlilik-politikasi'],
            'cookies' => ['Çerez Politikası', 'cerez-politikasi'],
        ] as $key => [$title, $slug]) {
            Page::firstOrCreate(['key' => $key], [
                'title' => $title,
                'slug' => $slug,
                'status' => PublishStatus::Draft,
                'body' => null,
            ]);
        }
    }

    private function seedMenus(): void
    {
        // [etiket, url (null = yalnızca açılır menü başlığı), alt öğeler]
        $menus = [
            'header' => ['Ana menü', [
                ['Anasayfa', '/', []],
                ['Kurumsal', null, [['Hakkımızda', '/hakkimizda'], ['Referanslar', '/referanslar']]],
                ['Portföyümüz', null, [['Satılık', '/satilik'], ['Kiralık', '/kiralik']]],
                ['Projeler', '/projeler', []],
                ['Hizmetler', '/hizmetler', []],
                ['Blog', '/blog', []],
                ['İletişim', '/iletisim', []],
            ]],
            'legal' => ['Yasal', [
                ['KVKK Aydınlatma Metni', '/kvkk', []],
                ['Gizlilik Politikası', '/gizlilik-politikasi', []],
                ['Çerez Politikası', '/cerez-politikasi', []],
            ]],
        ];

        foreach ($menus as $key => [$name, $items]) {
            $menu = Menu::updateOrCreate(['key' => $key], ['name' => $name]);

            foreach ($items as $order => [$label, $url, $children]) {
                $parent = $menu->items()->updateOrCreate(['label' => $label, 'parent_id' => null], [
                    'url' => $url,
                    'sort_order' => $order,
                ]);

                foreach ($children as $childOrder => [$childLabel, $childUrl]) {
                    $menu->items()->updateOrCreate(['label' => $childLabel, 'parent_id' => $parent->id], [
                        'url' => $childUrl,
                        'sort_order' => $childOrder,
                    ]);
                }
            }
        }
    }

    private function seedRedirects(): void
    {
        // Eski WordPress adresleri (PAGE_STRUCTURE.md)
        foreach (['/about' => '/hakkimizda', '/contact-2' => '/iletisim'] as $from => $to) {
            Redirect::updateOrCreate(['from_path' => $from], ['to_url' => $to, 'status_code' => 301]);
        }
    }
}
