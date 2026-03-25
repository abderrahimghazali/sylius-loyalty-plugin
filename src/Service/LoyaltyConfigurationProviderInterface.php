<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfigurationInterface;

interface LoyaltyConfigurationProviderInterface
{
    public function getConfiguration(): LoyaltyConfigurationInterface;
}
