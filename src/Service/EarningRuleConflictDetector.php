<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyEarningRuleRepositoryInterface;

/**
 * Detects earning rules that share target codes at the same scope level.
 * Rules at different scope levels (variant vs product vs taxon) are not
 * conflicts — specificity resolves them deterministically.
 */
final class EarningRuleConflictDetector
{
    public function __construct(
        private readonly LoyaltyEarningRuleRepositoryInterface $earningRuleRepository,
    ) {
    }

    /**
     * Find rules that conflict with the given rule (same scope, overlapping targets, same channel).
     *
     * @return LoyaltyEarningRuleInterface[]
     */
    public function findConflicts(LoyaltyEarningRuleInterface $rule): array
    {
        $channel = $rule->getChannel();
        if ($channel === null) {
            return [];
        }

        $allRules = $this->earningRuleRepository->findActiveRulesForChannel($channel);
        $conflicts = [];

        $ruleCodes = $rule->getTargetCodes();
        $ruleScope = $rule->getScopeType();

        if (count($ruleCodes) === 0) {
            return [];
        }

        foreach ($allRules as $other) {
            // Don't compare with itself
            if ($other->getId() === $rule->getId()) {
                continue;
            }

            // Only same scope level is a conflict
            if ($other->getScopeType() !== $ruleScope) {
                continue;
            }

            // Check for overlapping target codes
            $overlap = array_intersect($ruleCodes, $other->getTargetCodes());
            if (count($overlap) > 0) {
                $conflicts[] = $other;
            }
        }

        return $conflicts;
    }

    /**
     * Get the overlapping codes between two rules.
     *
     * @return string[]
     */
    public function getOverlappingCodes(LoyaltyEarningRuleInterface $a, LoyaltyEarningRuleInterface $b): array
    {
        return array_values(array_intersect($a->getTargetCodes(), $b->getTargetCodes()));
    }
}
