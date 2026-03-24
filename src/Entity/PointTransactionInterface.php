<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface PointTransactionInterface extends ResourceInterface
{
    public function getLoyaltyAccount(): ?LoyaltyAccountInterface;

    public function setLoyaltyAccount(?LoyaltyAccountInterface $loyaltyAccount): void;

    public function getType(): TransactionType;

    public function setType(TransactionType $type): void;

    public function getPoints(): int;

    public function setPoints(int $points): void;

    public function getOrder(): ?OrderInterface;

    public function setOrder(?OrderInterface $order): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getExpiresAt(): ?\DateTimeInterface;

    public function setExpiresAt(?\DateTimeInterface $expiresAt): void;

    public function isExpired(): bool;

    public function setExpired(bool $expired): void;

    public function getCreatedAt(): ?\DateTimeInterface;
}
