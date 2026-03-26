<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\EventListener\Workflow;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Workflow\Event\CompletedEvent;

/**
 * After the checkout workflow transition "complete" fires, deduct the
 * redeemed points from the customer's loyalty account.
 *
 * Uses a pessimistic write lock to prevent double-spend when two
 * checkouts for the same customer complete concurrently.
 *
 * Workflow: sylius_order_checkout, transition: complete
 */
#[AsEventListener(event: 'workflow.sylius_order_checkout.completed.complete')]
final class DeductPointsOnOrderCompleteListener
{
    public function __construct(
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

        $pointsToRedeem = $order->getPointsToRedeem();
        if ($pointsToRedeem <= 0) {
            return;
        }

        $customer = $order->getCustomer();
        if ($customer === null) {
            return;
        }

        $account = $this->balanceManager->getOrCreateAccount($customer);

        // Lock the account row to prevent concurrent double-spend
        $this->entityManager->find(LoyaltyAccount::class, $account->getId(), LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->refresh($account);

        // Final guard: don't deduct more than available
        $effectivePoints = min($pointsToRedeem, $account->getPointsBalance());
        if ($effectivePoints <= 0) {
            return;
        }

        $this->balanceManager->addTransaction(
            $account,
            TransactionType::Redeem,
            $effectivePoints,
            sprintf('Points redeemed for order #%s', $order->getNumber()),
            $order,
        );
    }
}
