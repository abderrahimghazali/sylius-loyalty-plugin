<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addLoyaltyMenu(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        // Add "Loyalty" section under Customers menu
        $customersMenu = $menu->getChild('customers');
        if ($customersMenu !== null) {
            $customersMenu
                ->addChild('loyalty_accounts', [
                    'route' => 'loyalty_admin_loyalty_account_index',
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
                    'route' => 'loyalty_admin_loyalty_tier_index',
                ])
                ->setLabel('loyalty.ui.loyalty_tiers')
                ->setLabelAttribute('icon', 'trophy')
            ;

            $configMenu
                ->addChild('loyalty_configuration', [
                    'route' => 'loyalty_admin_configuration',
                ])
                ->setLabel('loyalty.ui.loyalty_configuration')
                ->setLabelAttribute('icon', 'cog')
            ;
        }
    }
}
