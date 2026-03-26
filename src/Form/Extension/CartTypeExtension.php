<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Extension;

use Sylius\Bundle\OrderBundle\Form\Type\CartType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class CartTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pointsToRedeem', IntegerType::class, [
            'required' => false,
            'label' => 'loyalty.ui.points_to_redeem',
            'empty_data' => '0',
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [CartType::class];
    }
}
