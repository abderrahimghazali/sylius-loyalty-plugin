<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\EventListener;

use Abderrahim\SyliusLoyaltyPlugin\EventListener\OrderCancelListener;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class OrderCancelListenerTest extends TestCase
{
    private LoyaltyBalanceManagerInterface&MockObject $balanceManager;
    private EntityManagerInterface&MockObject $entityManager;
    private OrderCancelListener $listener;

    protected function setUp(): void
    {
        $this->balanceManager = $this->createMock(LoyaltyBalanceManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new OrderCancelListener($this->balanceManager, $this->entityManager);
    }

    public function test_revokes_points_for_cancelled_order(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $this->balanceManager->expects($this->once())->method('revokePointsForOrder')->with($order);
        $this->entityManager->expects($this->once())->method('flush');

        ($this->listener)(new GenericEvent($order));
    }

    public function test_skips_non_order_subject(): void
    {
        $this->balanceManager->expects($this->never())->method('revokePointsForOrder');

        ($this->listener)(new GenericEvent(new \stdClass()));
    }
}
