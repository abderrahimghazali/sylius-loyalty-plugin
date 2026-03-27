<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Admin;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyAccountInterface;
use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Form\Type\PointAdjustmentType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PointAdjustmentController extends AbstractController
{
    public function __construct(
        private readonly LoyaltyAccountRepositoryInterface $accountRepository,
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function adjustAction(Request $request, int $accountId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        $account = $this->accountRepository->find($accountId);
        if (!$account instanceof LoyaltyAccountInterface) {
            throw new NotFoundHttpException('Loyalty account not found.');
        }

        $form = $this->createForm(PointAdjustmentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $points = (int) $data['points'];
            $reason = strip_tags((string) $data['reason']);

            // Positive = credit points (Adjust), negative = debit points (Deduct)
            if ($points > 0) {
                $this->balanceManager->addTransaction(
                    $account,
                    TransactionType::Adjust,
                    $points,
                    sprintf('Manual adjustment: %s', $reason),
                );
            } else {
                $this->balanceManager->addTransaction(
                    $account,
                    TransactionType::Deduct,
                    abs($points),
                    sprintf('Manual deduction: %s', $reason),
                );
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'loyalty.flash.points_adjusted');

            return $this->redirectToRoute('loyalty_admin_loyalty_account_show', ['id' => $accountId]);
        }

        return $this->render('@SyliusLoyaltyPlugin/admin/loyalty_account/adjust.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }
}
