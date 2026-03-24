<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Enum;

enum TransactionType: string
{
    case Earn = 'earn';
    case Redeem = 'redeem';
    case Expire = 'expire';
    case Adjust = 'adjust';
    case Bonus = 'bonus';

    public function label(): string
    {
        return match ($this) {
            self::Earn => 'Points earned',
            self::Redeem => 'Points redeemed',
            self::Expire => 'Points expired',
            self::Adjust => 'Manual adjustment',
            self::Bonus => 'Bonus points',
        };
    }

    public function isDebit(): bool
    {
        return match ($this) {
            self::Redeem, self::Expire => true,
            default => false,
        };
    }
}
