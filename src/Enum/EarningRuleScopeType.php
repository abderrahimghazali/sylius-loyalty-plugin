<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Enum;

enum EarningRuleScopeType: string
{
    case Taxon = 'taxon';
    case Product = 'product';
    case Variant = 'variant';

    public function label(): string
    {
        return match ($this) {
            self::Taxon => 'Category',
            self::Product => 'Product',
            self::Variant => 'Variant',
        };
    }

    /**
     * Higher = more specific = wins over lower.
     */
    public function specificity(): int
    {
        return match ($this) {
            self::Variant => 30,
            self::Product => 20,
            self::Taxon => 10,
        };
    }
}
