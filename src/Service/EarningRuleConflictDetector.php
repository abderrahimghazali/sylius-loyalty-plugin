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
        $channels = $rule->getChannels();
        if ($channels->isEmpty()) {
            return [];
        }

        $ruleCodes = $rule->getTargetCodes();
        $ruleScope = $rule->getScopeType();

        if (count($ruleCodes) === 0) {
            return [];
        }

        $conflicts = [];
        $seen = [];

        foreach ($channels as $channel) {
            $allRules = $this->earningRuleRepository->findActiveRulesForChannel($channel);

            foreach ($allRules as $other) {
                if ($other->getId() === $rule->getId()) {
                    continue;
                }

                if (isset($seen[$other->getId()])) {
                    continue;
                }

                if ($other->getScopeType() !== $ruleScope) {
                    continue;
                }

                $overlap = array_intersect($ruleCodes, $other->getTargetCodes());
                if (count($overlap) > 0) {
                    $conflicts[] = $other;
                    $seen[$other->getId()] = true;
                }
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
