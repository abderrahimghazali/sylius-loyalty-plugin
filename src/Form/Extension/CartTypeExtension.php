<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Extension;

use Sylius\Bundle\OrderBundle\Form\Type\CartType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class CartTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pointsToRedeem', IntegerType::class, [
            'required' => false,
            'label' => 'loyalty.ui.points_to_redeem',
            'empty_data' => '0',
            'constraints' => [
                new Assert\GreaterThanOrEqual(0),
                new Assert\LessThanOrEqual(10_000_000),
            ],
        ]);

        $builder->get('pointsToRedeem')->addModelTransformer(new CallbackTransformer(
            fn (mixed $value): mixed => ($value === 0 || $value === null) ? null : $value,
            fn (mixed $value): int => (int) ($value ?? 0),
        ));
    }

    public static function getExtendedTypes(): iterable
    {
        return [CartType::class];
    }
}
