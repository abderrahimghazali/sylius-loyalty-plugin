<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity\Order;

interface LoyaltyOrderInterface
{
    public function getPointsToRedeem(): int;

    public function setPointsToRedeem(int $pointsToRedeem): void;
}
