<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\EarningRuleScopeType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyEarningRuleRepositoryInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;

final class EarningRuleResolver implements EarningRuleResolverInterface
{
    /** @var array<string, LoyaltyEarningRuleInterface[]> */
    private array $cache = [];

    public function __construct(
        private readonly LoyaltyEarningRuleRepositoryInterface $earningRuleRepository,
    ) {
    }

    public function resolve(OrderItemInterface $item, ChannelInterface $channel): ?LoyaltyEarningRuleInterface
    {
        $rules = $this->getRulesForChannel($channel);

        if (count($rules) === 0) {
            return null;
        }

        $variant = $item->getVariant();
        $product = $variant?->getProduct();

        $bestRule = null;
        $bestSpecificity = -1;

        foreach ($rules as $rule) {
            $scope = $rule->getScopeType();
            $targetId = $rule->getTargetId();

            $matches = match ($scope) {
                EarningRuleScopeType::Variant => $variant !== null && $variant->getId() === $targetId,
                EarningRuleScopeType::Product => $product !== null && $product->getId() === $targetId,
                EarningRuleScopeType::Taxon => $product instanceof ProductInterface && $this->productMatchesTaxon($product, $targetId),
            };

            if (!$matches) {
                continue;
            }

            $specificity = $scope->specificity();

            // Higher specificity wins; within same specificity, higher priority wins
            if (
                $specificity > $bestSpecificity
                || ($specificity === $bestSpecificity && ($bestRule === null || $rule->getPriority() > $bestRule->getPriority()))
            ) {
                $bestRule = $rule;
                $bestSpecificity = $specificity;
            }
        }

        return $bestRule;
    }

    private function productMatchesTaxon(ProductInterface $product, int $taxonId): bool
    {
        foreach ($product->getTaxons() as $taxon) {
            // Check the taxon itself and all ancestors
            $current = $taxon;
            while ($current !== null) {
                if ($current->getId() === $taxonId) {
                    return true;
                }
                $current = $current instanceof TaxonInterface ? $current->getParent() : null;
            }
        }

        return false;
    }

    /** @return LoyaltyEarningRuleInterface[] */
    private function getRulesForChannel(ChannelInterface $channel): array
    {
        $code = $channel->getCode() ?? '__default__';

        if (!isset($this->cache[$code])) {
            $this->cache[$code] = $this->earningRuleRepository->findActiveRulesForChannel($channel);
        }

        return $this->cache[$code];
    }
}
