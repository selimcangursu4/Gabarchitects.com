<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Project;
use App\Models\Property;
use App\Models\Reference;
use App\Models\Service;
use App\Services\PropertySearch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PropertySearch $search): View
    {
        $page = Page::with('cover')->where('key', 'home')->firstOrFail();
        $realEstate = (bool) settings('modules.real_estate', false);

        return view('pages.home', [
            'page' => $page,
            'realEstate' => $realEstate,
            'locations' => $realEstate ? $search->locationOptions() : null,
            // Talep 2: öne çıkan olarak işaretlenmiş son 9 ilan
            'featuredProperties' => $realEstate
                ? Property::published()->featured()->latestFirst()->with('cover')->limit(config('real_estate.home_featured_count'))->get()
                : collect(),
            // Talep 6: öne çıkan olarak işaretlenmiş son 4 proje
            'featured' => Project::published()->featured()->latestFirst()->with(['cover', 'category'])->limit(4)->get(),
            'approachProject' => Project::published()->with('cover')->find($page->field('approach.project_id')),
            // "Hakkımızda" bölümündeki sayılar: yalnızca yayındaki kayıtlar (taslak / zamanlanmış / silinmiş sayılmaz)
            'projectCount' => Project::published()->count(),
            'serviceCount' => Service::published()->count(),
            'services' => Service::published()->orderBy('sort_order')->with('cover')->get(),
            // Talep 8: ana sayfada öne çıkan son 16 referans
            'references' => Reference::query()->where('is_active', true)->where('is_featured', true)
                ->latest('created_at')->latest('id')->limit(16)->get(),
        ]);
    }
}
