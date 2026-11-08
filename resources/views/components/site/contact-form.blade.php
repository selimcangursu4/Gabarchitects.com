@props([
    'source' => 'contact_page',    // home_cta | contact_page | property
    'propertyId' => null,
    'subject' => null,             // önceden doldurulmuş konu (ör. ilan başlığı)
    'stacked' => false,            // dar alanlarda (ilan yan paneli) tek kolon
])

@php
    $field = 'peer block w-full border-0 border-b border-line bg-transparent px-0 pb-3 pt-6 text-[1rem] text-fg outline-none transition-colors placeholder:text-transparent focus:border-fg';
    $float = 'pointer-events-none absolute left-0 top-6 origin-left text-[1rem] text-muted transition-all duration-200 peer-focus:top-0 peer-focus:text-[0.75rem] peer-focus:tracking-[0.08em] peer-[:not(:placeholder-shown)]:top-0 peer-[:not(:placeholder-shown)]:text-[0.75rem]';
    $isThisForm = old('source') === $source;
@endphp

{{-- Talep 9: gönderimler panelde "Web Formları" altında listelenir (contact_messages) --}}
<form method="POST" action="{{ route('contact.store') }}" id="iletisim-formu" novalidate {{ $attributes->merge(['class' => 'scroll-mt-32']) }}>
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
    <input type="hidden" name="_t" value="{{ encrypt(now()->timestamp) }}">
    @if ($propertyId)
        <input type="hidden" name="property_id" value="{{ $propertyId }}">
    @endif

    {{-- Honeypot: ekran okuyuculardan ve görsel olarak gizli --}}
    <div class="absolute -left-[9999px]" aria-hidden="true">
        <label for="website-{{ $source }}">Web siteniz</label>
        <input type="text" id="website-{{ $source }}" name="website" tabindex="-1" autocomplete="off">
    </div>

    {{-- Başarı mesajı yalnızca gönderimin yapıldığı formda (session: kaynak) --}}
    @if (session('contact_success') === $source)
        <div role="status" class="mb-8 border border-accent/60 px-5 py-4 text-[0.9375rem]">
            Mesajınız bize ulaştı. En kısa sürede sizinle iletişime geçeceğiz.
        </div>
    @endif

    @if ($isThisForm && $errors->any())
        <div role="alert" class="mb-8 border border-[#b4553f]/60 px-5 py-4 text-[0.9375rem]">
            <p class="font-medium">Lütfen formu kontrol edin.</p>
            @if ($errors->has('_t') || $errors->has('website'))
                <p class="mt-1 text-muted">{{ $errors->first('_t') ?: $errors->first('website') }}</p>
            @endif
        </div>
    @endif

    <div @class(['grid gap-x-8 gap-y-7', 'sm:grid-cols-2' => ! $stacked])>
        @foreach ([
            ['name', 'Ad Soyad', 'text', 'name', true],
            ['email', 'E-posta', 'email', 'email', true],
            ['phone', 'Telefon', 'tel', 'tel', false],
            ['subject', 'Konu', 'text', 'off', false],
        ] as [$name, $text, $type, $autocomplete, $required])
            <div class="relative">
                <input type="{{ $type }}" id="{{ $name }}-{{ $source }}" name="{{ $name }}"
                       value="{{ $isThisForm ? old($name) : ($name === 'subject' ? $subject : '') }}"
                       placeholder="{{ $text }}" autocomplete="{{ $autocomplete }}"
                       @if ($required) required aria-required="true" @endif
                       @if ($isThisForm && $errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-{{ $source }}-error" @endif
                       class="{{ $field }}">
                <label for="{{ $name }}-{{ $source }}" class="{{ $float }}">{{ $text }}@if ($required) <span aria-hidden="true">*</span>@endif</label>
                @if ($isThisForm && $errors->has($name))
                    <p id="{{ $name }}-{{ $source }}-error" class="mt-2 text-[0.8125rem] text-[#c4644d]">{{ $errors->first($name) }}</p>
                @endif
            </div>
        @endforeach

        <div @class(['relative', 'sm:col-span-2' => ! $stacked])>
            <textarea id="message-{{ $source }}" name="message" rows="4" placeholder="Mesajınız" required aria-required="true"
                      @if ($isThisForm && $errors->has('message')) aria-invalid="true" aria-describedby="message-{{ $source }}-error" @endif
                      class="{{ $field }} resize-y">{{ $isThisForm ? old('message') : '' }}</textarea>
            <label for="message-{{ $source }}" class="{{ $float }}">Mesajınız <span aria-hidden="true">*</span></label>
            @if ($isThisForm && $errors->has('message'))
                <p id="message-{{ $source }}-error" class="mt-2 text-[0.8125rem] text-[#c4644d]">{{ $errors->first('message') }}</p>
            @endif
        </div>
    </div>

    <div @class(['mt-8 flex flex-col gap-6', 'sm:flex-row sm:items-center sm:justify-between' => ! $stacked])>
        <div>
            <label class="flex max-w-md cursor-pointer items-start gap-3 text-[0.875rem] leading-relaxed text-muted">
                <input type="checkbox" name="privacy_consent" value="1" required @checked($isThisForm && old('privacy_consent'))
                       class="mt-1 size-4 shrink-0 accent-current">
                <span>
                    <a href="{{ route('legal.kvkk') }}" target="_blank" class="text-fg underline decoration-line underline-offset-4 hover:decoration-accent">KVKK Aydınlatma Metni</a>'ni okudum; kişisel verilerimin talebimin değerlendirilmesi ve benimle iletişime geçilmesi amacıyla işlenmesini kabul ediyorum.
                </span>
            </label>
            @if ($isThisForm && $errors->has('privacy_consent'))
                <p class="mt-2 text-[0.8125rem] text-[#c4644d]">{{ $errors->first('privacy_consent') }}</p>
            @endif
        </div>

        <button type="submit" class="group inline-flex h-13 shrink-0 items-center justify-center gap-3 bg-fg px-7 text-[0.8125rem] font-medium uppercase tracking-[0.14em] text-bg transition-colors hover:bg-accent hover:text-[#111111]">
            Gönder
            <svg class="size-4 transition-transform duration-500 group-hover:translate-x-1" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path d="M1 8h13M9.5 3.5 14 8l-4.5 4.5" /></svg>
        </button>
    </div>
</form>
