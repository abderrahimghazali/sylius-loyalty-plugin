<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyRuleInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyTier;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyRuleResolverInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\PointsCalculator;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

final class PointsCalculatorTest extends TestCase
{
    private LoyaltyConfigurationProviderInterface&MockObject $configProvider;
    private LoyaltyRuleResolverInterface&MockObject $ruleResolver;

    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(LoyaltyConfigurationProviderInterface::class);
        $this->ruleResolver = $this->createMock(LoyaltyRuleResolverInterface::class);
    }

    public function test_calculate_base_points(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createOrderWithItems([5000]); // one item at €50

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        $this->assertSame(50, $calculator->calculateForOrder($order));
    }

    public function test_calculate_with_higher_rate(): void
    {
        $this->setupConfig(pointsPerUnit: 10);

        $order = $this->createOrderWithItems([5000]);

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        $this->assertSame(500, $calculator->calculateForOrder($order));
    }

    public function test_calculate_with_tier_multiplier(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createOrderWithItems([10000]); // €100

        $tier = new LoyaltyTier();
        $tier->setMultiplier(1.5);

        $account = new LoyaltyAccount();
        $account->setTier($tier);

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        // 100 * 1 * 1.5 = 150
        $this->assertSame(150, $calculator->calculateForOrder($order, $account));
    }

    public function test_calculate_without_account_uses_base_multiplier(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createOrderWithItems([10000]);

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        $this->assertSame(100, $calculator->calculateForOrder($order, null));
    }

    public function test_calculate_with_account_without_tier(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createOrderWithItems([10000]);

        $account = new LoyaltyAccount();

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        $this->assertSame(100, $calculator->calculateForOrder($order, $account));
    }

    public function test_calculate_floors_result(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createOrderWithItems([1550]); // €15.50

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        $this->assertSame(15, $calculator->calculateForOrder($order));
    }

    public function test_zero_total_returns_zero_points(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createOrderWithItems([0]);

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        $this->assertSame(0, $calculator->calculateForOrder($order));
    }

    public function test_multiple_items_summed(): void
    {
        $this->setupConfig(pointsPerUnit: 2);

        $order = $this->createOrderWithItems([3000, 2000]); // €30 + €20

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        // floor(30*2) + floor(20*2) = 60 + 40 = 100
        $this->assertSame(100, $calculator->calculateForOrder($order));
    }

    public function test_earning_rule_overrides_default_rate(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $channel = $this->createMock(ChannelInterface::class);
        $item = $this->createMock(OrderItemInterface::class);
        $item->method('getTotal')->willReturn(5000);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItems')->willReturn(new ArrayCollection([$item]));

        $rule = $this->createMock(LoyaltyRuleInterface::class);
        $rule->method('getPointsPerCurrencyUnit')->willReturn(5);

        $this->ruleResolver->method('resolve')->willReturn($rule);

        $this->configProvider->method('getConfigurationForChannel')->willReturn(
            $this->createConfigMock(1),
        );

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        // 50 * 5 = 250 (rule overrides default rate of 1)
        $this->assertSame(250, $calculator->calculateForOrder($order, null, $channel));
    }

    public function test_earning_rule_zero_excludes_product(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $channel = $this->createMock(ChannelInterface::class);
        $item = $this->createMock(OrderItemInterface::class);
        $item->method('getTotal')->willReturn(5000);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItems')->willReturn(new ArrayCollection([$item]));

        $rule = $this->createMock(LoyaltyRuleInterface::class);
        $rule->method('getPointsPerCurrencyUnit')->willReturn(0);

        $this->ruleResolver->method('resolve')->willReturn($rule);

        $this->configProvider->method('getConfigurationForChannel')->willReturn(
            $this->createConfigMock(1),
        );

        $calculator = new PointsCalculator($this->configProvider, $this->ruleResolver);

        $this->assertSame(0, $calculator->calculateForOrder($order, null, $channel));
    }

    private function setupConfig(int $pointsPerUnit): void
    {
        $config = $this->createConfigMock($pointsPerUnit);
        $this->configProvider->method('getConfiguration')->willReturn($config);
    }

    private function createConfigMock(int $pointsPerUnit): LoyaltyConfigurationInterface&MockObject
    {
        $config = $this->createMock(LoyaltyConfigurationInterface::class);
        $config->method('getPointsPerCurrencyUnit')->willReturn($pointsPerUnit);

        return $config;
    }

    private function createOrderWithItems(array $itemTotals): OrderInterface&MockObject
    {
        $items = [];
        foreach ($itemTotals as $total) {
            $item = $this->createMock(OrderItemInterface::class);
            $item->method('getTotal')->willReturn($total);
            $items[] = $item;
        }

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItems')->willReturn(new ArrayCollection($items));

        return $order;
    }
}
