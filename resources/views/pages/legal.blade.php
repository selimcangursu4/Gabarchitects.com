@php
    $hasText = $page->body && $page->status->value === 'published';
@endphp
{{-- Metni girilmemiş ("hazırlanıyor") sayfa dizine alınmaz --}}
<x-layouts.site :title="$page->title" :noindex="! $hasText">
    <x-site.page-header :title="$page->title" eyebrow="Yasal" :breadcrumbs="[['label' => $page->title]]" />

    <section class="section-y">
        <div class="container-gab">
            @if ($hasText)
                <div class="prose-gab">{!! $page->body !!}</div>
                <p class="mt-12 text-[0.875rem] text-subtle">Son güncelleme: {{ $page->updated_at->translatedFormat('d F Y') }}</p>
            @else
                {{-- Hukuki metin uydurulmaz; müşterinin onaylı metni yönetim panelinden girilecek --}}
                <p class="max-w-2xl text-lead text-muted">Bu metin hazırlanmaktadır. Sorularınız için bizimle <a href="{{ route('contact') }}" class="text-fg underline decoration-line underline-offset-4">iletişime geçebilirsiniz</a>.</p>
            @endif
        </div>
    </section>
</x-layouts.site>
