<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Repository;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @extends RepositoryInterface<LoyaltyAccountInterface>
 */
interface LoyaltyAccountRepositoryInterface extends RepositoryInterface
{
    public function findOneByCustomer(CustomerInterface $customer): ?LoyaltyAccountInterface;
}
