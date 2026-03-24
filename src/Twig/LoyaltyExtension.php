<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Twig;

use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

final class LoyaltyExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly LoyaltyAccountRepositoryInterface $accountRepository,
        private readonly int $redemptionRate,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'redemption_rate' => $this->redemptionRate,
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('loyalty_account', $this->getLoyaltyAccount(...)),
            new TwigFunction('loyalty_points_value', $this->getPointsMonetaryValue(...)),
        ];
    }

    public function getLoyaltyAccount(CustomerInterface $customer): ?array
    {
        $account = $this->accountRepository->findOneByCustomer($customer);

        if ($account === null) {
            return null;
        }

        return [
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
        if ($this->redemptionRate <= 0) {
            return 0.0;
        }

        return round($points / $this->redemptionRate, 2);
    }
}
