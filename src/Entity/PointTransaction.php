<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Sylius\Component\Core\Model\OrderInterface;

class PointTransaction implements PointTransactionInterface
{
    protected ?int $id = null;

    protected ?LoyaltyAccountInterface $loyaltyAccount = null;

    protected TransactionType $type = TransactionType::Earn;

    protected int $points = 0;

    protected ?OrderInterface $order = null;

    protected ?string $description = null;

    protected ?\DateTimeInterface $expiresAt = null;

    protected bool $expired = false;

    protected ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLoyaltyAccount(): ?LoyaltyAccountInterface
    {
        return $this->loyaltyAccount;
    }

    public function setLoyaltyAccount(?LoyaltyAccountInterface $loyaltyAccount): void
    {
        $this->loyaltyAccount = $loyaltyAccount;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function setType(TransactionType $type): void
    {
        $this->type = $type;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): void
    {
        $this->points = $points;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?OrderInterface $order): void
    {
        $this->order = $order;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function isExpired(): bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): void
    {
        $this->expired = $expired;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

}
