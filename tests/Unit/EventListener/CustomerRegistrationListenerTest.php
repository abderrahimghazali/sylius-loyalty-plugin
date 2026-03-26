<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusLoyaltyPlugin\Unit\EventListener;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccount;
use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\EventListener\CustomerRegistrationListener;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class CustomerRegistrationListenerTest extends TestCase
{
    private LoyaltyBalanceManagerInterface&MockObject $balanceManager;
    private LoyaltyConfigurationProviderInterface&MockObject $configProvider;
    private EntityManagerInterface&MockObject $entityManager;
    private CustomerRegistrationListener $listener;

    protected function setUp(): void
    {
        $this->balanceManager = $this->createMock(LoyaltyBalanceManagerInterface::class);
        $this->configProvider = $this->createMock(LoyaltyConfigurationProviderInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new CustomerRegistrationListener(
            $this->balanceManager,
            $this->configProvider,
            $this->entityManager,
        );
    }

    public function test_awards_registration_bonus(): void
    {
        $this->setupConfig(enabled: true, points: 100);

        $customer = $this->createMock(CustomerInterface::class);
        $account = new LoyaltyAccount();

        $this->balanceManager->expects($this->once())
            ->method('getOrCreateAccount')
            ->with($customer)
            ->willReturn($account);

        $this->balanceManager->expects($this->once())->method('addTransaction');
        $this->entityManager->expects($this->once())->method('flush');

        ($this->listener)(new GenericEvent($customer));
    }

    public function test_skips_when_bonus_disabled(): void
    {
        $this->setupConfig(enabled: false, points: 100);

        $this->balanceManager->expects($this->never())->method('getOrCreateAccount');

        ($this->listener)(new GenericEvent($this->createMock(CustomerInterface::class)));
    }

    public function test_skips_when_bonus_points_zero(): void
    {
        $this->setupConfig(enabled: true, points: 0);

        $this->balanceManager->expects($this->never())->method('getOrCreateAccount');

        ($this->listener)(new GenericEvent($this->createMock(CustomerInterface::class)));
    }

    public function test_skips_non_customer_subject(): void
    {
        $this->setupConfig(enabled: true, points: 100);

        $this->balanceManager->expects($this->never())->method('getOrCreateAccount');

        ($this->listener)(new GenericEvent(new \stdClass()));
    }

    private function setupConfig(bool $enabled, int $points): void
    {
        $config = $this->createMock(LoyaltyConfigurationInterface::class);
        $config->method('isRegistrationBonusEnabled')->willReturn($enabled);
        $config->method('getRegistrationBonusPoints')->willReturn($points);
        $this->configProvider->method('getConfiguration')->willReturn($config);
    }
}
