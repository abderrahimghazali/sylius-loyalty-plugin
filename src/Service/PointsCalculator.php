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

        // Use items subtotal (pre-discount) so loyalty/coupon discounts don't reduce earned points
        $orderTotalInUnits = $order->getItemsTotal() / 100;

        $basePoints = (int) floor($orderTotalInUnits * $config->getPointsPerCurrencyUnit());

        // Apply tier multiplier if account has a tier
        $multiplier = 1.0;
        if ($account !== null && $account->getTier() !== null) {
            $multiplier = $account->getTier()->getMultiplier();
        }

        return (int) floor($basePoints * $multiplier);
    }
}
