<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransactionInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\OrderInterface;

class PointTransactionRepository extends EntityRepository implements PointTransactionRepositoryInterface
{
    /** @return PointTransactionInterface[] */
    public function findExpirableTransactions(\DateTimeInterface $now, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('pt')
            ->andWhere('pt.expiresAt <= :now')
            ->andWhere('pt.expired = :notExpired')
            ->andWhere('pt.type IN (:earnTypes)')
            ->setParameter('now', $now)
            ->setParameter('notExpired', false)
            ->setParameter('earnTypes', [TransactionType::Earn->value, TransactionType::Bonus->value]);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /** @return PointTransactionInterface[] */
    public function findByLoyaltyAccount(LoyaltyAccountInterface $account, int $limit = 50): array
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->setParameter('account', $account)
            ->orderBy('pt.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByLoyaltyAccount(LoyaltyAccountInterface $account): int
    {
        return (int) $this->createQueryBuilder('pt')
            ->select('COUNT(pt.id)')
            ->andWhere('pt.loyaltyAccount = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return PointTransactionInterface[] */
    public function findPaginatedByLoyaltyAccount(LoyaltyAccountInterface $account, int $page = 1, int $perPage = 20): array
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->setParameter('account', $account)
            ->orderBy('pt.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function findEarnByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->andWhere('pt.order = :order')
            ->andWhere('pt.type = :type')
            ->setParameter('account', $account)
            ->setParameter('order', $order)
            ->setParameter('type', TransactionType::Earn->value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBonusByDescription(LoyaltyAccountInterface $account, string $description): ?PointTransactionInterface
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->andWhere('pt.type = :type')
            ->andWhere('pt.description = :description')
            ->setParameter('account', $account)
            ->setParameter('type', TransactionType::Bonus->value)
            ->setParameter('description', $description)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findDeductByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->andWhere('pt.order = :order')
            ->andWhere('pt.type = :type')
            ->setParameter('account', $account)
            ->setParameter('order', $order)
            ->setParameter('type', TransactionType::Deduct->value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRedeemByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->andWhere('pt.order = :order')
            ->andWhere('pt.type = :type')
            ->setParameter('account', $account)
            ->setParameter('order', $order)
            ->setParameter('type', TransactionType::Redeem->value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRestoreByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->andWhere('pt.order = :order')
            ->andWhere('pt.type = :type')
            ->andWhere('pt.description LIKE :desc')
            ->setParameter('account', $account)
            ->setParameter('order', $order)
            ->setParameter('type', TransactionType::Adjust->value)
            ->setParameter('desc', 'Points restored%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBonusByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->andWhere('pt.order = :order')
            ->andWhere('pt.type = :type')
            ->setParameter('account', $account)
            ->setParameter('order', $order)
            ->setParameter('type', TransactionType::Bonus->value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBonusDeductByOrder(LoyaltyAccountInterface $account, OrderInterface $order): ?PointTransactionInterface
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->andWhere('pt.order = :order')
            ->andWhere('pt.type = :type')
            ->andWhere('pt.description LIKE :desc')
            ->setParameter('account', $account)
            ->setParameter('order', $order)
            ->setParameter('type', TransactionType::Deduct->value)
            ->setParameter('desc', 'Bonus revoked%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBonusByDescriptionAndYear(LoyaltyAccountInterface $account, string $description, int $year): ?PointTransactionInterface
    {
        $start = new \DateTime(sprintf('%d-01-01', $year));
        $end = new \DateTime(sprintf('%d-01-01', $year + 1));

        return $this->createQueryBuilder('pt')
            ->andWhere('pt.loyaltyAccount = :account')
            ->andWhere('pt.type = :type')
            ->andWhere('pt.description = :description')
            ->andWhere('pt.createdAt >= :start')
            ->andWhere('pt.createdAt < :end')
            ->setParameter('account', $account)
            ->setParameter('type', TransactionType::Bonus->value)
            ->setParameter('description', $description)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
