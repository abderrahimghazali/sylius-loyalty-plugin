<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Sylius\Component\Channel\Model\ChannelInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['channel'], message: 'A configuration for this channel already exists.')]
class LoyaltyConfiguration implements LoyaltyConfigurationInterface
{
    protected ?int $id = null;

    protected ?ChannelInterface $channel = null;

    protected int $pointsPerCurrencyUnit = 1;

    protected int $redemptionRate = 100;

    protected int $expiryDays = 365;

    protected bool $tiersEnabled = true;

    protected bool $registrationBonusEnabled = true;

    protected int $registrationBonusPoints = 100;

    protected bool $birthdayBonusEnabled = true;

    protected int $birthdayBonusPoints = 200;

    protected bool $firstOrderBonusEnabled = true;

    protected int $firstOrderBonusPoints = 50;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
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

    public function getExpiryDays(): int
    {
        return $this->expiryDays;
    }

    public function setExpiryDays(int $expiryDays): void
    {
        $this->expiryDays = $expiryDays;
    }

    public function isTiersEnabled(): bool
    {
        return $this->tiersEnabled;
    }

    public function setTiersEnabled(bool $tiersEnabled): void
    {
        $this->tiersEnabled = $tiersEnabled;
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
