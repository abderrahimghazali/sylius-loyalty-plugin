<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\EventListener\Workflow;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Workflow\Event\CompletedEvent;

/**
 * When an order is cancelled, restore any points that were redeemed for it.
 *
 * Workflow: sylius_order, transition: cancel
 */
#[AsEventListener(event: 'workflow.sylius_order.completed.cancel')]
final class RestorePointsOnOrderCancelListener
{
    public function __construct(
        private readonly LoyaltyAccountRepositoryInterface $accountRepository,
        private readonly PointTransactionRepositoryInterface $transactionRepository,
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $order = $event->getSubject();

        if (!$order instanceof OrderInterface || !$order instanceof LoyaltyOrderInterface) {
            return;
        }

        $customer = $order->getCustomer();
        if ($customer === null) {
            return;
        }

        $account = $this->accountRepository->findOneByCustomer($customer);
        if ($account === null) {
            return;
        }

        // Find the redeem transaction for this order
        $redeemTransaction = $this->findRedeemTransactionForOrder($account, $order);
        if ($redeemTransaction === null) {
            return;
        }

        // Restore the points via a positive adjust transaction
        $this->balanceManager->addTransaction(
            $account,
            TransactionType::Adjust,
            $redeemTransaction->getPoints(),
            sprintf('Points restored for cancelled order #%s', $order->getNumber()),
            $order,
        );
    }

    private function findRedeemTransactionForOrder($account, OrderInterface $order): ?object
    {
        // Search through transactions for a redeem type linked to this order
        $transactions = $this->transactionRepository->findByLoyaltyAccount($account, 500);

        foreach ($transactions as $transaction) {
            if (
                $transaction->getType() === TransactionType::Redeem
                && $transaction->getOrder()?->getId() === $order->getId()
            ) {
                return $transaction;
            }
        }

        return null;
    }
}
