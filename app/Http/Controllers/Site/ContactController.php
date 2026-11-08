<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Page;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact', [
            'page' => Page::where('key', 'contact')->firstOrFail(),
        ]);
    }

    public function store(ContactRequest $request, ContactService $contacts): RedirectResponse
    {
        $contacts->submit($request->validated(), $request);

        // Flash değeri = formun kaynağı → başarı mesajı yalnızca o formda gösterilir
        return redirect()->to(strtok(url()->previous(), '#').'#iletisim-formu')
            ->with('contact_success', $request->validated('source'));
    }
}
