<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public static function numbers(): array
    {
        return [
            'yerel cep' => ['0532 000 00 00', '905320000000'],
            'uluslararası' => ['+90 (532) 000 00 00', '905320000000'],
            'başında sıfır yok' => ['532 000 00 00', '905320000000'],
            '00 ile' => ['0090 532 000 00 00', '905320000000'],
            'sabit hat' => ['0212-555 00 00', '902125550000'],
            'yabancı' => ['+44 20 7946 0958', '442079460958'],
        ];
    }

    #[DataProvider('numbers')]
    public function test_international_format(string $input, string $expected): void
    {
        $this->assertSame($expected, Phone::international($input));
        $this->assertSame('tel:+'.$expected, Phone::telHref($input));
    }

    public function test_invalid_or_empty_numbers_return_null(): void
    {
        foreach ([null, '', '123', 'abc', '0532'] as $input) {
            $this->assertNull(Phone::international($input));
            $this->assertNull(Phone::whatsappHref($input, 'x'));
        }
    }

    public function test_whatsapp_link_encodes_message(): void
    {
        $this->assertSame(
            'https://wa.me/905320000000?text=Merhaba%2C%20%22Bodrum%20Villa%22%20%C4%B0lan%C4%B1',
            Phone::whatsappHref('0532 000 00 00', 'Merhaba, "Bodrum Villa" İlanı'),
        );
    }
}
