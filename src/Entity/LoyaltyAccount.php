<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Resource\Model\TimestampableTrait;

class LoyaltyAccount implements LoyaltyAccountInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected ?CustomerInterface $customer = null;

    protected int $pointsBalance = 0;

    protected int $lifetimePoints = 0;

    protected ?LoyaltyTierInterface $tier = null;

    protected bool $enabled = true;

    /** @var Collection<int, PointTransactionInterface> */
    protected Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): void
    {
        $this->customer = $customer;
    }

    public function getPointsBalance(): int
    {
        return $this->pointsBalance;
    }

    public function setPointsBalance(int $pointsBalance): void
    {
        $this->pointsBalance = $pointsBalance;
    }

    public function getLifetimePoints(): int
    {
        return $this->lifetimePoints;
    }

    public function setLifetimePoints(int $lifetimePoints): void
    {
        $this->lifetimePoints = $lifetimePoints;
    }

    public function getTier(): ?LoyaltyTierInterface
    {
        return $this->tier;
    }

    public function setTier(?LoyaltyTierInterface $tier): void
    {
        $this->tier = $tier;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /** @return Collection<int, PointTransactionInterface> */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(PointTransactionInterface $transaction): void
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setLoyaltyAccount($this);
        }
    }

    public function creditPoints(int $points): void
    {
        $this->pointsBalance += $points;
        $this->lifetimePoints += $points;
    }

    public function debitPoints(int $points): void
    {
        if ($points > $this->pointsBalance) {
            throw new \DomainException(sprintf(
                'Cannot debit %d points — only %d available.',
                $points,
                $this->pointsBalance,
            ));
        }

        $this->pointsBalance -= $points;
    }
}
