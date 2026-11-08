<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * İletişim / "Bir proje konuşalım" / ilan bilgi talebi formu.
 * Spam koruması: honeypot (website) + süre kontrolü (_t, şifreli zaman damgası) + route rate limit.
 */
class ContactRequest extends FormRequest
{
    public const SOURCES = ['home_cta', 'contact_page', 'property'];

    /** Formun açılmasından gönderimine kadar geçmesi gereken minimum süre (sn). */
    public const MIN_SECONDS = 3;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\s\-]{7,40}$/'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'privacy_consent' => ['accepted'],
            'source' => ['required', Rule::in(self::SOURCES)],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'website' => ['nullable', 'max:0'],   // honeypot: insanlar bu alanı görmez
            '_t' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'ad soyad',
            'email' => 'e-posta',
            'phone' => 'telefon',
            'subject' => 'konu',
            'message' => 'mesaj',
            'privacy_consent' => 'aydınlatma metni onayı',
        ];
    }

    public function messages(): array
    {
        return [
            'privacy_consent.accepted' => 'Devam etmek için aydınlatma metnini onaylamanız gerekiyor.',
            'phone.regex' => 'Telefon numarası yalnızca rakam, boşluk ve + ( ) - içerebilir.',
            'website.max' => 'Gönderim doğrulanamadı.',
            '_t.required' => 'Gönderim doğrulanamadı. Lütfen sayfayı yenileyip tekrar deneyin.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $renderedAt = (int) decrypt((string) $this->input('_t'));
                } catch (DecryptException) {
                    $validator->errors()->add('_t', 'Gönderim doğrulanamadı. Lütfen sayfayı yenileyip tekrar deneyin.');

                    return;
                }

                if (now()->timestamp - $renderedAt < self::MIN_SECONDS) {
                    $validator->errors()->add('_t', 'Form çok hızlı gönderildi. Lütfen tekrar deneyin.');
                }
            },
        ];
    }

    /** Hata durumunda kullanıcı formun olduğu bölüme geri döner. */
    protected function getRedirectUrl(): string
    {
        return strtok(url()->previous(), '#').'#iletisim-formu';
    }
}
