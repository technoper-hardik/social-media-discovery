<?php

namespace App\Enums;

enum KeywordContext: string
{
    case COMPANY = 'company';
    case CATEGORY = 'category';
    case INDUSTRY = 'industry';
    case BRAND = 'brand';
    case PRODUCT = 'product';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }
}
