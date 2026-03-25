<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Controller\Api;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class LoyaltyRedemptionController
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoyaltyAccountRepositoryInterface $loyaltyAccountRepository,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(
        path: '/api/v2/shop/orders/{tokenValue}/loyalty-redemption',
        name: 'loyalty_api_shop_loyalty_redemption_apply',
        methods: ['POST'],
    )]
    public function apply(string $tokenValue, Request $request): JsonResponse
    {
        $order = $this->findOrder($tokenValue);
        if ($order === null) {
            return $this->errorResponse('Order not found.', Response::HTTP_NOT_FOUND);
        }

        if (!$order instanceof LoyaltyOrderInterface) {
            return $this->errorResponse(
                'Order entity does not support loyalty redemption. Apply the LoyaltyOrderTrait.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $customer = $order->getCustomer();
        if ($customer === null) {
            return $this->errorResponse('Guest orders cannot use loyalty points.', Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        $pointsToRedeem = (int) ($payload['pointsToRedeem'] ?? 0);

        if ($pointsToRedeem <= 0) {
            return $this->errorResponse('pointsToRedeem must be a positive integer.', Response::HTTP_BAD_REQUEST);
        }

        // Validate against available balance
        $account = $this->loyaltyAccountRepository->findOneByCustomer($customer);
        if ($account === null || !$account->isEnabled()) {
            return $this->errorResponse('No active loyalty account found.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $available = $account->getPointsBalance();
        if ($pointsToRedeem > $available) {
            return $this->errorResponse(
                sprintf('Insufficient points. Available: %d, requested: %d.', $available, $pointsToRedeem),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        // Set redemption and trigger order reprocessing
        $order->setPointsToRedeem($pointsToRedeem);
        $this->orderProcessor->process($order);
        $this->entityManager->flush();

        // The processor may have clamped the value
        $effectivePoints = $order->getPointsToRedeem();
        $redemptionRate = $this->configProvider->getConfiguration()->getRedemptionRate();
        $discountCents = (int) floor(($effectivePoints / $redemptionRate) * 100);

        return new JsonResponse([
            'pointsRedeemed' => $effectivePoints,
            'discountAmount' => $discountCents,
            'orderTotal' => $order->getTotal(),
            'message' => sprintf('%d points applied as discount.', $effectivePoints),
        ]);
    }

    #[Route(
        path: '/api/v2/shop/orders/{tokenValue}/loyalty-redemption',
        name: 'loyalty_api_shop_loyalty_redemption_remove',
        methods: ['DELETE'],
    )]
    public function remove(string $tokenValue): JsonResponse
    {
        $order = $this->findOrder($tokenValue);
        if ($order === null) {
            return $this->errorResponse('Order not found.', Response::HTTP_NOT_FOUND);
        }

        if (!$order instanceof LoyaltyOrderInterface) {
            return $this->errorResponse(
                'Order entity does not support loyalty redemption.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $order->setPointsToRedeem(0);
        $this->orderProcessor->process($order);
        $this->entityManager->flush();

        return new JsonResponse([
            'pointsRedeemed' => 0,
            'discountAmount' => 0,
            'orderTotal' => $order->getTotal(),
            'message' => 'Loyalty redemption removed.',
        ]);
    }

    private function findOrder(string $tokenValue): ?OrderInterface
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $tokenValue]);

        return $order;
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['message' => $message], $status);
    }
}
