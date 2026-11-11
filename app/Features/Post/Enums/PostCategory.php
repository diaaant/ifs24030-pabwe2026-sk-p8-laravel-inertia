<?php

namespace App\Features\Post\Enums;

enum PostCategory: string
{
    case Teknologi = 'Teknologi';
    case Pendidikan = 'Pendidikan';
    case GayaHidup = 'Gaya Hidup';
    case Bisnis = 'Bisnis';
    case Opini = 'Opini';
    case Umum = 'Umum';

    public function label(): string
    {
        return match ($this) {
            self::Teknologi => 'Teknologi & Koding',
            self::Pendidikan => 'Pendidikan & Tutorial',
            self::GayaHidup => 'Gaya Hidup & Produktivitas',
            self::Bisnis => 'Bisnis & Karir',
            self::Opini => 'Opini & Pemikiran',
            self::Umum => 'Umum',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /** @return list<array{value: string, label: string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }

    /** Toleran terhadap huruf besar/kecil dan label panjang; fallback ke Umum. */
    public static function fromLabel(?string $text): self
    {
        $needle = mb_strtolower(trim((string) $text));

        foreach (self::cases() as $case) {
            if (mb_strtolower($case->value) === $needle || mb_strtolower($case->label()) === $needle) {
                return $case;
            }
        }

        return self::Umum;
    }
}
