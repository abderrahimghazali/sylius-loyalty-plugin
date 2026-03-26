<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\Entity;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use PHPUnit\Framework\TestCase;

final class LoyaltyAccountTest extends TestCase
{
    public function test_credit_points_increases_balance_and_lifetime(): void
    {
        $account = new LoyaltyAccount();

        $account->creditPoints(100);

        $this->assertSame(100, $account->getPointsBalance());
        $this->assertSame(100, $account->getLifetimePoints());
    }

    public function test_credit_points_accumulates(): void
    {
        $account = new LoyaltyAccount();

        $account->creditPoints(100);
        $account->creditPoints(50);

        $this->assertSame(150, $account->getPointsBalance());
        $this->assertSame(150, $account->getLifetimePoints());
    }

    public function test_debit_points_decreases_balance_only(): void
    {
        $account = new LoyaltyAccount();
        $account->creditPoints(500);

        $account->debitPoints(200);

        $this->assertSame(300, $account->getPointsBalance());
        $this->assertSame(500, $account->getLifetimePoints());
    }

    public function test_debit_points_throws_when_exceeding_balance(): void
    {
        $account = new LoyaltyAccount();
        $account->creditPoints(100);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot debit 200 points');

        $account->debitPoints(200);
    }

    public function test_debit_points_throws_on_zero_balance(): void
    {
        $account = new LoyaltyAccount();

        $this->expectException(\DomainException::class);

        $account->debitPoints(1);
    }

    public function test_debit_exact_balance_leaves_zero(): void
    {
        $account = new LoyaltyAccount();
        $account->creditPoints(100);

        $account->debitPoints(100);

        $this->assertSame(0, $account->getPointsBalance());
        $this->assertSame(100, $account->getLifetimePoints());
    }

    public function test_enabled_by_default(): void
    {
        $account = new LoyaltyAccount();

        $this->assertTrue($account->isEnabled());
    }

    public function test_transactions_collection_starts_empty(): void
    {
        $account = new LoyaltyAccount();

        $this->assertCount(0, $account->getTransactions());
    }
}
