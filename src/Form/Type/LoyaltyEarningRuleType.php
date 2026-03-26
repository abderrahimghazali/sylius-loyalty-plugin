<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Abderrahim\SyliusLoyaltyPlugin\Enum\EarningRuleScopeType;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyEarningRuleType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius.ui.name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ])
            ->add('scopeType', ChoiceType::class, [
                'label' => 'loyalty.ui.scope_type',
                'choices' => [
                    'loyalty.ui.scope_taxon' => EarningRuleScopeType::Taxon->value,
                    'loyalty.ui.scope_product' => EarningRuleScopeType::Product->value,
                    'loyalty.ui.scope_variant' => EarningRuleScopeType::Variant->value,
                ],
                'getter' => fn ($rule) => $rule->getScopeType()->value,
                'setter' => fn ($rule, $value) => $rule->setScopeType(EarningRuleScopeType::from($value)),
            ])
            ->add('targetId', IntegerType::class, [
                'label' => 'loyalty.ui.target_id',
                'help' => 'loyalty.form.target_id_help',
                'constraints' => [new Assert\Positive()],
                'attr' => ['min' => 1],
            ])
            ->add('pointsPerCurrencyUnit', IntegerType::class, [
                'label' => 'loyalty.form.points_per_currency_unit',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'sylius.ui.priority',
                'constraints' => [new Assert\PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('startsAt', DateTimeType::class, [
                'label' => 'loyalty.ui.starts_at',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'loyalty.ui.ends_at',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('channel', ChannelChoiceType::class, [
                'label' => 'sylius.ui.channel',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_earning_rule';
    }
}
