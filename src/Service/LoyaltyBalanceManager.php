<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Service;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransaction;
use Abderrahim\SyliusLoyaltyPlugin\Entity\PointTransactionInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class LoyaltyBalanceManager implements LoyaltyBalanceManagerInterface
{
    public function __construct(
        private readonly LoyaltyAccountRepositoryInterface $accountRepository,
        private readonly PointTransactionRepositoryInterface $transactionRepository,
        private readonly FactoryInterface $accountFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly PointsCalculatorInterface $pointsCalculator,
        private readonly TierEvaluatorInterface $tierEvaluator,
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
    ) {
    }

    public function getOrCreateAccount(CustomerInterface $customer): LoyaltyAccountInterface
    {
        $account = $this->accountRepository->findOneByCustomer($customer);

        if ($account !== null) {
            return $account;
        }

        /** @var LoyaltyAccountInterface $account */
        $account = $this->accountFactory->createNew();
        $account->setCustomer($customer);

        $this->entityManager->persist($account);

        return $account;
    }

    public function addTransaction(
        LoyaltyAccountInterface $account,
        TransactionType $type,
        int $points,
        ?string $description = null,
        ?OrderInterface $order = null,
    ): PointTransactionInterface {
        $transaction = new PointTransaction();
        $transaction->setType($type);
        $transaction->setPoints($points);
        $transaction->setDescription($description);
        $transaction->setOrder($order);

        // Set expiry for earn/bonus transactions
        $expiryDays = $this->configProvider->getConfiguration()->getExpiryDays();
        if (in_array($type, [TransactionType::Earn, TransactionType::Bonus], true) && $expiryDays > 0) {
            $expiresAt = new \DateTime(sprintf('+%d days', $expiryDays));
            $transaction->setExpiresAt($expiresAt);
        }

        // Update account balance — clamp debits to available balance
        if ($type->isDebit()) {
            $safePoints = min($points, $account->getPointsBalance());
            if ($safePoints <= 0) {
                return $transaction;
            }
            $transaction->setPoints($safePoints);
            $account->addTransaction($transaction);
            $account->debitPoints($safePoints);
        } else {
            $account->addTransaction($transaction);
            $account->creditPoints($points);
        }

        // Re-evaluate tier
        $this->tierEvaluator->evaluate($account);

        $this->entityManager->persist($transaction);

        return $transaction;
    }

    public function awardPointsForOrder(OrderInterface $order): ?PointTransactionInterface
    {
        $customer = $order->getCustomer();
        if ($customer === null) {
            return null;
        }

        $account = $this->getOrCreateAccount($customer);

        if (!$account->isEnabled()) {
            return null;
        }

        // Check if points were already awarded for this order
        $existing = $this->transactionRepository->findEarnByOrder($account, $order);
        if ($existing !== null) {
            return null;
        }

        $points = $this->pointsCalculator->calculateForOrder($order, $account);

        if ($points <= 0) {
            return null;
        }

        return $this->addTransaction(
            $account,
            TransactionType::Earn,
            $points,
            sprintf('Points earned for order #%s', $order->getNumber()),
            $order,
        );
    }

    public function revokePointsForOrder(OrderInterface $order): ?PointTransactionInterface
    {
        $customer = $order->getCustomer();
        if ($customer === null) {
            return null;
        }

        $account = $this->accountRepository->findOneByCustomer($customer);
        if ($account === null) {
            return null;
        }

        $earnTransaction = $this->transactionRepository->findEarnByOrder($account, $order);
        if ($earnTransaction === null) {
            return null;
        }

        return $this->addTransaction(
            $account,
            TransactionType::Deduct,
            $earnTransaction->getPoints(),
            sprintf('Points revoked for cancelled order #%s', $order->getNumber()),
            $order,
        );
    }
}
