<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\EventListener\Workflow;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransactionInterface;
use Abderrahim\SyliusLoyaltyPlugin\EventListener\Workflow\RestorePointsOnOrderCancelListener;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;

/** @internal */
interface RestoreTestOrderStub extends OrderInterface, LoyaltyOrderInterface {}

final class RestorePointsOnOrderCancelListenerTest extends TestCase
{
    private LoyaltyAccountRepositoryInterface&MockObject $accountRepository;
    private PointTransactionRepositoryInterface&MockObject $transactionRepository;
    private LoyaltyBalanceManagerInterface&MockObject $balanceManager;
    private EntityManagerInterface&MockObject $entityManager;
    private RestorePointsOnOrderCancelListener $listener;

    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(LoyaltyAccountRepositoryInterface::class);
        $this->transactionRepository = $this->createMock(PointTransactionRepositoryInterface::class);
        $this->balanceManager = $this->createMock(LoyaltyBalanceManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new RestorePointsOnOrderCancelListener(
            $this->accountRepository,
            $this->transactionRepository,
            $this->balanceManager,
            $this->entityManager,
        );
    }

    public function test_restores_redeemed_points_on_cancel(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(RestoreTestOrderStub::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        $redeemTx = $this->createMock(PointTransactionInterface::class);
        $redeemTx->method('getPoints')->willReturn(200);
        $this->transactionRepository->method('findRedeemByOrder')->willReturn($redeemTx);
        $this->transactionRepository->method('findRestoreByOrder')->willReturn(null);

        $this->balanceManager->expects($this->once())->method('addTransaction');
        $this->entityManager->expects($this->once())->method('flush');

        ($this->listener)(new CompletedEvent($order, new Marking()));
    }

    public function test_skips_when_already_restored(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(RestoreTestOrderStub::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        $redeemTx = $this->createMock(PointTransactionInterface::class);
        $this->transactionRepository->method('findRedeemByOrder')->willReturn($redeemTx);
        $this->transactionRepository->method('findRestoreByOrder')
            ->willReturn($this->createMock(PointTransactionInterface::class));

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)(new CompletedEvent($order, new Marking()));
    }

    public function test_skips_when_no_redeem_transaction(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(RestoreTestOrderStub::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $this->accountRepository->method('findOneByCustomer')->willReturn($account);
        $this->transactionRepository->method('findRedeemByOrder')->willReturn(null);

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)(new CompletedEvent($order, new Marking()));
    }

    public function test_skips_when_no_account(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(RestoreTestOrderStub::class);
        $order->method('getCustomer')->willReturn($customer);

        $this->accountRepository->method('findOneByCustomer')->willReturn(null);

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)(new CompletedEvent($order, new Marking()));
    }

    public function test_skips_guest_order(): void
    {
        $order = $this->createMock(RestoreTestOrderStub::class);
        $order->method('getCustomer')->willReturn(null);

        $this->accountRepository->expects($this->never())->method('findOneByCustomer');

        ($this->listener)(new CompletedEvent($order, new Marking()));
    }
}
