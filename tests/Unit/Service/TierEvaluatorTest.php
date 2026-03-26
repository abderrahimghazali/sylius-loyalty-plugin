<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyTier;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\TierEvaluator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class TierEvaluatorTest extends TestCase
{
    private RepositoryInterface&MockObject $tierRepository;
    private LoyaltyConfigurationProviderInterface&MockObject $configProvider;
    private TierEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->tierRepository = $this->createMock(RepositoryInterface::class);
        $this->configProvider = $this->createMock(LoyaltyConfigurationProviderInterface::class);
        $this->evaluator = new TierEvaluator($this->tierRepository, $this->configProvider);
    }

    public function test_does_nothing_when_tiers_disabled(): void
    {
        $this->setupConfig(tiersEnabled: false);

        $account = new LoyaltyAccount();
        $account->creditPoints(1000);

        $this->tierRepository->expects($this->never())->method('findBy');

        $this->evaluator->evaluate($account);

        $this->assertNull($account->getTier());
    }

    public function test_assigns_tier_based_on_lifetime_points(): void
    {
        $this->setupConfig(tiersEnabled: true);

        $bronze = $this->createTier('bronze', 0);
        $silver = $this->createTier('silver', 500);
        $gold = $this->createTier('gold', 1000);

        $this->tierRepository->method('findBy')->willReturn([$gold, $silver, $bronze]);

        $account = new LoyaltyAccount();
        $account->creditPoints(600);

        $this->evaluator->evaluate($account);

        $this->assertSame($silver, $account->getTier());
    }

    public function test_assigns_highest_qualifying_tier(): void
    {
        $this->setupConfig(tiersEnabled: true);

        $bronze = $this->createTier('bronze', 0);
        $silver = $this->createTier('silver', 500);
        $gold = $this->createTier('gold', 1000);

        $this->tierRepository->method('findBy')->willReturn([$gold, $silver, $bronze]);

        $account = new LoyaltyAccount();
        $account->creditPoints(1500);

        $this->evaluator->evaluate($account);

        $this->assertSame($gold, $account->getTier());
    }

    public function test_never_downgrades_tier(): void
    {
        $this->setupConfig(tiersEnabled: true);

        $bronze = $this->createTier('bronze', 0);
        $silver = $this->createTier('silver', 500);
        $gold = $this->createTier('gold', 1000);

        $this->tierRepository->method('findBy')->willReturn([$gold, $silver, $bronze]);

        $account = new LoyaltyAccount();
        $account->setTier($gold);
        $account->creditPoints(600); // Lifetime qualifies for silver, but already gold

        $this->evaluator->evaluate($account);

        $this->assertSame($gold, $account->getTier());
    }

    public function test_assigns_no_tier_when_below_all_thresholds(): void
    {
        $this->setupConfig(tiersEnabled: true);

        $silver = $this->createTier('silver', 500);
        $gold = $this->createTier('gold', 1000);

        $this->tierRepository->method('findBy')->willReturn([$gold, $silver]);

        $account = new LoyaltyAccount();
        $account->creditPoints(100);

        $this->evaluator->evaluate($account);

        $this->assertNull($account->getTier());
    }

    public function test_upgrades_from_lower_to_higher_tier(): void
    {
        $this->setupConfig(tiersEnabled: true);

        $bronze = $this->createTier('bronze', 0);
        $silver = $this->createTier('silver', 500);
        $gold = $this->createTier('gold', 1000);

        $this->tierRepository->method('findBy')->willReturn([$gold, $silver, $bronze]);

        $account = new LoyaltyAccount();
        $account->setTier($silver);
        $account->creditPoints(1200);

        $this->evaluator->evaluate($account);

        $this->assertSame($gold, $account->getTier());
    }

    private function setupConfig(bool $tiersEnabled): void
    {
        $config = $this->createMock(LoyaltyConfigurationInterface::class);
        $config->method('isTiersEnabled')->willReturn($tiersEnabled);
        $this->configProvider->method('getConfiguration')->willReturn($config);
    }

    private function createTier(string $code, int $minPoints): LoyaltyTier
    {
        $tier = new LoyaltyTier();
        $tier->setCode($code);
        $tier->setMinPoints($minPoints);

        return $tier;
    }
}
