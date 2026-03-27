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
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
        $scope = EarningRuleScopeType::from($options['scope']);

        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius.ui.name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ])
        ;

        // Add only the relevant autocomplete field based on scope
        match ($scope) {
            EarningRuleScopeType::Taxon => $builder
                ->add('targets', TaxonAutocompleteType::class, [
                    'label' => 'loyalty.ui.target_taxons',
                    'multiple' => true,
                    'required' => true,
                    'mapped' => false,
                ]),
            EarningRuleScopeType::Product => $builder
                ->add('targets', ProductAutocompleteType::class, [
                    'label' => 'loyalty.ui.target_products',
                    'multiple' => true,
                    'required' => true,
                    'mapped' => false,
                ]),
            EarningRuleScopeType::Variant => $builder
                ->add('targets', ProductVariantAutocompleteType::class, [
                    'label' => 'loyalty.ui.target_variants',
                    'multiple' => true,
                    'required' => true,
                    'mapped' => false,
                ]),
        };

        // Add the appropriate data transformer
        $transformer = match ($scope) {
            EarningRuleScopeType::Taxon => $this->taxonsToCodesTransformer,
            EarningRuleScopeType::Product => $this->productsToCodesTransformer,
            EarningRuleScopeType::Variant => $this->variantsToCodesTransformer,
        };
        $builder->get('targets')->addModelTransformer($transformer);

        $builder
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
            ->add('channels', ChannelChoiceType::class, [
                'label' => 'sylius.ui.channels',
                'multiple' => true,
                'expanded' => true,
                'constraints' => [new Assert\Count(min: 1, minMessage: 'loyalty.earning_rule.channels.not_empty')],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
                'required' => false,
            ])
        ;

        // Pre-populate targets from entity's targetCodes
        $builder->addEventListener(FormEvents::POST_SET_DATA, static function (FormEvent $event): void {
            $rule = $event->getData();
            if ($rule === null) {
                return;
            }

            $codes = $rule->getTargetCodes();
            if (count($codes) > 0) {
                $event->getForm()->get('targets')->setData($codes);
            }
        });

        // On submit: set scopeType + targetCodes from the form
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) use ($scope): void {
            $rule = $event->getData();
            if ($rule === null) {
                return;
            }

            $rule->setScopeType($scope);

            $codes = $event->getForm()->get('targets')->getData();
            $rule->setTargetCodes(is_array($codes) ? $codes : []);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('scope', 'taxon');
        $resolver->setAllowedValues('scope', ['taxon', 'product', 'variant']);
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_earning_rule';
    }
}
