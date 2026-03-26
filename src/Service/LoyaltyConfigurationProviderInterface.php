<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

interface LoyaltyConfigurationProviderInterface
{
    /**
     * Get config for the current request channel (via ChannelContext).
     */
    public function getConfiguration(): LoyaltyConfigurationInterface;

    /**
     * Get config for a specific channel.
     */
    public function getConfigurationForChannel(ChannelInterface $channel): LoyaltyConfigurationInterface;
}
