<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SyliusLoyaltyExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        // Load resource configurations so Sylius picks them up
        $loader->load('resources/loyalty_account.yaml');
        $loader->load('resources/point_transaction.yaml');
        $loader->load('resources/loyalty_tier.yaml');
        $loader->load('resources/loyalty_configuration.yaml');

        // Load grid configurations
        $loader->load('grids/admin/loyalty_account.yaml');
        $loader->load('grids/admin/point_transaction.yaml');
        $loader->load('grids/admin/loyalty_tier.yaml');

        // Load twig hooks configuration
        $loader->load('config.yaml');
    }
}
