<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransactionInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;

interface LoyaltyBalanceManagerInterface
{
    public function getOrCreateAccount(CustomerInterface $customer): LoyaltyAccountInterface;

    public function addTransaction(
        LoyaltyAccountInterface $account,
        TransactionType $type,
        int $points,
        ?string $description = null,
        ?OrderInterface $order = null,
        ?ChannelInterface $channel = null,
    ): PointTransactionInterface;

    public function awardPointsForOrder(OrderInterface $order): ?PointTransactionInterface;

    public function revokePointsForOrder(OrderInterface $order): ?PointTransactionInterface;
}
