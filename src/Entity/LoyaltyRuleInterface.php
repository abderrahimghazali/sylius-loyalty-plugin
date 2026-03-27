<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface LoyaltyRuleInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getPointsPerCurrencyUnit(): int;

    public function setPointsPerCurrencyUnit(int $pointsPerCurrencyUnit): void;

    /** @return Collection<int, ChannelInterface> */
    public function getChannels(): Collection;

    public function addChannel(ChannelInterface $channel): void;

    public function removeChannel(ChannelInterface $channel): void;

    public function hasChannel(ChannelInterface $channel): bool;

    /** @return Collection<int, ProductInterface> */
    public function getProducts(): Collection;

    public function addProduct(ProductInterface $product): void;

    public function removeProduct(ProductInterface $product): void;

    public function hasProduct(ProductInterface $product): bool;
}
