<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyTier;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\PointsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;

final class PointsCalculatorTest extends TestCase
{
    private LoyaltyConfigurationProviderInterface&MockObject $configProvider;

    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(LoyaltyConfigurationProviderInterface::class);
    }

    public function test_calculate_base_points(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItemsTotal')->willReturn(5000); // €50.00

        $calculator = new PointsCalculator($this->configProvider);

        $this->assertSame(50, $calculator->calculateForOrder($order));
    }

    public function test_calculate_with_higher_rate(): void
    {
        $this->setupConfig(pointsPerUnit: 10);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItemsTotal')->willReturn(5000);

        $calculator = new PointsCalculator($this->configProvider);

        $this->assertSame(500, $calculator->calculateForOrder($order));
    }

    public function test_calculate_with_tier_multiplier(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItemsTotal')->willReturn(10000); // €100.00

        $tier = new LoyaltyTier();
        $tier->setMultiplier(1.5);

        $account = new LoyaltyAccount();
        $account->setTier($tier);

        $calculator = new PointsCalculator($this->configProvider);

        // 100 * 1 * 1.5 = 150
        $this->assertSame(150, $calculator->calculateForOrder($order, $account));
    }

    public function test_calculate_without_account_uses_base_multiplier(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItemsTotal')->willReturn(10000);

        $calculator = new PointsCalculator($this->configProvider);

        $this->assertSame(100, $calculator->calculateForOrder($order, null));
    }

    public function test_calculate_with_account_without_tier(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItemsTotal')->willReturn(10000);

        $account = new LoyaltyAccount();

        $calculator = new PointsCalculator($this->configProvider);

        $this->assertSame(100, $calculator->calculateForOrder($order, $account));
    }

    public function test_calculate_floors_result(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItemsTotal')->willReturn(1550); // €15.50

        $calculator = new PointsCalculator($this->configProvider);

        $this->assertSame(15, $calculator->calculateForOrder($order));
    }

    public function test_zero_total_returns_zero_points(): void
    {
        $this->setupConfig(pointsPerUnit: 1);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getItemsTotal')->willReturn(0);

        $calculator = new PointsCalculator($this->configProvider);

        $this->assertSame(0, $calculator->calculateForOrder($order));
    }

    private function setupConfig(int $pointsPerUnit): void
    {
        $config = $this->createMock(LoyaltyConfigurationInterface::class);
        $config->method('getPointsPerCurrencyUnit')->willReturn($pointsPerUnit);
        $this->configProvider->method('getConfiguration')->willReturn($config);
    }
}
