<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Twig;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\EarningRuleConflictDetector;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EarningRuleConflictExtension extends AbstractExtension
{
    public function __construct(
        private readonly EarningRuleConflictDetector $conflictDetector,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('loyalty_earning_rule_conflicts', $this->getConflicts(...)),
        ];
    }

    /**
     * @return array<array{rule: LoyaltyEarningRuleInterface, overlap: string[]}>
     */
    public function getConflicts(LoyaltyEarningRuleInterface $rule): array
    {
        $conflicts = $this->conflictDetector->findConflicts($rule);
        $result = [];

        foreach ($conflicts as $conflicting) {
            $result[] = [
                'rule' => $conflicting,
                'overlap' => $this->conflictDetector->getOverlappingCodes($rule, $conflicting),
            ];
        }

        return $result;
    }
}
