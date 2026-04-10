<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\OrderProcessing;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Model\AdjustmentTypes;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyAccountRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyConfigurationProviderInterface;
use Sylius\Bundle\OrderBundle\Attribute\AsOrderProcessor;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * Converts pointsToRedeem on the order into a negative loyalty adjustment.
 *
 * Priority 5: runs AFTER taxes (10) but BEFORE payment processing (0).
 * This ensures taxes are calculated on the pre-discount total, then
 * loyalty discount is applied, then the payment amount reflects the final total.
 */
#[AsOrderProcessor(priority: 5)]
final class LoyaltyOrderProcessor implements OrderProcessorInterface
{
    public function __construct(
        private readonly LoyaltyAccountRepositoryInterface $accountRepository,
        private readonly FactoryInterface $adjustmentFactory,
        private readonly LoyaltyConfigurationProviderInterface $configProvider,
    ) {
    }

    public function process(BaseOrderInterface $order): void
    {
        if (!$order instanceof OrderInterface || !$order instanceof LoyaltyOrderInterface) {
            return;
        }

        // Always clear existing loyalty adjustments before recalculating
        $this->clearLoyaltyAdjustments($order);
        $order->recalculateAdjustmentsTotal();

        $pointsToRedeem = $order->getPointsToRedeem();

        if ($pointsToRedeem <= 0) {
            return;
        }

        // Guard: guest orders cannot use loyalty points
        $customer = $order->getCustomer();
        if ($customer === null) {
            $order->setPointsToRedeem(0);

            return;
        }

        // Guard: verify customer has enough points
        $account = $this->accountRepository->findOneByCustomer($customer);
        if ($account === null || !$account->isEnabled()) {
            $order->setPointsToRedeem(0);

            return;
        }

        $availablePoints = $account->getPointsBalance();
        $effectivePoints = min($pointsToRedeem, $availablePoints);

        if ($effectivePoints <= 0) {
            $order->setPointsToRedeem(0);

            return;
        }

        // Use channel-specific config for redemption rate
        $channel = $order->getChannel();
        $config = $channel !== null
            ? $this->configProvider->getConfigurationForChannel($channel)
            : $this->configProvider->getConfiguration();
        $redemptionRate = $config->getRedemptionRate();

        if ($redemptionRate <= 0) {
            $order->setPointsToRedeem(0);

            return;
        }

        // Convert points to discount amount (in smallest currency unit = cents)
        $discountAmount = (int) floor(($effectivePoints / $redemptionRate) * 100);

        // Guard: discount cannot exceed the order total (items + shipping + tax, before this adjustment)
        $orderTotalBeforeDiscount = $order->getTotal();
        if ($discountAmount > $orderTotalBeforeDiscount) {
            $discountAmount = $orderTotalBeforeDiscount;
            // Recalculate how many points this actually requires
            $effectivePoints = (int) ceil(($discountAmount / 100) * $redemptionRate);
        }

        if ($discountAmount <= 0) {
            $order->setPointsToRedeem(0);

            return;
        }

        // Clamp pointsToRedeem to the effective value (may have been reduced)
        $order->setPointsToRedeem($effectivePoints);

        /** @var AdjustmentInterface $adjustment */
        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentTypes::LOYALTY_POINTS_DISCOUNT);
        $adjustment->setAmount(-$discountAmount);
        $adjustment->setLabel(sprintf('Loyalty points discount (%d pts)', $effectivePoints));
        $adjustment->setNeutral(false);

        $order->addAdjustment($adjustment);
    }

    private function clearLoyaltyAdjustments(OrderInterface $order): void
    {
        foreach ($order->getAdjustments(AdjustmentTypes::LOYALTY_POINTS_DISCOUNT) as $adjustment) {
            $order->removeAdjustment($adjustment);
        }
    }
}
