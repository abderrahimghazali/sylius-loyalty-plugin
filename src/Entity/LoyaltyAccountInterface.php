<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface LoyaltyAccountInterface extends ResourceInterface, TimestampableInterface
{
    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): void;

    public function getPointsBalance(): int;

    public function setPointsBalance(int $pointsBalance): void;

    public function getLifetimePoints(): int;

    public function setLifetimePoints(int $lifetimePoints): void;

    public function getTier(): ?LoyaltyTierInterface;

    public function setTier(?LoyaltyTierInterface $tier): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    /** @return Collection<int, PointTransactionInterface> */
    public function getTransactions(): Collection;

    public function addTransaction(PointTransactionInterface $transaction): void;

    public function creditPoints(int $points): void;

    public function debitPoints(int $points): void;
}
