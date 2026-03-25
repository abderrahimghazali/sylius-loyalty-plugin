<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class PointsCalculator implements PointsCalculatorInterface
{
    public function __construct(
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
    ) {
    }

    public function calculateForOrder(OrderInterface $order, ?LoyaltyAccountInterface $account = null): int
    {
        $config = $this->configProvider->getConfiguration();

        // Order total is in smallest currency unit (cents), convert to whole units
        $orderTotalInUnits = $order->getTotal() / 100;

        $basePoints = (int) floor($orderTotalInUnits * $config->getPointsPerCurrencyUnit());

        // Apply tier multiplier if account has a tier
        $multiplier = 1.0;
        if ($account !== null && $account->getTier() !== null) {
            $multiplier = $account->getTier()->getMultiplier();
        }

        return (int) floor($basePoints * $multiplier);
    }
}
