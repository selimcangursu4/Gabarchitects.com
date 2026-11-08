<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Project;
use App\Models\Property;
use App\Models\Service;
use Illuminate\Support\HtmlString;

/**
 * Schema.org JSON-LD üreticileri. Yalnızca veritabanında olan bilgi yazılır;
 * boş alanlar çıktıdan atılır (uydurma değer yok).
 */
final class StructuredData
{
    public static function script(array $data): HtmlString
    {
        $json = json_encode(
            ['@context' => 'https://schema.org'] + self::clean($data),
            // HEX_TAG: içerikte "</script>" olsa bile etiketten çıkılamaz
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR,
        );

        return new HtmlString('<script type="application/ld+json">'.$json.'</script>');
    }

    public static function organizationId(): string
    {
        return url('/').'/#organization';
    }

    public static function organization(): array
    {
        $phone = Phone::international(settings('contact.phone'));
        $address = settings('contact.address');

        return [
            '@type' => 'Organization',
            '@id' => self::organizationId(),
            'name' => settings('site.brand') ?: config('gab.brand'),
            'url' => url('/'),
            'description' => settings('site.description'),
            'email' => settings('contact.email'),
            'telephone' => $phone ? '+'.$phone : null,
            'address' => $address ? [
                '@type' => 'PostalAddress',
                'streetAddress' => preg_replace('/\s*\R\s*/', ', ', trim($address)),
                'addressCountry' => 'TR',
            ] : null,
            'sameAs' => collect(settings('social.links') ?: [])->pluck('url')->filter()->values()->all(),
        ];
    }

    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/').'/#website',
            'url' => url('/'),
            'name' => settings('site.brand') ?: config('gab.brand'),
            'inLanguage' => 'tr-TR',
            'publisher' => ['@id' => self::organizationId()],
        ];
    }

    /**
     * @param  array<int, array{label:string, url?:string|null}>  $crumbs  Anasayfa hariç
     */
    public static function breadcrumbs(array $crumbs): array
    {
        $items = [['name' => 'Anasayfa', 'item' => url('/')]];
        $last = array_key_last($crumbs);

        foreach ($crumbs as $i => $crumb) {
            // Bağlantısı olmayan ara başlıklar ("Kurumsal") listeye girmez; son öğe = bu sayfa
            $href = $i === $last ? url()->current() : ($crumb['url'] ?? null);

            if ($href) {
                $items[] = ['name' => $crumb['label'], 'item' => url($href)];
            }
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['item'],
            ])->all(),
        ];
    }

    public static function article(BlogPost $post): array
    {
        return [
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->meta_description ?: $post->excerpt,
            'image' => self::images([$post->cover]),
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : ['@id' => self::organizationId()],
            'publisher' => ['@id' => self::organizationId()],
            'mainEntityOfPage' => url()->current(),
            'inLanguage' => 'tr-TR',
        ];
    }

    public static function project(Project $project): array
    {
        return [
            '@type' => 'CreativeWork',
            'name' => $project->title,
            'description' => $project->meta_description ?: $project->summary,
            'url' => $project->url(),
            'image' => self::images(collect([$project->cover])->merge($project->gallery)),
            'dateCreated' => $project->year ? (string) $project->year : null,
            'locationCreated' => $project->location ? ['@type' => 'Place', 'name' => $project->location] : null,
            'genre' => $project->category?->name,
            'creator' => ['@id' => self::organizationId()],
        ];
    }

    public static function service(Service $service): array
    {
        return [
            '@type' => 'Service',
            'name' => $service->title,
            'description' => $service->meta_description ?: $service->excerpt,
            'url' => url()->current(),
            'image' => self::images([$service->cover]),
            'provider' => ['@id' => self::organizationId()],
            'areaServed' => ['@type' => 'Country', 'name' => 'Türkiye'],
        ];
    }

    public static function property(Property $property): array
    {
        $price = $property->show_price && $property->price !== null ? (float) $property->price : null;

        return [
            '@type' => 'RealEstateListing',
            'name' => $property->title,
            'description' => $property->meta_description ?: $property->summary,
            'url' => $property->url(),
            'image' => self::images(collect([$property->cover])->merge($property->gallery)),
            'datePosted' => ($property->published_at ?? $property->created_at)?->toAtomString(),
            'offers' => $price !== null ? [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => $property->currency ?: 'TRY',
                // Kat karşılığının GoodRelations karşılığı yok → yazılmaz
                'businessFunction' => match ($property->listing_type->value) {
                    'rent' => 'http://purl.org/goodrelations/v1#LeaseOut',
                    'sale' => 'http://purl.org/goodrelations/v1#Sell',
                    default => null,
                },
                'availableAtOrFrom' => $property->locationLabel()
                    ? ['@type' => 'Place', 'name' => $property->locationLabel()]
                    : null,
                'offeredBy' => ['@id' => self::organizationId()],
            ] : null,
        ];
    }

    /** @param  iterable<Media|null>  $media */
    private static function images(iterable $media): array
    {
        return collect($media)->filter()->unique('id')
            ->map(fn (Media $m) => $m->shareImage()['url'] ?? null)
            ->filter()->map(fn (string $u) => url($u))->values()->all();
    }

    /** null / '' / [] değerleri (iç içe) atar */
    private static function clean(array $data): array
    {
        $isList = array_is_list($data);   // itemListElement, image, sameAs dizi olarak kalmalı

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::clean($value);
                $data[$key] = $value;
            }

            if ($value === null || $value === '' || $value === []) {
                unset($data[$key]);
            }
        }

        return $isList ? array_values($data) : $data;
    }
}
