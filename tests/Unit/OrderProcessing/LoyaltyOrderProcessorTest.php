<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\OrderProcessing;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Model\AdjustmentTypes;
use Abderrahim\SyliusLoyaltyPlugin\OrderProcessing\LoyaltyOrderProcessor;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class LoyaltyOrderProcessorTest extends TestCase
{
    private LoyaltyAccountRepositoryInterface&MockObject $accountRepository;
    private FactoryInterface&MockObject $adjustmentFactory;
    private LoyaltyConfigurationProviderInterface&MockObject $configProvider;
    private LoyaltyOrderProcessor $processor;

    private const REDEMPTION_RATE = 100; // 100 points = 1 currency unit

    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(LoyaltyAccountRepositoryInterface::class);
        $this->adjustmentFactory = $this->createMock(FactoryInterface::class);
        $this->configProvider = $this->createMock(LoyaltyConfigurationProviderInterface::class);

        $config = $this->createMock(LoyaltyConfigurationInterface::class);
        $config->method('getRedemptionRate')->willReturn(self::REDEMPTION_RATE);
        $this->configProvider->method('getConfiguration')->willReturn($config);

        $this->processor = new LoyaltyOrderProcessor(
            $this->accountRepository,
            $this->adjustmentFactory,
            $this->configProvider,
        );
    }

    public function test_it_skips_orders_without_loyalty_interface(): void
    {
        $order = $this->createMock(OrderInterface::class);

        // Should not throw, just return early
        $order->expects($this->never())->method('getCustomer');

        $this->processor->process($order);
    }

    public function test_it_clears_existing_loyalty_adjustments(): void
    {
        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(0);

        $existingAdjustment = $this->createMock(AdjustmentInterface::class);
        $order->method('getAdjustments')
            ->with(AdjustmentTypes::LOYALTY_POINTS_DISCOUNT)
            ->willReturn(new ArrayCollection([$existingAdjustment]));

        $order->expects($this->once())
            ->method('removeAdjustment')
            ->with($existingAdjustment);

        $this->processor->process($order);
    }

    public function test_it_does_nothing_when_points_to_redeem_is_zero(): void
    {
        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(0);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());

        $order->expects($this->never())->method('addAdjustment');

        $this->processor->process($order);
    }

    public function test_it_resets_points_for_guest_orders(): void
    {
        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(500);
        $order->method('getCustomer')->willReturn(null);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());

        $order->expects($this->once())
            ->method('setPointsToRedeem')
            ->with(0);

        $order->expects($this->never())->method('addAdjustment');

        $this->processor->process($order);
    }

    public function test_it_resets_points_when_no_loyalty_account_exists(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(500);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());

        $this->accountRepository->method('findOneByCustomer')
            ->with($customer)
            ->willReturn(null);

        $order->expects($this->once())
            ->method('setPointsToRedeem')
            ->with(0);

        $this->processor->process($order);
    }

    public function test_it_resets_points_when_account_is_disabled(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $account = $this->createLoyaltyAccount(pointsBalance: 1000, enabled: false);
        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(500);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());

        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        $order->expects($this->once())
            ->method('setPointsToRedeem')
            ->with(0);

        $this->processor->process($order);
    }

    public function test_it_clamps_points_to_available_balance(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $account = $this->createLoyaltyAccount(pointsBalance: 200);

        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(500); // Requesting more than available
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());
        $order->method('getTotal')->willReturn(5000); // €50.00

        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        // 200 points / 100 rate = €2.00 = 200 cents
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $this->adjustmentFactory->method('createNew')->willReturn($adjustment);

        $adjustment->expects($this->once())->method('setAmount')->with(-200);
        $adjustment->expects($this->once())->method('setType')->with(AdjustmentTypes::LOYALTY_POINTS_DISCOUNT);
        $adjustment->expects($this->once())->method('setNeutral')->with(false);

        $order->expects($this->once())
            ->method('setPointsToRedeem')
            ->with(200); // Clamped to available

        $order->expects($this->once())
            ->method('addAdjustment')
            ->with($adjustment);

        $this->processor->process($order);
    }

    public function test_it_applies_correct_discount_amount(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $account = $this->createLoyaltyAccount(pointsBalance: 1000);

        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(500);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());
        $order->method('getTotal')->willReturn(10000); // €100.00

        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        // 500 points / 100 rate = €5.00 = 500 cents
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $this->adjustmentFactory->method('createNew')->willReturn($adjustment);

        $adjustment->expects($this->once())->method('setAmount')->with(-500);

        $order->expects($this->once())->method('addAdjustment')->with($adjustment);

        $this->processor->process($order);
    }

    public function test_it_caps_discount_at_order_total(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $account = $this->createLoyaltyAccount(pointsBalance: 50000); // 50,000 points = €500

        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(50000);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());
        $order->method('getTotal')->willReturn(1500); // €15.00 — much less than discount

        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        $adjustment = $this->createMock(AdjustmentInterface::class);
        $this->adjustmentFactory->method('createNew')->willReturn($adjustment);

        // Discount capped at order total: 1500 cents = €15.00
        $adjustment->expects($this->once())->method('setAmount')->with(-1500);

        // Points recalculated: ceil((1500 / 100) * 100) = 1500 points
        $order->expects($this->once())
            ->method('setPointsToRedeem')
            ->with(1500);

        $order->expects($this->once())->method('addAdjustment')->with($adjustment);

        $this->processor->process($order);
    }

    public function test_it_resets_when_balance_is_zero(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $account = $this->createLoyaltyAccount(pointsBalance: 0);

        $order = $this->createLoyaltyOrder();
        $order->method('getPointsToRedeem')->willReturn(500);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());

        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        $order->expects($this->once())
            ->method('setPointsToRedeem')
            ->with(0);

        $order->expects($this->never())->method('addAdjustment');

        $this->processor->process($order);
    }

    // --- Helpers ---

    private function createLoyaltyOrder(): OrderInterface&LoyaltyOrderInterface&MockObject
    {
        return $this->createMock(LoyaltyOrderStub::class);
    }

    private function createLoyaltyAccount(int $pointsBalance = 0, bool $enabled = true): LoyaltyAccountInterface
    {
        $account = new LoyaltyAccount();
        $account->setPointsBalance($pointsBalance);
        $account->setEnabled($enabled);

        return $account;
    }
}

/**
 * Stub interface combining both OrderInterface and LoyaltyOrderInterface for mocking.
 *
 * @internal
 */
interface LoyaltyOrderStub extends OrderInterface, LoyaltyOrderInterface
{
}
