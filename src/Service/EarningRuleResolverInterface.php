<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Sylius\Component\Channel\Model\ChannelInterface;

interface EarningRuleResolverInterface
{
    /**
     * Get the points-per-product rate for a channel from earning rules.
     * Returns null if no earning rule is configured for this channel.
     */
    public function getPointsRateForChannel(ChannelInterface $channel): ?int;
}
