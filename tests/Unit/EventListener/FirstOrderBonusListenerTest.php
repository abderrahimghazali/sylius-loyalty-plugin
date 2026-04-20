<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\EventListener;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransactionInterface;
use Abderrahim\SyliusLoyaltyPlugin\EventListener\FirstOrderBonusListener;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class FirstOrderBonusListenerTest extends TestCase
{
    private LoyaltyBalanceManagerInterface&MockObject $balanceManager;
    private LoyaltyConfigurationProviderInterface&MockObject $configProvider;
    private OrderRepositoryInterface&MockObject $orderRepository;
    private PointTransactionRepositoryInterface&MockObject $transactionRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private FirstOrderBonusListener $listener;

    protected function setUp(): void
    {
        $this->balanceManager = $this->createMock(LoyaltyBalanceManagerInterface::class);
        $this->configProvider = $this->createMock(LoyaltyConfigurationProviderInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->transactionRepository = $this->createMock(PointTransactionRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new FirstOrderBonusListener(
            $this->balanceManager,
            $this->configProvider,
            $this->orderRepository,
            $this->transactionRepository,
            $this->entityManager,
        );
    }

    public function test_awards_first_order_bonus(): void
    {
        $this->setupConfig(enabled: true, points: 50);

        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $ref = new \ReflectionProperty(LoyaltyAccount::class, 'id');
        $ref->setValue($account, 1);
        $this->balanceManager->method('getOrCreateAccount')->willReturn($account);
        $this->transactionRepository->method('findBonusByDescription')->willReturn(null);
        $this->orderRepository->method('countByCustomer')->willReturn(1);

        $this->balanceManager->expects($this->once())->method('addTransaction');
        $this->entityManager->expects($this->once())->method('flush');

        ($this->listener)(new GenericEvent($order));
    }

    public function test_skips_when_disabled(): void
    {
        $this->setupConfig(enabled: false, points: 50);

        $order = $this->createMock(OrderInterface::class);
        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)(new GenericEvent($order));
    }

    public function test_skips_when_already_awarded(): void
    {
        $this->setupConfig(enabled: true, points: 50);

        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $ref = new \ReflectionProperty(LoyaltyAccount::class, 'id');
        $ref->setValue($account, 1);
        $this->balanceManager->method('getOrCreateAccount')->willReturn($account);
        $this->transactionRepository->method('findBonusByDescription')
            ->willReturn($this->createMock(PointTransactionInterface::class));

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)(new GenericEvent($order));
    }

    public function test_skips_when_not_first_order(): void
    {
        $this->setupConfig(enabled: true, points: 50);

        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $ref = new \ReflectionProperty(LoyaltyAccount::class, 'id');
        $ref->setValue($account, 1);
        $this->balanceManager->method('getOrCreateAccount')->willReturn($account);
        $this->transactionRepository->method('findBonusByDescription')->willReturn(null);
        $this->orderRepository->method('countByCustomer')->willReturn(3);

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)(new GenericEvent($order));
    }

    public function test_skips_disabled_account(): void
    {
        $this->setupConfig(enabled: true, points: 50);

        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $account->setEnabled(false);
        $this->balanceManager->method('getOrCreateAccount')->willReturn($account);

        $this->balanceManager->expects($this->never())->method('addTransaction');

        ($this->listener)(new GenericEvent($order));
    }

    public function test_skips_guest_order(): void
    {
        $this->setupConfig(enabled: true, points: 50);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn(null);

        $this->balanceManager->expects($this->never())->method('getOrCreateAccount');

        ($this->listener)(new GenericEvent($order));
    }

    private function setupConfig(bool $enabled, int $points): void
    {
        $config = $this->createMock(LoyaltyConfigurationInterface::class);
        $config->method('isFirstOrderBonusEnabled')->willReturn($enabled);
        $config->method('getFirstOrderBonusPoints')->willReturn($points);
        $this->configProvider->method('getConfiguration')->willReturn($config);
    }
}
