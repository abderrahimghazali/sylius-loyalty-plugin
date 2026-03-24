<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyTier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyTierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'sylius.ui.code',
                'disabled' => $options['is_edit'],
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 50)],
            ])
            ->add('name', TextType::class, [
                'label' => 'sylius.ui.name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
            ])
            ->add('minPoints', IntegerType::class, [
                'label' => 'loyalty.form.min_points',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('multiplier', NumberType::class, [
                'label' => 'loyalty.form.multiplier',
                'scale' => 2,
                'constraints' => [new Assert\Positive()],
                'attr' => ['min' => 0.01, 'step' => 0.01],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'sylius.ui.position',
                'constraints' => [new Assert\PositiveOrZero()],
            ])
            ->add('color', ColorType::class, [
                'label' => 'loyalty.form.color',
                'required' => false,
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LoyaltyTier::class,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_tier';
    }
}
