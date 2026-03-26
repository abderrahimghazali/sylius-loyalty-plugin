<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Abderrahim\SyliusLoyaltyPlugin\Enum\EarningRuleScopeType;
use Sylius\Bundle\AdminBundle\Form\Type\ProductAutocompleteType;
use Sylius\Bundle\AdminBundle\Form\Type\ProductVariantAutocompleteType;
use Sylius\Bundle\AdminBundle\Form\Type\TaxonAutocompleteType;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            })
            ->add('targetTaxon', TaxonAutocompleteType::class, [
                'label' => 'loyalty.ui.scope_taxon',
                'required' => false,
                'mapped' => false,
            ])
            ->add('targetProduct', ProductAutocompleteType::class, [
                'label' => 'loyalty.ui.scope_product',
                'required' => false,
                'mapped' => false,
            ])
            ->add('targetVariant', ProductVariantAutocompleteType::class, [
                'label' => 'loyalty.ui.scope_variant',
                'required' => false,
                'mapped' => false,
            ])
            ->add('pointsPerCurrencyUnit', IntegerType::class, [
                'label' => 'loyalty.form.points_per_currency_unit',
                'help' => 'loyalty.form.earning_rule_rate_help',
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

        // On submit: read the autocomplete field matching scopeType and set targetId
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event): void {
            $rule = $event->getData();
            if ($rule === null) {
                return;
            }

            $form = $event->getForm();
            $scope = $rule->getScopeType();

            $target = match ($scope) {
                EarningRuleScopeType::Taxon => $form->get('targetTaxon')->getData(),
                EarningRuleScopeType::Product => $form->get('targetProduct')->getData(),
                EarningRuleScopeType::Variant => $form->get('targetVariant')->getData(),
            };

            if ($target !== null) {
                $rule->setTargetId($target->getId());
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_earning_rule';
    }
}
