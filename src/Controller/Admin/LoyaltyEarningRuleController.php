<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Admin;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleInterface;
use Abderrahim\SyliusLoyaltyPlugin\Form\Type\LoyaltyEarningRuleType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LoyaltyEarningRuleController extends AbstractController
{
    public function __construct(
        private readonly RepositoryInterface $earningRuleRepository,
        private readonly FactoryInterface $earningRuleFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        $scope = $request->query->getString('scope', 'taxon');

        /** @var LoyaltyEarningRuleInterface $rule */
        $rule = $this->earningRuleFactory->createNew();

        $form = $this->createForm(LoyaltyEarningRuleType::class, $rule, ['scope' => $scope]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($rule);
            $this->entityManager->flush();

            $this->addFlash('success', 'sylius.resource.create');

            return $this->redirectToRoute('loyalty_admin_earning_rule_index');
        }

        return $this->render('@SyliusLoyaltyPlugin/admin/loyalty_earning_rule/create.html.twig', [
            'form' => $form->createView(),
            'scope' => $scope,
        ]);
    }

    public function updateAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        $rule = $this->earningRuleRepository->find($id);
        if (!$rule instanceof LoyaltyEarningRuleInterface) {
            throw new NotFoundHttpException('Earning rule not found.');
        }

        $scope = $rule->getScopeType()->value;

        $form = $this->createForm(LoyaltyEarningRuleType::class, $rule, ['scope' => $scope]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'sylius.resource.update');

            return $this->redirectToRoute('loyalty_admin_earning_rule_index');
        }

        return $this->render('@SyliusLoyaltyPlugin/admin/loyalty_earning_rule/update.html.twig', [
            'form' => $form->createView(),
            'scope' => $scope,
            'rule' => $rule,
        ]);
    }
}
