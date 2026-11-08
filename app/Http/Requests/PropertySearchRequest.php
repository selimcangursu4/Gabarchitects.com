<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Portföy filtresi (GET). Parametreler Türkçe ve paylaşılabilir URL üretir:
 * /portfoy?islem=satilik&tip=villa&il=İstanbul&ilce=Sarıyer&fiyat_min=...&oda[]=3
 *
 * Geçersiz parametreler hata sayfası yerine sessizce yok sayılır (bkz. filters()).
 */
class PropertySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'islem' => ['nullable', Rule::in(array_column(config('real_estate.listing_types'), 'slug'))],
            'tip' => ['nullable', Rule::in(array_keys(Arr::collapse(config('real_estate.property_types'))))],
            'il' => ['nullable', 'string', 'max:80'],
            'ilce' => ['nullable', 'string', 'max:80'],
            'fiyat_min' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'fiyat_max' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'm2_min' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'm2_max' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'oda' => ['nullable', 'array', 'max:5'],
            'oda.*' => ['integer', Rule::in(array_keys(config('real_estate.room_options')))],
            'siralama' => ['nullable', Rule::in(array_keys(config('real_estate.sort_options')))],
        ];
    }

    /** Formdaki boş alanlar ("") ve binlik ayırıcılar temizlenir. */
    protected function prepareForValidation(): void
    {
        $clean = [];

        foreach (['fiyat_min', 'fiyat_max', 'm2_min', 'm2_max'] as $key) {
            if ($this->filled($key)) {
                $clean[$key] = preg_replace('/\D/', '', (string) $this->input($key));
            }
        }

        $this->merge($clean);
    }

    /**
     * Doğrulanmış filtreler. Geçersiz bir alan varsa sayfa hata vermez; o alan yok sayılır.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $validator = validator($this->all(), $this->rules());

        return collect($validator->valid())
            ->only(array_keys($this->rules()))
            ->reject(fn ($v) => $v === null || $v === '' || $v === [])
            ->all();
    }

    /** Doğrulama hatası sayfayı bozmaz (GET filtre formu). */
    protected function failedValidation($validator): void {}
}
