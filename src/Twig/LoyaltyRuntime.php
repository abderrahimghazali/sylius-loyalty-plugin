<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Twig;

use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class LoyaltyRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly LoyaltyAccountRepositoryInterface $accountRepository,
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
    ) {
    }

    public function getLoyaltyAccount(CustomerInterface $customer): ?array
    {
        $account = $this->accountRepository->findOneByCustomer($customer);

        if ($account === null) {
            return null;
        }

        return [
            'id' => $account->getId(),
            'balance' => $account->getPointsBalance(),
            'lifetime' => $account->getLifetimePoints(),
            'tier' => $account->getTier()?->getName(),
            'tier_color' => $account->getTier()?->getColor(),
            'enabled' => $account->isEnabled(),
        ];
    }

    /**
     * Convert points to monetary value (in whole currency units).
     */
    public function getPointsMonetaryValue(int $points): float
    {
        $redemptionRate = $this->configProvider->getConfiguration()->getRedemptionRate();

        if ($redemptionRate <= 0) {
            return 0.0;
        }

        return round($points / $redemptionRate, 2);
    }

    /**
     * Get the redemption rate for the current channel (lazy, called at render time).
     */
    public function getRedemptionRate(): int
    {
        return $this->configProvider->getConfiguration()->getRedemptionRate();
    }
}
