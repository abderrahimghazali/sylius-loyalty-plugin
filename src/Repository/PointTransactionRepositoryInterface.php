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
    public function findExpirableTransactions(\DateTimeInterface $now, ?int $limit = null): array;

    /** @return PointTransactionInterface[] */
    public function findByLoyaltyAccount(LoyaltyAccountInterface $account, int $limit = 50): array;

    public function countByLoyaltyAccount(LoyaltyAccountInterface $account): int;

    /** @return PointTransactionInterface[] */
    public function findPaginatedByLoyaltyAccount(LoyaltyAccountInterface $account, int $page = 1, int $perPage = 20): array;

    public function findEarnByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface;

    public function findBonusByDescription(LoyaltyAccountInterface $account, string $description): ?PointTransactionInterface;

    public function findDeductByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface;

    public function findRedeemByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface;

    public function findRestoreByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface;

    public function findBonusByDescriptionAndYear(LoyaltyAccountInterface $account, string $description, int $year): ?PointTransactionInterface;
}
