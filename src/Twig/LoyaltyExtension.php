<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LoyaltyExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('loyalty_account', [LoyaltyRuntime::class, 'getLoyaltyAccount']),
            new TwigFunction('loyalty_points_value', [LoyaltyRuntime::class, 'getPointsMonetaryValue']),
            new TwigFunction('loyalty_redemption_rate', [LoyaltyRuntime::class, 'getRedemptionRate']),
        ];
    }
}
