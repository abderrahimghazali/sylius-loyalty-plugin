<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class PointAdjustmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('points', IntegerType::class, [
                'label' => 'loyalty.form.adjustment_points',
                'help' => 'loyalty.form.adjustment_points_help',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\NotEqualTo(0),
                ],
            ])
            ->add('reason', TextType::class, [
                'label' => 'loyalty.form.adjustment_reason',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_point_adjustment';
    }
}
