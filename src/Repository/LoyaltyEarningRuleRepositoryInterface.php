<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface LoyaltyEarningRuleRepositoryInterface extends RepositoryInterface
{
    /** @return LoyaltyEarningRuleInterface[] */
    public function findEnabledRulesForChannel(ChannelInterface $channel): array;
}
