<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfiguration;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyConfigurationRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

final class LoyaltyConfigurationProvider implements LoyaltyConfigurationProviderInterface
{
    /** @var array<string, LoyaltyConfigurationInterface> */
    private array $cache = [];

    public function __construct(
        private readonly LoyaltyConfigurationRepositoryInterface $configurationRepository,
        private readonly ChannelContextInterface $channelContext,
    ) {
    }

    public function getConfiguration(): LoyaltyConfigurationInterface
    {
        try {
            $channel = $this->channelContext->getChannel();
        } catch (\Throwable) {
            // No channel context available (e.g., CLI commands without channel scope)
            return new LoyaltyConfiguration();
        }

        return $this->getConfigurationForChannel($channel);
    }

    public function getConfigurationForChannel(ChannelInterface $channel): LoyaltyConfigurationInterface
    {
        $code = $channel->getCode() ?? '__default__';

        if (isset($this->cache[$code])) {
            return $this->cache[$code];
        }

        $config = $this->configurationRepository->findOneByChannel($channel);

        if (!$config instanceof LoyaltyConfigurationInterface) {
            // Return an in-memory default. Run `loyalty:install` to persist.
            $config = new LoyaltyConfiguration();
        }

        $this->cache[$code] = $config;

        return $config;
    }
}
