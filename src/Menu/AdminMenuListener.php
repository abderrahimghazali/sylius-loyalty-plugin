<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addLoyaltyMenu(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        // Add "Loyalty Accounts" under Customers menu
        $customersMenu = $menu->getChild('customers');
        if ($customersMenu !== null) {
            $customersMenu
                ->addChild('loyalty_accounts', [
                    'route' => 'loyalty_admin_account_index',
                    'extras' => ['routes' => [
                        ['route' => 'loyalty_admin_loyalty_account_show'],
                        ['route' => 'loyalty_admin_point_adjustment'],
                    ]],
                ])
                ->setLabel('loyalty.ui.loyalty_accounts')
                ->setLabelAttribute('icon', 'star')
            ;
        }

        // Add tier management + config under Configuration menu
        $configMenu = $menu->getChild('configuration');
        if ($configMenu !== null) {
            $configMenu
                ->addChild('loyalty_tiers', [
                    'route' => 'loyalty_admin_tier_index',
                    'extras' => ['routes' => [
                        ['route' => 'loyalty_admin_tier_create'],
                        ['route' => 'loyalty_admin_tier_update'],
                    ]],
                ])
                ->setLabel('loyalty.ui.loyalty_tiers')
                ->setLabelAttribute('icon', 'trophy')
            ;

            $configMenu
                ->addChild('loyalty_rules', [
                    'route' => 'loyalty_admin_rule_index',
                    'extras' => ['routes' => [
                        ['route' => 'loyalty_admin_rule_create'],
                        ['route' => 'loyalty_admin_rule_update'],
                    ]],
                ])
                ->setLabel('loyalty.ui.loyalty_rules')
                ->setLabelAttribute('icon', 'percent')
            ;

            $configMenu
                ->addChild('loyalty_configuration', [
                    'route' => 'loyalty_admin_configuration_index',
                ])
                ->setLabel('loyalty.ui.loyalty_configuration')
                ->setLabelAttribute('icon', 'cog')
            ;
        }
    }
}
