<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\EventListener;

use Abderrahim\SyliusLoyaltyPlugin\EventListener\OrderCompleteListener;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class OrderCompleteListenerTest extends TestCase
{
    private LoyaltyBalanceManagerInterface&MockObject $balanceManager;
    private EntityManagerInterface&MockObject $entityManager;
    private OrderCompleteListener $listener;

    protected function setUp(): void
    {
        $this->balanceManager = $this->createMock(LoyaltyBalanceManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new OrderCompleteListener($this->balanceManager, $this->entityManager);
    }

    public function test_awards_points_for_completed_order(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $this->balanceManager->expects($this->once())->method('awardPointsForOrder')->with($order);
        $this->entityManager->expects($this->once())->method('flush');

        ($this->listener)(new GenericEvent($order));
    }

    public function test_skips_non_order_subject(): void
    {
        $this->balanceManager->expects($this->never())->method('awardPointsForOrder');

        ($this->listener)(new GenericEvent(new \stdClass()));
    }
}
