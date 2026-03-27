<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface LoyaltyEarningRuleInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    /** @return Collection<int, LoyaltyEarningRuleChannelConfigurationInterface> */
    public function getChannelConfigurations(): Collection;

    public function addChannelConfiguration(LoyaltyEarningRuleChannelConfigurationInterface $configuration): void;

    public function removeChannelConfiguration(LoyaltyEarningRuleChannelConfigurationInterface $configuration): void;

    public function hasChannelConfiguration(LoyaltyEarningRuleChannelConfigurationInterface $configuration): bool;

    public function getConfigurationForChannel(ChannelInterface $channel): ?LoyaltyEarningRuleChannelConfigurationInterface;
}
