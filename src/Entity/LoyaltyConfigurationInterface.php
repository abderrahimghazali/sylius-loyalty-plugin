<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface LoyaltyConfigurationInterface extends ResourceInterface
{
    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;

    public function getPointsPerCurrencyUnit(): int;

    public function setPointsPerCurrencyUnit(int $pointsPerCurrencyUnit): void;

    public function getRedemptionRate(): int;

    public function setRedemptionRate(int $redemptionRate): void;

    public function getExpiryDays(): int;

    public function setExpiryDays(int $expiryDays): void;

    public function isTiersEnabled(): bool;

    public function setTiersEnabled(bool $tiersEnabled): void;

    public function isRegistrationBonusEnabled(): bool;

    public function setRegistrationBonusEnabled(bool $enabled): void;

    public function getRegistrationBonusPoints(): int;

    public function setRegistrationBonusPoints(int $points): void;

    public function isBirthdayBonusEnabled(): bool;

    public function setBirthdayBonusEnabled(bool $enabled): void;

    public function getBirthdayBonusPoints(): int;

    public function setBirthdayBonusPoints(int $points): void;

    public function isFirstOrderBonusEnabled(): bool;

    public function setFirstOrderBonusEnabled(bool $enabled): void;

    public function getFirstOrderBonusPoints(): int;

    public function setFirstOrderBonusPoints(int $points): void;
}
