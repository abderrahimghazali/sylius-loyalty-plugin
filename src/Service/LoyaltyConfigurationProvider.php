<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfiguration;
use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfigurationInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class LoyaltyConfigurationProvider implements LoyaltyConfigurationProviderInterface
{
    private ?LoyaltyConfigurationInterface $cached = null;

    public function __construct(
        private readonly RepositoryInterface $configurationRepository,
    ) {
    }

    public function getConfiguration(): LoyaltyConfigurationInterface
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $config = $this->configurationRepository->findOneBy([]);

        if ($config === null) {
            // Return an in-memory default with sensible values.
            // Run `loyalty:install` to persist the config row.
            $config = new LoyaltyConfiguration();
        }

        $this->cached = $config;

        return $config;
    }
}
