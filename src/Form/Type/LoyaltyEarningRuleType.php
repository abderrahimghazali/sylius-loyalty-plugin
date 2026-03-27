<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Form\Type;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyEarningRuleChannelConfiguration;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyEarningRuleType extends AbstractResourceType
{
    public function __construct(
        string $dataClass,
        private readonly ChannelRepositoryInterface $channelRepository,
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
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
                'required' => false,
            ])
            ->add('channelConfigurations', CollectionType::class, [
                'entry_type' => LoyaltyEarningRuleChannelConfigurationType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
        ;

        // Pre-populate channel configurations for all available channels
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $rule = $event->getData();
            if ($rule === null) {
                return;
            }

            /** @var ChannelInterface[] $allChannels */
            $allChannels = $this->channelRepository->findAll();

            foreach ($allChannels as $channel) {
                if ($rule->getConfigurationForChannel($channel) !== null) {
                    continue;
                }

                $config = new LoyaltyEarningRuleChannelConfiguration();
                $config->setChannel($channel);
                $config->setPointsPerProduct(1);
                $rule->addChannelConfiguration($config);
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return 'loyalty_earning_rule';
    }
}
