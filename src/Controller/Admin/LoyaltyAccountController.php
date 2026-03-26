<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Admin;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LoyaltyAccountController extends AbstractController
{
    private const PER_PAGE = 20;

    public function __construct(
        private readonly LoyaltyAccountRepositoryInterface $accountRepository,
        private readonly PointTransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function showAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        $account = $this->accountRepository->find($id);

        if (!$account instanceof LoyaltyAccountInterface) {
            throw new NotFoundHttpException('Loyalty account not found.');
        }

        $page = max(1, $request->query->getInt('page', 1));
        $totalItems = $this->transactionRepository->countByLoyaltyAccount($account);
        $totalPages = max(1, (int) ceil($totalItems / self::PER_PAGE));
        $transactions = $this->transactionRepository->findPaginatedByLoyaltyAccount($account, $page, self::PER_PAGE);

        return $this->render('@SyliusLoyaltyPlugin/admin/loyalty_account/show.html.twig', [
            'account' => $account,
            'transactions' => $transactions,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
