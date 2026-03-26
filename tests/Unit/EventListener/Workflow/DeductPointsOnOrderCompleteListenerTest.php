<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\EventListener\Workflow;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\EventListener\Workflow\DeductPointsOnOrderCompleteListener;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;

/** @internal */
interface DeductTestOrderStub extends OrderInterface, LoyaltyOrderInterface {}

final class DeductPointsOnOrderCompleteListenerTest extends TestCase
{
    private LoyaltyBalanceManagerInterface&MockObject $balanceManager;
    private EntityManagerInterface&MockObject $entityManager;
    private DeductPointsOnOrderCompleteListener $listener;

    protected function setUp(): void
    {
        $this->balanceManager = $this->createMock(LoyaltyBalanceManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new DeductPointsOnOrderCompleteListener(
            $this->balanceManager,
            $this->entityManager,
        );
    }

    public function test_deducts_redeemed_points(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(DeductTestOrderStub::class);
        $order->method('getPointsToRedeem')->willReturn(200);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $account->creditPoints(500);

        $this->balanceManager->method('getOrCreateAccount')->willReturn($account);
        $this->entityManager->method('find')->willReturn($account);

        $this->balanceManager->expects($this->once())->method('addTransaction');
        $this->entityManager->expects($this->once())->method('flush');

        ($this->listener)($this->createCompletedEvent($order));
    }

    public function test_skips_when_no_points_to_redeem(): void
    {
        $order = $this->createMock(DeductTestOrderStub::class);
        $order->method('getPointsToRedeem')->willReturn(0);

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)($this->createCompletedEvent($order));
    }

    public function test_skips_guest_orders(): void
    {
        $order = $this->createMock(DeductTestOrderStub::class);
        $order->method('getPointsToRedeem')->willReturn(100);
        $order->method('getCustomer')->willReturn(null);

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)($this->createCompletedEvent($order));
    }

    public function test_skips_non_loyalty_orders(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $this->balanceManager->expects($this->never())->method('getOrCreateAccount');

        ($this->listener)($this->createCompletedEvent($order));
    }

    public function test_clamps_to_available_balance(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(DeductTestOrderStub::class);
        $order->method('getPointsToRedeem')->willReturn(500);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $account->creditPoints(100); // Only 100 available

        $this->balanceManager->method('getOrCreateAccount')->willReturn($account);
        $this->entityManager->method('find')->willReturn($account);

        $this->balanceManager->expects($this->once())
            ->method('addTransaction')
            ->with($account, $this->anything(), 100, $this->anything(), $order);

        ($this->listener)($this->createCompletedEvent($order));
    }

    public function test_skips_when_balance_is_zero(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(DeductTestOrderStub::class);
        $order->method('getPointsToRedeem')->willReturn(100);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount(); // 0 balance

        $this->balanceManager->method('getOrCreateAccount')->willReturn($account);
        $this->entityManager->method('find')->willReturn($account);

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)($this->createCompletedEvent($order));
    }

    private function createCompletedEvent(object $subject): CompletedEvent
    {
        return new CompletedEvent($subject, new Marking());
    }
}
