<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyTierInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class TierEvaluator implements TierEvaluatorInterface
{
    /** @param RepositoryInterface<LoyaltyTierInterface> $tierRepository */
    public function __construct(
        private readonly RepositoryInterface $tierRepository,
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
    ) {
    }

    public function evaluate(LoyaltyAccountInterface $account): void
    {
        if (!$this->configProvider->getConfiguration()->isTiersEnabled()) {
            return;
        }

        $tiers = $this->tierRepository->findBy(
            ['enabled' => true],
            ['minPoints' => 'DESC'],
        );

        $currentTier = $account->getTier();
        $lifetimePoints = $account->getLifetimePoints();

        foreach ($tiers as $tier) {
            if ($lifetimePoints >= $tier->getMinPoints()) {
                // Only upgrade, never downgrade
                if ($currentTier === null || $tier->getMinPoints() > $currentTier->getMinPoints()) {
                    $account->setTier($tier);
                }

                return;
            }
        }
    }
}
