<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\EventListener;

use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Revokes loyalty points when an order is cancelled.
 * Listens to: sylius.order.post_cancel
 */
final class OrderCancelListener
{
    public function __construct(
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(GenericEvent $event): void
    {
        $order = $event->getSubject();

        if (!$order instanceof OrderInterface) {
            return;
        }

        $this->balanceManager->revokePointsForOrder($order);
        $this->entityManager->flush();
    }
}
