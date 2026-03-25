<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Shop;

use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class LoyaltyAccountController extends AbstractController
{
    public function __construct(
        private readonly CustomerContextInterface $customerContext,
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
        private readonly PointTransactionRepositoryInterface $transactionRepository,
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
    ) {
    }

    public function indexAction(): Response
    {
        $customer = $this->customerContext->getCustomer();

        if ($customer === null) {
            return $this->redirectToRoute('sylius_shop_login');
        }

        $account = $this->balanceManager->getOrCreateAccount($customer);
        $transactions = $this->transactionRepository->findByLoyaltyAccount($account, 50);

        return $this->render('@SyliusLoyaltyPlugin/shop/account/loyalty.html.twig', [
            'account' => $account,
            'transactions' => $transactions,
            'redemptionRate' => $this->configProvider->getConfiguration()->getRedemptionRate(),
        ]);
    }
}
