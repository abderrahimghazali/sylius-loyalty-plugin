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
        private readonly LoyaltyRuleResolverInterface $ruleResolver,
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

        foreach ($order->getItems() as $item) {
            $rate = $defaultRate;

            // Check for a per-product loyalty rule override
            if ($channel !== null) {
                $rule = $this->ruleResolver->resolve($item, $channel);
                if ($rule !== null) {
                    $rate = $rule->getPointsPerCurrencyUnit();
                }
            }

            // Use item total (unit price * qty minus item-level promotions)
            $itemTotalInUnits = $item->getTotal() / 100;
            $totalPoints += (int) floor($itemTotalInUnits * $rate);
        }

        // Apply tier multiplier
        $multiplier = 1.0;
        if ($account !== null && $account->getTier() !== null) {
            $multiplier = $account->getTier()->getMultiplier();
        }

        return (int) floor($totalPoints * $multiplier);
    }
}
