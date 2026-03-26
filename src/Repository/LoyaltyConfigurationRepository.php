<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class LoyaltyConfigurationRepository extends EntityRepository implements LoyaltyConfigurationRepositoryInterface
{
    public function findOneByChannel(ChannelInterface $channel): ?LoyaltyConfigurationInterface
    {
        /** @var LoyaltyConfigurationInterface|null $result */
        $result = $this->createQueryBuilder('lc')
            ->andWhere('lc.channel = :channel')
            ->setParameter('channel', $channel)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
