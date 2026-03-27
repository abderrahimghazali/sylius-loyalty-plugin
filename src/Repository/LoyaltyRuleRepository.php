<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyRuleInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class LoyaltyRuleRepository extends EntityRepository implements LoyaltyRuleRepositoryInterface
{
    /** @return LoyaltyRuleInterface[] */
    public function findActiveRulesForChannel(ChannelInterface $channel): array
    {
        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.channels', 'c')
            ->andWhere('c = :channel')
            ->andWhere('r.enabled = :enabled')
            ->setParameter('channel', $channel)
            ->setParameter('enabled', true);

        return $qb->getQuery()->getResult();
    }
}
