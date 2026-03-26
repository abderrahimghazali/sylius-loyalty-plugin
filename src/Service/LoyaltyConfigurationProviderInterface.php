<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;

interface LoyaltyConfigurationProviderInterface
{
    public function getConfiguration(): LoyaltyConfigurationInterface;
}
