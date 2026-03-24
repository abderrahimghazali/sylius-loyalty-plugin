<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pointsPerCurrencyUnit', IntegerType::class, [
                'label' => 'loyalty.form.points_per_currency_unit',
                'help' => 'loyalty.form.points_per_currency_unit_help',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('redemptionRate', IntegerType::class, [
                'label' => 'loyalty.form.redemption_rate',
                'help' => 'loyalty.form.redemption_rate_help',
                'constraints' => [new Assert\Positive()],
                'attr' => ['min' => 1],
            ])
            ->add('expiryMonths', IntegerType::class, [
                'label' => 'loyalty.form.expiry_months',
                'help' => 'loyalty.form.expiry_months_help',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('registrationBonusEnabled', CheckboxType::class, [
                'label' => 'loyalty.form.registration_bonus_enabled',
                'required' => false,
            ])
            ->add('registrationBonusPoints', IntegerType::class, [
                'label' => 'loyalty.form.registration_bonus_points',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('birthdayBonusEnabled', CheckboxType::class, [
                'label' => 'loyalty.form.birthday_bonus_enabled',
                'required' => false,
            ])
            ->add('birthdayBonusPoints', IntegerType::class, [
                'label' => 'loyalty.form.birthday_bonus_points',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('firstOrderBonusEnabled', CheckboxType::class, [
                'label' => 'loyalty.form.first_order_bonus_enabled',
                'required' => false,
            ])
            ->add('firstOrderBonusPoints', IntegerType::class, [
                'label' => 'loyalty.form.first_order_bonus_points',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LoyaltyConfiguration::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_configuration';
    }
}
