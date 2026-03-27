<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Abderrahim\SyliusLoyaltyPlugin\Enum\EarningRuleScopeType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;

class LoyaltyEarningRule implements LoyaltyEarningRuleInterface
{
    protected ?int $id = null;

    protected ?string $name = null;

    protected string $scopeType = 'taxon';

    /** @var string[] */
    protected array $targetCodes = [];

    protected int $pointsPerCurrencyUnit = 1;

    protected int $priority = 0;

    protected ?\DateTimeInterface $startsAt = null;

    protected ?\DateTimeInterface $endsAt = null;

    protected bool $enabled = true;

    /** @var Collection<int, ChannelInterface> */
    protected Collection $channels;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
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

    public function getScopeType(): EarningRuleScopeType
    {
        return EarningRuleScopeType::from($this->scopeType);
    }

    public function setScopeType(EarningRuleScopeType $scopeType): void
    {
        $this->scopeType = $scopeType->value;
    }

    /** @return string[] */
    public function getTargetCodes(): array
    {
        return $this->targetCodes;
    }

    /** @param string[] $targetCodes */
    public function setTargetCodes(array $targetCodes): void
    {
        $this->targetCodes = $targetCodes;
    }

    public function getPointsPerCurrencyUnit(): int
    {
        return $this->pointsPerCurrencyUnit;
    }

    public function setPointsPerCurrencyUnit(int $pointsPerCurrencyUnit): void
    {
        $this->pointsPerCurrencyUnit = $pointsPerCurrencyUnit;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeInterface $startsAt): void
    {
        $this->startsAt = $startsAt;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeInterface $endsAt): void
    {
        $this->endsAt = $endsAt;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /** @return Collection<int, ChannelInterface> */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(ChannelInterface $channel): void
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }
    }

    public function removeChannel(ChannelInterface $channel): void
    {
        $this->channels->removeElement($channel);
    }

    public function hasChannel(ChannelInterface $channel): bool
    {
        return $this->channels->contains($channel);
    }
}
