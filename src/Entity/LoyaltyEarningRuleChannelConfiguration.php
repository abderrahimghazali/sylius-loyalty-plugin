<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Sylius\Component\Channel\Model\ChannelInterface;

class LoyaltyEarningRuleChannelConfiguration implements LoyaltyEarningRuleChannelConfigurationInterface
{
    protected ?int $id = null;

    protected ?LoyaltyEarningRuleInterface $earningRule = null;

    protected ?ChannelInterface $channel = null;

    protected int $pointsPerProduct = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEarningRule(): ?LoyaltyEarningRuleInterface
    {
        return $this->earningRule;
    }

    public function setEarningRule(?LoyaltyEarningRuleInterface $earningRule): void
    {
        $this->earningRule = $earningRule;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    public function getPointsPerProduct(): int
    {
        return $this->pointsPerProduct;
    }

    public function setPointsPerProduct(int $pointsPerProduct): void
    {
        $this->pointsPerProduct = $pointsPerProduct;
    }
}
