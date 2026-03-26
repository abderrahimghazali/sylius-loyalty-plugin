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
            $codes = $rule->getTargetCodes();

            if (count($codes) === 0) {
                continue;
            }

            $matches = match ($scope) {
                EarningRuleScopeType::Variant => $variant !== null && in_array($variant->getCode(), $codes, true),
                EarningRuleScopeType::Product => $product !== null && in_array($product->getCode(), $codes, true),
                EarningRuleScopeType::Taxon => $product instanceof ProductInterface && $this->productMatchesTaxonCodes($product, $codes),
            };

            if (!$matches) {
                continue;
            }

            $specificity = $scope->specificity();

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

    /**
     * Check if any of the product's taxons (or their ancestors) match the given codes.
     *
     * @param string[] $taxonCodes
     */
    private function productMatchesTaxonCodes(ProductInterface $product, array $taxonCodes): bool
    {
        foreach ($product->getTaxons() as $taxon) {
            $current = $taxon;
            while ($current !== null) {
                if (in_array($current->getCode(), $taxonCodes, true)) {
                    return true;
                }
                $parent = $current->getParent();
                $current = $parent instanceof TaxonInterface ? $parent : null;
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
