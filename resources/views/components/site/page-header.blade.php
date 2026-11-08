@props([
    'title',
    'eyebrow' => null,
    'intro' => null,
    'breadcrumbs' => [],   // [['label' => 'Projeler', 'url' => '/projeler'], ['label' => 'Sapanca Villa']]
])

{{-- İç sayfa başlığı: breadcrumb + büyük serif başlık + giriş --}}
<section {{ $attributes->merge(['class' => 'plan-lines border-b border-line pt-36 pb-16 lg:pt-44 lg:pb-24']) }}>
    <div class="container-gab relative">
        <x-site.breadcrumbs :items="$breadcrumbs" />

        <div class="grid-gab mt-12 gap-y-8 lg:mt-16">
            <div class="col-span-4 lg:col-span-8">
                @if ($eyebrow)
                    <p class="eyebrow mb-6">{{ $eyebrow }}</p>
                @endif
                <h1 class="text-h1">{{ $title }}</h1>
            </div>
            @if ($intro)
                <p class="col-span-4 text-lead text-muted lg:col-span-4 lg:self-end">{{ $intro }}</p>
            @endif
        </div>

        {{ $slot }}
    </div>
</section>
