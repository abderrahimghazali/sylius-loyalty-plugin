<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyRuleInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

interface LoyaltyRuleResolverInterface
{
    public function resolve(OrderItemInterface $item, ChannelInterface $channel): ?LoyaltyRuleInterface;
}
