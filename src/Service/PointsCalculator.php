<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class PointsCalculator implements PointsCalculatorInterface
{
    public function __construct(
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
        private readonly EarningRuleResolverInterface $earningRuleResolver,
    ) {
    }

    public function calculateForOrder(
        OrderInterface $order,
        ?LoyaltyAccountInterface $account = null,
        ?ChannelInterface $channel = null,
    ): int {
        $config = $channel !== null
            ? $this->configProvider->getConfigurationForChannel($channel)
            : $this->configProvider->getConfiguration();

        $defaultRate = $config->getPointsPerCurrencyUnit();
        $totalPoints = 0;

        // Check if there's an earning rule rate for this channel
        $earningRuleRate = $channel !== null
            ? $this->earningRuleResolver->getPointsRateForChannel($channel)
            : null;

        foreach ($order->getItems() as $item) {
            if ($earningRuleRate !== null) {
                // Earning rule: points per product purchased
                $totalPoints += $item->getQuantity() * $earningRuleRate;
            } else {
                // Default: points per currency unit spent
                $itemTotalInUnits = $item->getTotal() / 100;
                $totalPoints += (int) floor($itemTotalInUnits * $defaultRate);
            }
        }

        // Apply tier multiplier
        $multiplier = 1.0;
        if ($account !== null && $account->getTier() !== null) {
            $multiplier = $account->getTier()->getMultiplier();
        }

        return (int) floor($totalPoints * $multiplier);
    }
}
