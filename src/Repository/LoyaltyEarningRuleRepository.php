<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class LoyaltyEarningRuleRepository extends EntityRepository implements LoyaltyEarningRuleRepositoryInterface
{
    /** @return LoyaltyEarningRuleInterface[] */
    public function findEnabledRulesForChannel(ChannelInterface $channel): array
    {
        return $this->createQueryBuilder('er')
            ->innerJoin('er.channelConfigurations', 'cc')
            ->andWhere('cc.channel = :channel')
            ->andWhere('er.enabled = :enabled')
            ->setParameter('channel', $channel)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();
    }
}
