{{-- text/plain: HTML kaçışı yok (XSS riski yok, e-posta istemcisi metni ham gösterir) --}}
Yeni bir web formu gönderildi.

Ad Soyad : {!! $contactMessage->name !!}
E-posta  : {!! $contactMessage->email !!}
Telefon  : {!! $contactMessage->phone ?: '-' !!}
Konu     : {!! $contactMessage->subject ?: '-' !!}
Kaynak   : {!! $contactMessage->source !!}@if ($contactMessage->property) — İlan: {!! $contactMessage->property->title !!}@endif

Sayfa    : {!! $contactMessage->source_url ?: '-' !!}
Tarih    : {!! $contactMessage->created_at->format('d.m.Y H:i') !!}

Mesaj:
{!! $contactMessage->message !!}

—
Bu mesaj yönetim panelinde "Web Formları" bölümüne kaydedildi.
