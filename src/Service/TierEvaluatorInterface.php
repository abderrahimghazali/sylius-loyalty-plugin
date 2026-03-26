<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

interface TierEvaluatorInterface
{
    /**
     * Evaluate and assign the correct tier based on lifetime points.
     * Tiers only go up, never demote.
     */
    public function evaluate(LoyaltyAccountInterface $account, ?ChannelInterface $channel = null): void;
}
