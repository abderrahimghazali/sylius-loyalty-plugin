<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

interface EarningRuleResolverInterface
{
    /**
     * Resolve the best-matching earning rule for an order item.
     * Returns null if the channel default rate should be used.
     */
    public function resolve(OrderItemInterface $item, ChannelInterface $channel): ?LoyaltyEarningRuleInterface;
}
