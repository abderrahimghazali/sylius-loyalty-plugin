<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Sylius\Component\Channel\Model\ChannelInterface;

interface LoyaltyEarningRuleChannelConfigurationInterface
{
    public function getId(): ?int;

    public function getEarningRule(): ?LoyaltyEarningRuleInterface;

    public function setEarningRule(?LoyaltyEarningRuleInterface $earningRule): void;

    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;

    public function getPointsPerProduct(): int;

    public function setPointsPerProduct(int $pointsPerProduct): void;
}
