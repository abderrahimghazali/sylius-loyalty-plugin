<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class ShopAccountMenuListener
{
    public function addLoyaltyMenu(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu
            ->addChild('loyalty_points', [
                'route' => 'loyalty_shop_account_loyalty',
            ])
            ->setLabel('loyalty.ui.my_loyalty_points')
            ->setLabelAttribute('icon', 'tabler:star')
        ;
    }
}
