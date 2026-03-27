<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyRuleInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface LoyaltyRuleRepositoryInterface extends RepositoryInterface
{
    /** @return LoyaltyRuleInterface[] */
    public function findActiveRulesForChannel(ChannelInterface $channel): array;
}
