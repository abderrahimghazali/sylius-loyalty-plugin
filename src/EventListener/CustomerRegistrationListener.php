<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\EventListener;

use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Awards bonus points when a new customer registers.
 * Listens to: sylius.customer.post_register
 */
final class CustomerRegistrationListener
{
    public function __construct(
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
        private readonly int $registrationBonus,
    ) {
    }

    public function __invoke(GenericEvent $event): void
    {
        if ($this->registrationBonus <= 0) {
            return;
        }

        $customer = $event->getSubject();

        if (!$customer instanceof CustomerInterface) {
            return;
        }

        $account = $this->balanceManager->getOrCreateAccount($customer);

        $this->balanceManager->addTransaction(
            $account,
            TransactionType::Bonus,
            $this->registrationBonus,
            'Welcome bonus for account registration',
        );
    }
}
