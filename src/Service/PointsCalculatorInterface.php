<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface PointsCalculatorInterface
{
    public function calculateForOrder(
        OrderInterface $order,
        ?LoyaltyAccountInterface $account = null,
        ?ChannelInterface $channel = null,
    ): int;
}
