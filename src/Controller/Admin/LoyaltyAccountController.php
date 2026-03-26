<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Admin;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LoyaltyAccountController extends AbstractController
{
    public function __construct(
        private readonly LoyaltyAccountRepositoryInterface $accountRepository,
        private readonly PointTransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function showAction(int $id): Response
    {
        $account = $this->accountRepository->find($id);

        if (!$account instanceof LoyaltyAccountInterface) {
            throw new NotFoundHttpException('Loyalty account not found.');
        }

        $transactions = $this->transactionRepository->findByLoyaltyAccount($account, 100);

        return $this->render('@SyliusLoyaltyPlugin/admin/loyalty_account/show.html.twig', [
            'account' => $account,
            'transactions' => $transactions,
        ]);
    }
}
