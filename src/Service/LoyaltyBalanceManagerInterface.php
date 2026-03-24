<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransactionInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;

interface LoyaltyBalanceManagerInterface
{
    /**
     * Get or create a loyalty account for the given customer.
     */
    public function getOrCreateAccount(CustomerInterface $customer): LoyaltyAccountInterface;

    /**
     * Add a point transaction and update the account balance.
     */
    public function addTransaction(
        LoyaltyAccountInterface $account,
        TransactionType $type,
        int $points,
        ?string $description = null,
        ?OrderInterface $order = null,
    ): PointTransactionInterface;

    /**
     * Award points for a completed order.
     */
    public function awardPointsForOrder(OrderInterface $order): ?PointTransactionInterface;

    /**
     * Revoke points previously awarded for an order (on cancel/refund).
     */
    public function revokePointsForOrder(OrderInterface $order): ?PointTransactionInterface;
}
