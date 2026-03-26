<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\EventListener;

use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Awards bonus points on a customer's first completed order.
 * Listens to: sylius.order.post_complete
 */
final class FirstOrderBonusListener
{
    public function __construct(
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PointTransactionRepositoryInterface $transactionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(GenericEvent $event): void
    {
        $order = $event->getSubject();

        if (!$order instanceof OrderInterface) {
            return;
        }

        $config = $this->configProvider->getConfiguration();

        if (!$config->isFirstOrderBonusEnabled() || $config->getFirstOrderBonusPoints() <= 0) {
            return;
        }

        $customer = $order->getCustomer();
        if ($customer === null) {
            return;
        }

        $account = $this->balanceManager->getOrCreateAccount($customer);

        if (!$account->isEnabled()) {
            return;
        }

        // Check if a first-order bonus was already awarded (idempotency)
        $existing = $this->transactionRepository->findBonusByDescription($account, 'First order bonus');
        if ($existing !== null) {
            return;
        }

        // Check this is actually the customer's first completed order
        $completedOrders = $this->orderRepository->countByCustomer($customer);
        if ($completedOrders > 1) {
            return;
        }

        $this->balanceManager->addTransaction(
            $account,
            TransactionType::Bonus,
            $config->getFirstOrderBonusPoints(),
            'First order bonus',
            $order,
        );

        $this->entityManager->flush();
    }
}
