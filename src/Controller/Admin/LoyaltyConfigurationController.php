<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Admin;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Form\Type\LoyaltyConfigurationType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyConfigurationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LoyaltyConfigurationController extends AbstractController
{
    public function __construct(
        private readonly LoyaltyConfigurationRepositoryInterface $configurationRepository,
        private readonly FactoryInterface $configurationFactory,
        private readonly RepositoryInterface $channelRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function indexAction(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        $channels = $this->channelRepository->findAll();

        $configurations = [];
        foreach ($channels as $channel) {
            assert($channel instanceof ChannelInterface);
            $configurations[] = [
                'channel' => $channel,
                'config' => $this->configurationRepository->findOneByChannel($channel),
            ];
        }

        return $this->render('@SyliusLoyaltyPlugin/admin/configuration/index.html.twig', [
            'configurations' => $configurations,
        ]);
    }

    public function editAction(Request $request, string $channelCode): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneBy(['code' => $channelCode]);
        if ($channel === null) {
            throw new NotFoundHttpException(sprintf('Channel "%s" not found.', $channelCode));
        }

        $config = $this->configurationRepository->findOneByChannel($channel);

        if ($config === null) {
            /** @var LoyaltyConfigurationInterface $config */
            $config = $this->configurationFactory->createNew();
            $config->setChannel($channel);
        }

        $form = $this->createForm(LoyaltyConfigurationType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($config);
            $this->entityManager->flush();

            $this->addFlash('success', 'loyalty.flash.configuration_updated');

            return $this->redirectToRoute('loyalty_admin_configuration', ['channelCode' => $channelCode]);
        }

        return $this->render('@SyliusLoyaltyPlugin/admin/configuration/edit.html.twig', [
            'form' => $form->createView(),
            'channel' => $channel,
        ]);
    }
}
