<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransaction;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransactionInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManager;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\PointsCalculatorInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\TierEvaluatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class LoyaltyBalanceManagerTest extends TestCase
{
    private LoyaltyAccountRepositoryInterface&MockObject $accountRepository;
    private PointTransactionRepositoryInterface&MockObject $transactionRepository;
    private FactoryInterface&MockObject $accountFactory;
    private EntityManagerInterface&MockObject $entityManager;
    private PointsCalculatorInterface&MockObject $pointsCalculator;
    private TierEvaluatorInterface&MockObject $tierEvaluator;
    private LoyaltyConfigurationProviderInterface&MockObject $configProvider;
    private LoyaltyBalanceManager $manager;

    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(LoyaltyAccountRepositoryInterface::class);
        $this->transactionRepository = $this->createMock(PointTransactionRepositoryInterface::class);
        $this->accountFactory = $this->createMock(FactoryInterface::class);
        $transactionFactory = $this->createMock(FactoryInterface::class);
        $transactionFactory->method('createNew')->willReturnCallback(fn () => new PointTransaction());
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->pointsCalculator = $this->createMock(PointsCalculatorInterface::class);
        $this->tierEvaluator = $this->createMock(TierEvaluatorInterface::class);
        $this->configProvider = $this->createMock(LoyaltyConfigurationProviderInterface::class);

        $config = $this->createMock(LoyaltyConfigurationInterface::class);
        $config->method('getExpiryDays')->willReturn(365);
        $this->configProvider->method('getConfiguration')->willReturn($config);

        $this->manager = new LoyaltyBalanceManager(
            $this->accountRepository,
            $this->transactionRepository,
            $this->accountFactory,
            $transactionFactory,
            $this->entityManager,
            $this->pointsCalculator,
            $this->tierEvaluator,
            $this->configProvider,
        );
    }

    // --- getOrCreateAccount ---

    public function test_get_existing_account(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $account = new LoyaltyAccount();

        $this->accountRepository->method('findOneByCustomer')->with($customer)->willReturn($account);
        $this->entityManager->expects($this->never())->method('persist');

        $result = $this->manager->getOrCreateAccount($customer);

        $this->assertSame($account, $result);
    }

    public function test_create_account_when_none_exists(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $newAccount = new LoyaltyAccount();

        $this->accountRepository->method('findOneByCustomer')->willReturn(null);
        $this->accountFactory->method('createNew')->willReturn($newAccount);
        $this->entityManager->expects($this->once())->method('persist')->with($newAccount);

        $result = $this->manager->getOrCreateAccount($customer);

        $this->assertSame($newAccount, $result);
        $this->assertSame($customer, $result->getCustomer());
    }

    // --- addTransaction ---

    public function test_add_earn_transaction_credits_points(): void
    {
        $account = new LoyaltyAccount();

        $transaction = $this->manager->addTransaction($account, TransactionType::Earn, 100, 'Test earn');

        $this->assertSame(100, $account->getPointsBalance());
        $this->assertSame(100, $account->getLifetimePoints());
        $this->assertSame(100, $transaction->getPoints());
        $this->assertNotNull($transaction->getExpiresAt());
    }

    public function test_add_redeem_transaction_debits_points(): void
    {
        $account = new LoyaltyAccount();
        $account->creditPoints(500);

        $transaction = $this->manager->addTransaction($account, TransactionType::Redeem, 200, 'Test redeem');

        $this->assertSame(300, $account->getPointsBalance());
        $this->assertSame(200, $transaction->getPoints());
    }

    public function test_debit_clamped_to_available_balance(): void
    {
        $account = new LoyaltyAccount();
        $account->creditPoints(50);

        $transaction = $this->manager->addTransaction($account, TransactionType::Redeem, 200, 'Over-redeem');

        $this->assertSame(0, $account->getPointsBalance());
        $this->assertSame(50, $transaction->getPoints()); // Clamped
    }

    public function test_debit_on_zero_balance_returns_early(): void
    {
        $account = new LoyaltyAccount();

        $transaction = $this->manager->addTransaction($account, TransactionType::Redeem, 100, 'No balance');

        $this->assertSame(0, $account->getPointsBalance());
        $this->assertSame(100, $transaction->getPoints()); // Original, not persisted
        $this->assertCount(0, $account->getTransactions()); // Not added
    }

    public function test_bonus_transaction_sets_expiry(): void
    {
        $account = new LoyaltyAccount();

        $transaction = $this->manager->addTransaction($account, TransactionType::Bonus, 50, 'Welcome');

        $this->assertNotNull($transaction->getExpiresAt());
        $this->assertSame(50, $account->getPointsBalance());
    }

    public function test_adjust_transaction_does_not_set_expiry(): void
    {
        $account = new LoyaltyAccount();

        $transaction = $this->manager->addTransaction($account, TransactionType::Adjust, 50, 'Manual');

        $this->assertNull($transaction->getExpiresAt());
    }

    public function test_tier_evaluated_after_credit(): void
    {
        $account = new LoyaltyAccount();

        $this->tierEvaluator->expects($this->once())->method('evaluate')->with($account);

        $this->manager->addTransaction($account, TransactionType::Earn, 100);
    }

    // --- awardPointsForOrder ---

    public function test_award_points_for_order(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getNumber')->willReturn('000001');

        $account = new LoyaltyAccount();
        $this->accountRepository->method('findOneByCustomer')->willReturn($account);
        $this->transactionRepository->method('findEarnByOrder')->willReturn(null);
        $this->pointsCalculator->method('calculateForOrder')->willReturn(100);

        $result = $this->manager->awardPointsForOrder($order);

        $this->assertNotNull($result);
        $this->assertSame(100, $account->getPointsBalance());
    }

    public function test_award_skips_guest_order(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn(null);

        $result = $this->manager->awardPointsForOrder($order);

        $this->assertNull($result);
    }

    public function test_award_skips_disabled_account(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $account->setEnabled(false);
        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        $result = $this->manager->awardPointsForOrder($order);

        $this->assertNull($result);
    }

    public function test_award_is_idempotent(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $this->accountRepository->method('findOneByCustomer')->willReturn($account);
        $this->transactionRepository->method('findEarnByOrder')
            ->willReturn($this->createMock(PointTransactionInterface::class));

        $result = $this->manager->awardPointsForOrder($order);

        $this->assertNull($result);
        $this->assertSame(0, $account->getPointsBalance());
    }

    // --- revokePointsForOrder ---

    public function test_revoke_points_for_order(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getNumber')->willReturn('000001');

        $account = new LoyaltyAccount();
        $account->creditPoints(100);
        $this->accountRepository->method('findOneByCustomer')->willReturn($account);

        $earnTransaction = $this->createMock(PointTransactionInterface::class);
        $earnTransaction->method('getPoints')->willReturn(100);
        $this->transactionRepository->method('findEarnByOrder')->willReturn($earnTransaction);

        $result = $this->manager->revokePointsForOrder($order);

        $this->assertNotNull($result);
        $this->assertSame(0, $account->getPointsBalance());
    }

    public function test_revoke_skips_when_no_earn_transaction(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $account = new LoyaltyAccount();
        $this->accountRepository->method('findOneByCustomer')->willReturn($account);
        $this->transactionRepository->method('findEarnByOrder')->willReturn(null);

        $result = $this->manager->revokePointsForOrder($order);

        $this->assertNull($result);
    }
}
