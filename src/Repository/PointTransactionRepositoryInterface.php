<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransactionInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface PointTransactionRepositoryInterface extends RepositoryInterface
{
    /** @return PointTransactionInterface[] */
    public function findExpirableTransactions(\DateTimeInterface $now): array;

    /** @return PointTransactionInterface[] */
    public function findByLoyaltyAccount(LoyaltyAccountInterface $account, int $limit = 50): array;

    public function findEarnByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface;
}
