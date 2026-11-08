<x-layouts.site title="Sayfa bulunamadı">
    <section class="plan-lines flex min-h-[80svh] items-center pt-32 pb-20">
        <div class="container-gab relative">
            <div class="grid-gab gap-y-10">
                <p class="col-span-4 font-serif text-[clamp(7rem,22vw,20rem)] leading-[0.8] text-accent lg:col-span-6">404</p>
                <div class="col-span-4 lg:col-span-5 lg:col-start-8 lg:self-end">
                    <x-site.section-label number="0" label="Hata" />
                    <h1 class="mt-8 text-h2">Aradığınız sayfa bulunamadı.</h1>
                    <p class="mt-6 text-muted">Sayfa taşınmış, kaldırılmış ya da henüz yayına alınmamış olabilir.</p>
                    <div class="mt-10 flex flex-wrap gap-4">
                        <x-site.button href="{{ route('home') }}">Ana sayfa</x-site.button>
                        <x-site.button href="{{ url('/iletisim') }}" variant="outline">İletişim</x-site.button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
