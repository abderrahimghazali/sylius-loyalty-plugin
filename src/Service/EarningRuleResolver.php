<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyEarningRuleRepositoryInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

final class EarningRuleResolver implements EarningRuleResolverInterface
{
    /** @var array<string, int|null> */
    private array $cache = [];

    public function __construct(
        private readonly LoyaltyEarningRuleRepositoryInterface $earningRuleRepository,
    ) {
    }

    public function getPointsRateForChannel(ChannelInterface $channel): ?int
    {
        $code = $channel->getCode() ?? '__default__';

        if (array_key_exists($code, $this->cache)) {
            return $this->cache[$code];
        }

        $rules = $this->earningRuleRepository->findEnabledRulesForChannel($channel);

        if (count($rules) === 0) {
            $this->cache[$code] = null;

            return null;
        }

        // Use the first enabled rule's configuration for this channel
        $rule = $rules[0];
        $config = $rule->getConfigurationForChannel($channel);
        $rate = $config?->getPointsPerProduct();

        $this->cache[$code] = $rate;

        return $rate;
    }
}
