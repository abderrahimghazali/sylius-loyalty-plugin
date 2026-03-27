<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;

class LoyaltyEarningRule implements LoyaltyEarningRuleInterface
{
    protected ?int $id = null;

    protected ?string $name = null;

    protected bool $enabled = true;

    /** @var Collection<int, LoyaltyEarningRuleChannelConfigurationInterface> */
    protected Collection $channelConfigurations;

    public function __construct()
    {
        $this->channelConfigurations = new ArrayCollection();
    }

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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /** @return Collection<int, LoyaltyEarningRuleChannelConfigurationInterface> */
    public function getChannelConfigurations(): Collection
    {
        return $this->channelConfigurations;
    }

    public function addChannelConfiguration(LoyaltyEarningRuleChannelConfigurationInterface $configuration): void
    {
        if (!$this->hasChannelConfiguration($configuration)) {
            $configuration->setEarningRule($this);
            $this->channelConfigurations->add($configuration);
        }
    }

    public function removeChannelConfiguration(LoyaltyEarningRuleChannelConfigurationInterface $configuration): void
    {
        if ($this->hasChannelConfiguration($configuration)) {
            $configuration->setEarningRule(null);
            $this->channelConfigurations->removeElement($configuration);
        }
    }

    public function hasChannelConfiguration(LoyaltyEarningRuleChannelConfigurationInterface $configuration): bool
    {
        return $this->channelConfigurations->contains($configuration);
    }

    public function getConfigurationForChannel(ChannelInterface $channel): ?LoyaltyEarningRuleChannelConfigurationInterface
    {
        foreach ($this->channelConfigurations as $configuration) {
            if ($configuration->getChannel() === $channel) {
                return $configuration;
            }
        }

        return null;
    }
}
