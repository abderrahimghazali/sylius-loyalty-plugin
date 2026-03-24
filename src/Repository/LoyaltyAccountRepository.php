<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Customer\Model\CustomerInterface;

class LoyaltyAccountRepository extends EntityRepository implements LoyaltyAccountRepositoryInterface
{
    public function findOneByCustomer(CustomerInterface $customer): ?LoyaltyAccountInterface
    {
        return $this->createQueryBuilder('la')
            ->andWhere('la.customer = :customer')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
