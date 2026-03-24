<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface LoyaltyTierInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getMinPoints(): int;

    public function setMinPoints(int $minPoints): void;

    public function getMultiplier(): float;

    public function setMultiplier(float $multiplier): void;

    public function getPosition(): int;

    public function setPosition(int $position): void;

    public function getColor(): ?string;

    public function setColor(?string $color): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;
}
