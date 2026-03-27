<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class LoyaltyEarningRuleRepository extends EntityRepository implements LoyaltyEarningRuleRepositoryInterface
{
    /** @return LoyaltyEarningRuleInterface[] */
    public function findActiveRulesForChannel(ChannelInterface $channel, ?\DateTimeInterface $date = null): array
    {
        $date ??= new \DateTime();

        $qb = $this->createQueryBuilder('er')
            ->innerJoin('er.channels', 'c')
            ->andWhere('c = :channel')
            ->andWhere('er.enabled = :enabled')
            ->andWhere('er.startsAt IS NULL OR er.startsAt <= :date')
            ->andWhere('er.endsAt IS NULL OR er.endsAt >= :date')
            ->setParameter('channel', $channel)
            ->setParameter('enabled', true)
            ->setParameter('date', $date)
            ->orderBy('er.priority', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
