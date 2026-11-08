<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('pages.services.index', [
            'services' => Service::published()->orderBy('sort_order')->with('cover')->get(),
            'intro' => Page::findByKey('home')?->field('services_intro'),
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->isPubliclyVisible(), 404);

        $service->load(['cover', 'gallery', 'projects' => fn ($q) => $q->published()->ordered()->with(['cover', 'category'])->limit(6)]);

        return view('pages.services.show', [
            'service' => $service,
            'others' => Service::published()->whereKeyNot($service->id)->orderBy('sort_order')->get(['id', 'title', 'slug']),
        ]);
    }
}
