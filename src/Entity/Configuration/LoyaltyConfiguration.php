<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'loyalty_configuration')]
class LoyaltyConfiguration implements LoyaltyConfigurationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    protected int $pointsPerCurrencyUnit = 1;

    #[ORM\Column(type: 'integer', options: ['default' => 100])]
    protected int $redemptionRate = 100;

    #[ORM\Column(type: 'integer', options: ['default' => 12])]
    protected int $expiryMonths = 12;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected bool $registrationBonusEnabled = true;

    #[ORM\Column(type: 'integer', options: ['default' => 100])]
    protected int $registrationBonusPoints = 100;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected bool $birthdayBonusEnabled = true;

    #[ORM\Column(type: 'integer', options: ['default' => 200])]
    protected int $birthdayBonusPoints = 200;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected bool $firstOrderBonusEnabled = true;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    protected int $firstOrderBonusPoints = 50;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPointsPerCurrencyUnit(): int
    {
        return $this->pointsPerCurrencyUnit;
    }

    public function setPointsPerCurrencyUnit(int $pointsPerCurrencyUnit): void
    {
        $this->pointsPerCurrencyUnit = $pointsPerCurrencyUnit;
    }

    public function getRedemptionRate(): int
    {
        return $this->redemptionRate;
    }

    public function setRedemptionRate(int $redemptionRate): void
    {
        $this->redemptionRate = $redemptionRate;
    }

    public function getExpiryMonths(): int
    {
        return $this->expiryMonths;
    }

    public function setExpiryMonths(int $expiryMonths): void
    {
        $this->expiryMonths = $expiryMonths;
    }

    public function isRegistrationBonusEnabled(): bool
    {
        return $this->registrationBonusEnabled;
    }

    public function setRegistrationBonusEnabled(bool $enabled): void
    {
        $this->registrationBonusEnabled = $enabled;
    }

    public function getRegistrationBonusPoints(): int
    {
        return $this->registrationBonusPoints;
    }

    public function setRegistrationBonusPoints(int $points): void
    {
        $this->registrationBonusPoints = $points;
    }

    public function isBirthdayBonusEnabled(): bool
    {
        return $this->birthdayBonusEnabled;
    }

    public function setBirthdayBonusEnabled(bool $enabled): void
    {
        $this->birthdayBonusEnabled = $enabled;
    }

    public function getBirthdayBonusPoints(): int
    {
        return $this->birthdayBonusPoints;
    }

    public function setBirthdayBonusPoints(int $points): void
    {
        $this->birthdayBonusPoints = $points;
    }

    public function isFirstOrderBonusEnabled(): bool
    {
        return $this->firstOrderBonusEnabled;
    }

    public function setFirstOrderBonusEnabled(bool $enabled): void
    {
        $this->firstOrderBonusEnabled = $enabled;
    }

    public function getFirstOrderBonusPoints(): int
    {
        return $this->firstOrderBonusPoints;
    }

    public function setFirstOrderBonusPoints(int $points): void
    {
        $this->firstOrderBonusPoints = $points;
    }
}
