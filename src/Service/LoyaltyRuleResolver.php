<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyRuleInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyRuleRepositoryInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class LoyaltyRuleResolver implements LoyaltyRuleResolverInterface
{
    /** @var array<string, LoyaltyRuleInterface[]> */
    private array $cache = [];

    public function __construct(
        private readonly LoyaltyRuleRepositoryInterface $ruleRepository,
    ) {
    }

    public function resolve(OrderItemInterface $item, ChannelInterface $channel): ?LoyaltyRuleInterface
    {
        $product = $item->getVariant()?->getProduct();
        if (!$product instanceof ProductInterface) {
            return null;
        }

        $rules = $this->getRulesForChannel($channel);

        foreach ($rules as $rule) {
            if ($rule->hasProduct($product)) {
                return $rule;
            }
        }

        return null;
    }

    /** @return LoyaltyRuleInterface[] */
    private function getRulesForChannel(ChannelInterface $channel): array
    {
        $code = $channel->getCode() ?? '__default__';

        if (!isset($this->cache[$code])) {
            $this->cache[$code] = $this->ruleRepository->findActiveRulesForChannel($channel);
        }

        return $this->cache[$code];
    }
}
