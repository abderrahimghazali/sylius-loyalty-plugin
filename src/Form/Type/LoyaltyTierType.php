<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyTierType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('color', ColorType::class, [
                'label' => 'loyalty.form.color',
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 7),
                    new Assert\Regex(
                        pattern: '/^#[0-9A-Fa-f]{6}$/',
                        message: 'Color must be a valid hex code (e.g. #FF5733).',
                    ),
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
                'required' => false,
            ])
        ;

        // Auto-generate code from name and position from minPoints
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event): void {
            $tier = $event->getData();

            if ($tier === null) {
                return;
            }

            // Auto-generate code from name if empty (only on create)
            if (empty($tier->getCode()) && !empty($tier->getName())) {
                $slugger = new AsciiSlugger();
                $code = strtoupper((string) $slugger->slug($tier->getName(), '_'));
                $tier->setCode($code);
            }

            // Auto-set position from minPoints
            $tier->setPosition($tier->getMinPoints());
        });
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_tier';
    }
}
