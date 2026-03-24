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
    public function findExpirableTransactions(\DateTimeInterface $now): array
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.expiresAt <= :now')
            ->andWhere('pt.expired = :notExpired')
            ->andWhere('pt.type IN (:earnTypes)')
            ->setParameter('now', $now)
            ->setParameter('notExpired', false)
            ->setParameter('earnTypes', [TransactionType::Earn->value, TransactionType::Bonus->value])
            ->getQuery()
            ->getResult();
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
}
