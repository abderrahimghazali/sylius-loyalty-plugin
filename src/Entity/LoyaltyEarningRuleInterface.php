<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Abderrahim\SyliusLoyaltyPlugin\Enum\EarningRuleScopeType;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface LoyaltyEarningRuleInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getScopeType(): EarningRuleScopeType;

    public function setScopeType(EarningRuleScopeType $scopeType): void;

    public function getTargetId(): int;

    public function setTargetId(int $targetId): void;

    public function getPointsPerCurrencyUnit(): int;

    public function setPointsPerCurrencyUnit(int $pointsPerCurrencyUnit): void;

    public function getPriority(): int;

    public function setPriority(int $priority): void;

    public function getStartsAt(): ?\DateTimeInterface;

    public function setStartsAt(?\DateTimeInterface $startsAt): void;

    public function getEndsAt(): ?\DateTimeInterface;

    public function setEndsAt(?\DateTimeInterface $endsAt): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;
}
