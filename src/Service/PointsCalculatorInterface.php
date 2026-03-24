<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface PointsCalculatorInterface
{
    /**
     * Calculate the number of points earned for the given order.
     * Takes into account the customer's tier multiplier.
     */
    public function calculateForOrder(OrderInterface $order, ?LoyaltyAccountInterface $account = null): int;
}
