<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfiguration;
use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfigurationInterface;
use Doctrine\ORM\EntityManagerInterface;

final class LoyaltyConfigurationProvider implements LoyaltyConfigurationProviderInterface
{
    private ?LoyaltyConfigurationInterface $cached = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getConfiguration(): LoyaltyConfigurationInterface
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $repository = $this->entityManager->getRepository(LoyaltyConfiguration::class);
        $config = $repository->findOneBy([]);

        if ($config === null) {
            // Return a transient default — callers that need persistence
            // (e.g. admin config form) will persist and flush themselves.
            $config = new LoyaltyConfiguration();
            $this->entityManager->persist($config);
        }

        $this->cached = $config;

        return $config;
    }
}
