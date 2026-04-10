<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\EventListener\Workflow;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Workflow\Event\CompletedEvent;

/**
 * When an order is cancelled:
 * 1. Revoke earned points
 * 2. Revoke bonus points (first order bonus)
 * 3. Restore any redeemed points
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
        private readonly EntityManagerInterface $entityManager,
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

        // Lock the account row to prevent concurrent double-processing
        $this->entityManager->find($account::class, $account->getId(), LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->refresh($account);

        // 1. Revoke earned points
        $this->balanceManager->revokePointsForOrder($order);

        // 2. Revoke bonus points (e.g. first order bonus)
        $this->balanceManager->revokeBonusForOrder($order);

        // 3. Restore redeemed points
        $redeemTransaction = $this->transactionRepository->findRedeemByOrder($account, $order);
        if ($redeemTransaction !== null) {
            $existing = $this->transactionRepository->findRestoreByOrder($account, $order);
            if ($existing === null) {
                $this->balanceManager->addTransaction(
                    $account,
                    TransactionType::Adjust,
                    $redeemTransaction->getPoints(),
                    sprintf('Points restored for cancelled order #%s', $order->getNumber()),
                    $order,
                );
            }
        }

        $this->entityManager->flush();
    }
}
