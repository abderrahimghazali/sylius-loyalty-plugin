<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface LoyaltyConfigurationRepositoryInterface extends RepositoryInterface
{
    public function findOneByChannel(ChannelInterface $channel): ?LoyaltyConfigurationInterface;
}
