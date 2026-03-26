<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['code'], message: 'A tier with this code already exists.')]
class LoyaltyTier implements LoyaltyTierInterface
{
    protected ?int $id = null;

    protected ?string $name = null;

    protected ?string $code = null;

    protected int $minPoints = 0;

    protected float $multiplier = 1.0;

    protected int $position = 0;

    protected ?string $color = null;

    protected bool $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getMinPoints(): int
    {
        return $this->minPoints;
    }

    public function setMinPoints(int $minPoints): void
    {
        $this->minPoints = $minPoints;
    }

    public function getMultiplier(): float
    {
        return $this->multiplier;
    }

    public function setMultiplier(float $multiplier): void
    {
        $this->multiplier = $multiplier;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
