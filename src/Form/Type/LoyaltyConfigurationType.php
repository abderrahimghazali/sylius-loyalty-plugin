<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyConfigurationType extends AbstractResourceType
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
            ->add('expiryDays', IntegerType::class, [
                'label' => 'loyalty.form.expiry_days',
                'help' => 'loyalty.form.expiry_days_help',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('tiersEnabled', CheckboxType::class, [
                'label' => 'loyalty.form.tiers_enabled',
                'required' => false,
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

    public function getBlockPrefix(): string
    {
        return 'loyalty_configuration';
    }
}
