<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity\Order;

use Doctrine\ORM\Mapping as ORM;

trait LoyaltyOrderTrait
{
    #[ORM\Column(name: 'loyalty_points_to_redeem', type: 'integer', options: ['default' => 0])]
    protected int $pointsToRedeem = 0;

    public function getPointsToRedeem(): int
    {
        return $this->pointsToRedeem;
    }

    public function setPointsToRedeem(int $pointsToRedeem): void
    {
        $this->pointsToRedeem = $pointsToRedeem;
    }
}
