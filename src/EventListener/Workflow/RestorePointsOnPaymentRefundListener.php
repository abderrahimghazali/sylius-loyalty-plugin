<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\EventListener\Workflow;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Workflow\Event\CompletedEvent;

/**
 * When a payment is refunded, restore any points that were redeemed for that order.
 *
 * Workflow: sylius_payment, transition: refund
 */
#[AsEventListener(event: 'workflow.sylius_payment.completed.refund')]
final class RestorePointsOnPaymentRefundListener
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
        $payment = $event->getSubject();

        if (!$payment instanceof PaymentInterface) {
            return;
        }

        $order = $payment->getOrder();
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

        // Find the redeem transaction for this order via DB query
        $redeemTransaction = $this->transactionRepository->findRedeemByOrder($account, $order);
        if ($redeemTransaction === null) {
            return;
        }

        // Check we haven't already restored via DB query
        $existing = $this->transactionRepository->findRestoreByOrder($account, $order);
        if ($existing !== null) {
            return;
        }

        $this->balanceManager->addTransaction(
            $account,
            TransactionType::Adjust,
            $redeemTransaction->getPoints(),
            sprintf('Points restored for refunded order #%s', $order->getNumber()),
            $order,
        );

        $this->entityManager->flush();
    }
}
