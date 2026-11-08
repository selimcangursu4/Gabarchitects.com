@use('App\Support\StructuredData')
<x-layouts.site :title="$page->meta_title ?: 'İletişim'" :description="$page->meta_description ?: 'GAB Architects iletişim bilgileri ve iletişim formu.'"
    :schema="[StructuredData::organization()]">
    <x-site.page-header
        title="İletişim"
        eyebrow="Bir proje konuşalım"
        :intro="$page->field('intro')"
        :breadcrumbs="[['label' => 'İletişim']]" />

    <section class="section-y">
        <div class="container-gab">
            <div class="grid-gab gap-y-16">
                <div class="col-span-4 lg:col-span-4">
                    <dl class="space-y-10">
                        <div>
                            <dt class="eyebrow">Adres</dt>
                            <dd class="mt-3 whitespace-pre-line text-lead">{{ settings('contact.address') }}</dd>
                            @if (settings('contact.map_url'))
                                <dd class="mt-4"><a href="{{ settings('contact.map_url') }}" target="_blank" rel="noopener" class="text-[0.875rem] underline decoration-line underline-offset-4 hover:decoration-accent">Google Haritalar'da aç ↗</a></dd>
                            @endif
                        </div>
                        @if (settings('contact.phone'))
                            <div>
                                <dt class="eyebrow">Telefon</dt>
                                <dd class="mt-3 text-lead"><a href="tel:{{ preg_replace('/[^\d+]/', '', settings('contact.phone')) }}" class="hover:text-accent">{{ settings('contact.phone') }}</a></dd>
                            </div>
                        @endif
                        @if (settings('contact.email'))
                            <div>
                                <dt class="eyebrow">E-posta</dt>
                                <dd class="mt-3 text-lead"><a href="mailto:{{ settings('contact.email') }}" class="hover:text-accent">{{ settings('contact.email') }}</a></dd>
                            </div>
                        @endif
                        @if (settings('social.links'))
                            <div>
                                <dt class="eyebrow">Sosyal medya</dt>
                                <dd class="mt-3 flex gap-6">
                                    @foreach (settings('social.links') as $social)
                                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="text-lead hover:text-accent">{{ $social['label'] }}</a>
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="col-span-4 lg:col-span-7 lg:col-start-6">
                    <h2 class="font-serif text-h2">Mesaj gönderin</h2>
                    <x-site.contact-form source="contact_page" class="mt-8" />
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
