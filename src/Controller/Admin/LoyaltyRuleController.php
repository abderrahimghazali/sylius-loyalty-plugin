<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Admin;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyRuleInterface;
use Abderrahim\SyliusLoyaltyPlugin\Form\Type\LoyaltyRuleType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LoyaltyRuleController extends AbstractController
{
    public function __construct(
        private readonly RepositoryInterface $ruleRepository,
        private readonly FactoryInterface $ruleFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        /** @var LoyaltyRuleInterface $rule */
        $rule = $this->ruleFactory->createNew();

        $form = $this->createForm(LoyaltyRuleType::class, $rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($rule);
            $this->entityManager->flush();

            $this->addFlash('success', 'loyalty.flash.rule_created');

            return $this->redirectToRoute('loyalty_admin_rule_index');
        }

        return $this->render('@SyliusLoyaltyPlugin/admin/loyalty_rule/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function updateAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        $rule = $this->ruleRepository->find($id);
        if (!$rule instanceof LoyaltyRuleInterface) {
            throw new NotFoundHttpException('Loyalty rule not found.');
        }

        $form = $this->createForm(LoyaltyRuleType::class, $rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'loyalty.flash.rule_updated');

            return $this->redirectToRoute('loyalty_admin_rule_index');
        }

        return $this->render('@SyliusLoyaltyPlugin/admin/loyalty_rule/update.html.twig', [
            'form' => $form->createView(),
            'rule' => $rule,
        ]);
    }
}
