<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_loyalty');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('points_per_currency_unit')
                    ->defaultValue(1)
                    ->min(0)
                    ->info('Number of loyalty points earned per currency unit spent')
                ->end()
                ->integerNode('redemption_rate')
                    ->defaultValue(100)
                    ->min(1)
                    ->info('How many points equal 1 currency unit for redemption')
                ->end()
                ->integerNode('expiry_days')
                    ->defaultValue(365)
                    ->min(0)
                    ->info('Number of days before earned points expire (0 = never)')
                ->end()
                ->arrayNode('bonus')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('registration')
                            ->defaultValue(100)
                            ->min(0)
                            ->info('Bonus points for new account registration')
                        ->end()
                        ->integerNode('first_order')
                            ->defaultValue(50)
                            ->min(0)
                            ->info('Bonus points for first order')
                        ->end()
                        ->integerNode('birthday')
                            ->defaultValue(200)
                            ->min(0)
                            ->info('Bonus points awarded on customer birthday')
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('tiers_enabled')
                    ->defaultTrue()
                    ->info('Enable loyalty tier system')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
