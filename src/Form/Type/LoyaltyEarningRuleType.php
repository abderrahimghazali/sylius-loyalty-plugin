<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Abderrahim\SyliusLoyaltyPlugin\Enum\EarningRuleScopeType;
use Sylius\Bundle\AdminBundle\Form\Type\ProductAutocompleteType;
use Sylius\Bundle\AdminBundle\Form\Type\ProductVariantAutocompleteType;
use Sylius\Bundle\AdminBundle\Form\Type\TaxonAutocompleteType;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\CoreBundle\Form\DataTransformer\ProductsToCodesTransformer;
use Sylius\Bundle\CoreBundle\Form\DataTransformer\ProductVariantsToCodesTransformer;
use Sylius\Bundle\CoreBundle\Form\DataTransformer\TaxonsToCodesTransformer;
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
    public function __construct(
        string $dataClass,
        private readonly ProductsToCodesTransformer $productsToCodesTransformer,
        private readonly TaxonsToCodesTransformer $taxonsToCodesTransformer,
        private readonly ProductVariantsToCodesTransformer $variantsToCodesTransformer,
        array $validationGroups = [],
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

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
                    'loyalty.ui.scope_taxon' => 'taxon',
                    'loyalty.ui.scope_product' => 'product',
                    'loyalty.ui.scope_variant' => 'variant',
                ],
                'mapped' => false,
            ])
            ->add('targetTaxons', TaxonAutocompleteType::class, [
                'label' => 'loyalty.ui.target_taxons',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ])
            ->add('targetProducts', ProductAutocompleteType::class, [
                'label' => 'loyalty.ui.target_products',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ])
            ->add('targetVariants', ProductVariantAutocompleteType::class, [
                'label' => 'loyalty.ui.target_variants',
                'multiple' => true,
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

        // Add data transformers for entity ↔ codes conversion
        $builder->get('targetTaxons')->addModelTransformer($this->taxonsToCodesTransformer);
        $builder->get('targetProducts')->addModelTransformer($this->productsToCodesTransformer);
        $builder->get('targetVariants')->addModelTransformer($this->variantsToCodesTransformer);

        // Pre-populate form from entity
        $builder->addEventListener(FormEvents::POST_SET_DATA, static function (FormEvent $event): void {
            $rule = $event->getData();
            if ($rule === null) {
                return;
            }

            $form = $event->getForm();
            $form->get('scopeType')->setData($rule->getScopeType()->value);

            $codes = $rule->getTargetCodes();
            if (count($codes) > 0) {
                match ($rule->getScopeType()) {
                    EarningRuleScopeType::Taxon => $form->get('targetTaxons')->setData($codes),
                    EarningRuleScopeType::Product => $form->get('targetProducts')->setData($codes),
                    EarningRuleScopeType::Variant => $form->get('targetVariants')->setData($codes),
                };
            }
        });

        // On submit: read scope + matching autocomplete → set entity fields
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event): void {
            $rule = $event->getData();
            if ($rule === null) {
                return;
            }

            $form = $event->getForm();
            $scopeValue = $form->get('scopeType')->getData();

            if ($scopeValue !== null) {
                $rule->setScopeType(EarningRuleScopeType::from($scopeValue));
            }

            $codes = match ($rule->getScopeType()) {
                EarningRuleScopeType::Taxon => $form->get('targetTaxons')->getData(),
                EarningRuleScopeType::Product => $form->get('targetProducts')->getData(),
                EarningRuleScopeType::Variant => $form->get('targetVariants')->getData(),
            };

            $rule->setTargetCodes(is_array($codes) ? $codes : []);
        });
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_earning_rule';
    }
}
