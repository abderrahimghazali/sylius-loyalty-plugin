<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Shop;

use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LoyaltyAccountController extends AbstractController
{
    private const PER_PAGE = 15;

    public function __construct(
        private readonly CustomerContextInterface $customerContext,
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
        private readonly PointTransactionRepositoryInterface $transactionRepository,
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $customer = $this->customerContext->getCustomer();

        if ($customer === null) {
            return $this->redirectToRoute('sylius_shop_login');
        }

        $account = $this->balanceManager->getOrCreateAccount($customer);

        $page = max(1, $request->query->getInt('page', 1));
        $totalItems = $this->transactionRepository->countByLoyaltyAccount($account);
        $totalPages = max(1, (int) ceil($totalItems / self::PER_PAGE));
        $transactions = $this->transactionRepository->findPaginatedByLoyaltyAccount($account, $page, self::PER_PAGE);

        return $this->render('@SyliusLoyaltyPlugin/shop/account/loyalty.html.twig', [
            'account' => $account,
            'transactions' => $transactions,
            'redemptionRate' => $this->configProvider->getConfiguration()->getRedemptionRate(),
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
