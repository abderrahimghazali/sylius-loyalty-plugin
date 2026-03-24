<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Admin;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfiguration;
use Abderrahim\SyliusLoyaltyPlugin\Form\Type\LoyaltyConfigurationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LoyaltyConfigurationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function editAction(Request $request): Response
    {
        $config = $this->getOrCreateConfiguration();

        $form = $this->createForm(LoyaltyConfigurationType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'loyalty.flash.configuration_updated');

            return $this->redirectToRoute('loyalty_admin_configuration');
        }

        return $this->render('@SyliusLoyaltyPlugin/admin/configuration/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function getOrCreateConfiguration(): LoyaltyConfiguration
    {
        $repository = $this->entityManager->getRepository(LoyaltyConfiguration::class);
        $config = $repository->findOneBy([]);

        if ($config === null) {
            $config = new LoyaltyConfiguration();
            $this->entityManager->persist($config);
            $this->entityManager->flush();
        }

        return $config;
    }
}
