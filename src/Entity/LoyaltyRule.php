<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;

class LoyaltyRule implements LoyaltyRuleInterface
{
    protected ?int $id = null;

    protected ?string $name = null;

    protected bool $enabled = true;

    protected int $pointsPerCurrencyUnit = 1;

    /** @var Collection<int, ChannelInterface> */
    protected Collection $channels;

    /** @var Collection<int, ProductInterface> */
    protected Collection $products;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
        $this->products = new ArrayCollection();
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

    public function getPointsPerCurrencyUnit(): int
    {
        return $this->pointsPerCurrencyUnit;
    }

    public function setPointsPerCurrencyUnit(int $pointsPerCurrencyUnit): void
    {
        $this->pointsPerCurrencyUnit = $pointsPerCurrencyUnit;
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

    /** @return Collection<int, ProductInterface> */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(ProductInterface $product): void
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }
    }

    public function removeProduct(ProductInterface $product): void
    {
        $this->products->removeElement($product);
    }

    public function hasProduct(ProductInterface $product): bool
    {
        return $this->products->contains($product);
    }
}
