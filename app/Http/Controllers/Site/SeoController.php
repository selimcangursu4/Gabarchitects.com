<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Property;
use App\Models\Service;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** /sitemap.xml ve /robots.txt (brief §9) */
class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $realEstate = (bool) settings('modules.real_estate', false);

        // Hukuki metni henüz girilmemiş sayfa ("hazırlanıyor") haritaya girmez
        $legal = Page::query()->whereIn('key', ['kvkk', 'privacy', 'cookies'])
            ->where('status', 'published')->whereNotNull('body')->where('body', '!=', '')
            ->get(['key', 'updated_at'])->keyBy('key');

        $urls = collect([
            $this->entry(route('home'), Page::where('key', 'home')->value('updated_at'), 1.0),
            $this->entry(route('about'), Page::where('key', 'about')->value('updated_at'), 0.6),
            $this->entry(route('references'), null, 0.5),
            $this->entry(route('services.index'), Service::published()->max('updated_at'), 0.8),
            $this->entry(route('projects.index'), Project::published()->max('updated_at'), 0.8),
            $this->entry(route('blog.index'), BlogPost::published()->max('updated_at'), 0.6),
            $this->entry(route('contact'), Page::where('key', 'contact')->value('updated_at'), 0.5),
        ])
            ->when($realEstate, fn (Collection $c) => $c->push(
                $this->entry(route('properties.index'), $this->latestProperty(), 0.9),
                $this->entry(route('properties.sale'), $this->latestProperty('sale'), 0.9),
                $this->entry(route('properties.rent'), $this->latestProperty('rent'), 0.9),
                $this->entry(route('properties.exchange'), $this->latestProperty('exchange'), 0.9),
            ))
            ->merge(Service::published()->orderBy('sort_order')->get(['slug', 'updated_at'])
                ->map(fn (Service $s) => $this->entry(route('services.show', $s), $s->updated_at, 0.7)))
            ->merge(ProjectCategory::active()->whereHas('projects', fn ($q) => $q->published())->get(['id', 'slug', 'updated_at'])
                ->map(fn (ProjectCategory $c) => $this->entry(route('projects.category', $c), $c->updated_at, 0.5)))
            ->merge(Project::published()->ordered()->get(['id', 'slug', 'updated_at'])
                ->map(fn (Project $p) => $this->entry($p->url(), $p->updated_at, 0.7)))
            ->merge($realEstate
                // ÖRNEK ilanlar (is_sample) hiçbir zaman haritaya girmez
                ? Property::published()->where('is_sample', false)->latestFirst()->get(['id', 'slug', 'updated_at'])
                    ->map(fn (Property $p) => $this->entry($p->url(), $p->updated_at, 0.8))
                : [])
            ->merge(BlogCategory::whereHas('posts', fn ($q) => $q->published())->get(['id', 'slug', 'updated_at'])
                ->map(fn (BlogCategory $c) => $this->entry(route('blog.category', $c), $c->updated_at, 0.4)))
            ->merge(BlogPost::published()->orderByDesc('published_at')->get(['id', 'slug', 'updated_at'])
                ->map(fn (BlogPost $p) => $this->entry(route('blog.show', $p), $p->updated_at, 0.6)))
            ->merge(collect(['kvkk' => 'legal.kvkk', 'privacy' => 'legal.privacy', 'cookies' => 'legal.cookies'])
                ->filter(fn (string $route, string $key) => $legal->has($key))
                ->map(fn (string $route, string $key) => $this->entry(route($route), $legal[$key]->updated_at, 0.2))
                ->values())
            ->values();

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = config('seo.indexable')
            ? [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /livewire',
                '',
                'Sitemap: '.route('sitemap'),
            ]
            // Lokal / test / staging: arama motorlarına tamamen kapalı
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function latestProperty(?string $listingType = null): mixed
    {
        return Property::published()->where('is_sample', false)
            ->when($listingType, fn ($q) => $q->where('listing_type', $listingType))
            ->max('updated_at');
    }

    /** @return array{loc:string, lastmod:?string, priority:string} */
    private function entry(string $loc, CarbonInterface|string|null $lastmod, float $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod ? Carbon::parse($lastmod)->toAtomString() : null,
            'priority' => number_format($priority, 1, '.', ''),
        ];
    }
}
