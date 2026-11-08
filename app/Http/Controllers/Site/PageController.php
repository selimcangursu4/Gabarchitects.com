<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Reference;
use App\Models\TeamMember;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'page' => Page::with('cover')->where('key', 'about')->firstOrFail(),
            'team' => TeamMember::active()->with('photo')->get(),
            'faqs' => Faq::active()->get(),
        ]);
    }

    /** Talep 8: referanslar sayfasında aktif olanların hepsi */
    public function references(): View
    {
        return view('pages.references', [
            'references' => Reference::active()->with(['logo', 'project'])->get(),
        ]);
    }

    /**
     * Hukuki sayfalar. Metin henüz girilmediyse "hazırlanıyor" notu gösterilir
     * (KVKK onay kutusu bu sayfaya bağlantı verdiği için 404 dönmez; metin uydurulmaz).
     */
    public function legal(string $key): View
    {
        return view('pages.legal', [
            'page' => Page::where('key', $key)->firstOrFail(),
        ]);
    }
}
