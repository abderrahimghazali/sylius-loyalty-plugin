<p align="center">
    <a href="https://sylius.com" target="_blank">
        <picture>
            <source media="(prefers-color-scheme: dark)" srcset="https://media.sylius.com/sylius-logo-800-dark.png">
            <source media="(prefers-color-scheme: light)" srcset="https://media.sylius.com/sylius-logo-800.png">
            <img alt="Sylius Logo" src="https://media.sylius.com/sylius-logo-800.png" width="300"/>
        </picture>
    </a>
</p>

<h1 align="center">Sylius Loyalty Plugin</h1>

<p align="center">
    A points-based loyalty and rewards system for <a href="https://sylius.com">Sylius 2.x</a> e-commerce stores.
</p>

<p align="center">
    <a href="https://packagist.org/packages/abderrahimghazali/sylius-loyalty-plugin"><img src="https://img.shields.io/packagist/v/abderrahimghazali/sylius-loyalty-plugin.svg" alt="Latest Version"/></a>
    <a href="https://packagist.org/packages/abderrahimghazali/sylius-loyalty-plugin"><img src="https://img.shields.io/packagist/php-v/abderrahimghazali/sylius-loyalty-plugin.svg" alt="PHP Version"/></a>
    <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"/></a>
    <a href="https://packagist.org/packages/abderrahimghazali/sylius-loyalty-plugin"><img src="https://img.shields.io/badge/sylius-2.x-green.svg" alt="Sylius 2.x"/></a>
    <a href="https://packagist.org/packages/abderrahimghazali/sylius-loyalty-plugin"><img src="https://img.shields.io/badge/symfony-7.x-black.svg" alt="Symfony 7.x"/></a>
</p>

---

## Overview

SyliusLoyaltyPlugin adds a complete loyalty program to any Sylius 2.x store. Customers earn points on purchases, redeem them as discounts at checkout, unlock tier-based multipliers, and receive bonus points for registration, birthdays, and first orders — all manageable from the admin panel.

### Key Features

- **Points earning** — Configurable points per currency unit on every order
- **Checkout redemption** — Spend points as a monetary discount with live total update (Stimulus)
- **Points expiry** — Automatic expiration with cron command + 30-day warnings
- **Bonus events** — Registration, birthday, and first-order bonuses (toggle on/off)
- **Tier system** — Bronze / Silver / Gold with earning multipliers (tiers only go up)
- **Admin panel** — Full management: accounts, transactions, manual adjustments, global config
- **Customer account** — Points balance, tier badge, transaction history with running balance
- **REST API** — Headless-ready endpoints for balance and checkout redemption
- **Workflow integration** — Points deducted on order complete, restored on cancel/refund

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.2 |
| Sylius | ~2.1 |
| Symfony | ^7.0 |

## Installation

```bash
composer require abderrahimghazali/sylius-loyalty-plugin
```

### 1. Register the plugin

```php
// config/bundles.php
return [
    // ...
    Abderrahim\SyliusLoyaltyPlugin\SyliusLoyaltyPlugin::class => ['all' => true],
];
```

### 2. Import configuration and routes

```yaml
# config/packages/sylius_loyalty.yaml
sylius_loyalty:
    points_per_currency_unit: 1   # 1 point per €1 spent
    redemption_rate: 100          # 100 points = €1 discount
    expiry_days: 365              # Points expire after 12 months
    bonus:
        registration: 100         # Welcome bonus
        first_order: 50           # First purchase bonus
        birthday: 200             # Annual birthday bonus
    tiers_enabled: true
```

```yaml
# config/routes/sylius_loyalty.yaml
sylius_loyalty:
    resource: '@SyliusLoyaltyPlugin/config/routes.yaml'
```

### 3. Extend your Order entity

Add the loyalty trait to your Order entity so customers can redeem points at checkout:

```php
// src/Entity/Order/Order.php
namespace App\Entity\Order;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderInterface;
use Abderrahim\SyliusLoyaltyPlugin\Entity\Order\LoyaltyOrderTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements LoyaltyOrderInterface
{
    use LoyaltyOrderTrait;

    // ...existing code...
}
```

### 4. Register the Stimulus controller (for checkout widget)

Add the plugin JS dependency to your `package.json`:

```json
{
    "dependencies": {
        "@abderrahimghazali/sylius-loyalty-plugin": "file:vendor/abderrahimghazali/sylius-loyalty-plugin/assets"
    }
}
```

Register the controller in `assets/shop/controllers.json`:

```json
{
    "controllers": {
        "@abderrahimghazali/sylius-loyalty-plugin": {
            "loyalty-redemption": {
                "enabled": true,
                "fetch": "eager"
            }
        }
    }
}
```

Then rebuild assets:

```bash
yarn install --force
yarn encore dev
```

### 5. Run migrations

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 6. Set up cron jobs

```bash
# Expire old points (run daily)
php bin/console loyalty:expire-points

# Award birthday bonuses (run daily)
php bin/console loyalty:birthday-bonus
```

## Architecture

### Domain Model

```
Customer ──1:1──▶ LoyaltyAccount ──1:N──▶ PointTransaction
                        │                    (earn/redeem/expire/adjust/bonus)
                        │
                        └───N:1──▶ LoyaltyTier
                                   (Bronze/Silver/Gold)
```

### Entities

| Entity | Purpose |
|---|---|
| `LoyaltyAccount` | Per-customer account with balance, lifetime points, tier |
| `PointTransaction` | Ledger entry — signed points, type, optional order link, expiry |
| `LoyaltyTier` | Tier with min-points threshold, earning multiplier, color |
| `LoyaltyConfiguration` | Single-row config table for admin-editable settings |

### Sylius Integration Points

| Extension Point | What It Does |
|---|---|
| `OrderProcessorInterface` (priority 5) | Applies loyalty discount adjustment after taxes |
| `sylius.order.post_complete` event | Awards earn points on order completion |
| `sylius.order.post_cancel` event | Revokes earned points |
| `sylius.customer.post_register` event | Awards registration bonus |
| `workflow.sylius_order_checkout.completed.complete` | Deducts redeemed points from balance |
| `workflow.sylius_order.completed.cancel` | Restores redeemed points |
| `workflow.sylius_payment.completed.refund` | Restores redeemed points on refund |
| `sylius.menu.admin.main` event | Adds menu items under Customers & Configuration |
| Twig hooks | Checkout widget, customer show section, account menu |

## Shop Features

### Checkout Redemption Widget

At the checkout summary step, logged-in customers see their points balance and can enter how many points to redeem. The widget uses a **Stimulus controller** for live updates — no page reload needed.

- Input field with min/max validation
- "Use all points" button
- "Clear" button to remove redemption
- Live discount and total recalculation via API
- Automatic clamping: can't exceed balance or order total

### Customer Account — Loyalty Page

Accessible from the account sidebar menu:

- Current balance + redeemable monetary value
- Tier badge with multiplier info
- Expiry warning for points expiring within 30 days
- Transaction history table with running balance column

## Admin Features

### Loyalty Accounts

Grid view of all customer loyalty accounts with balance, lifetime points, tier, and status. Click through to a detail page showing full transaction history.

### Manual Point Adjustment

From any loyalty account detail page, admins can add or deduct points with a required reason field. Positive values create an `Adjust` (credit) transaction, negative values create a `Deduct` (debit) transaction.

### Tier Management

Full CRUD for loyalty tiers under **Configuration > Loyalty Tiers**:

| Field | Description |
|---|---|
| Code | Unique identifier (e.g., `BRONZE`) |
| Name | Display name (e.g., "Bronze") |
| Min Points | Lifetime points threshold to reach this tier |
| Multiplier | Earning multiplier (e.g., 1.5x for Silver) |
| Color | Badge color (hex, rendered in admin and shop) |

### Global Configuration

Under **Configuration > Loyalty Configuration**:

- Points per currency unit
- Redemption rate (points per 1 currency unit)
- Expiry period in days
- Enable/disable tier system
- Toggle and configure bonus events (registration, birthday, first order)

Settings are stored in the database and take effect immediately without redeployment. The YAML config values serve as defaults for the initial database row.

## API Endpoints

### Shop API

```
POST   /api/v2/shop/orders/{tokenValue}/loyalty-redemption
       Body: { "pointsToRedeem": 500 }
       → { "pointsRedeemed": 500, "discountAmount": 500, "orderTotal": 9500 }

DELETE /api/v2/shop/orders/{tokenValue}/loyalty-redemption
       → { "pointsRedeemed": 0, "discountAmount": 0, "orderTotal": 10000 }

GET    /api/v2/shop/loyalty/account
       → Balance, lifetime points, tier info
```

### Admin API

```
GET    /api/v2/admin/loyalty/accounts
GET    /api/v2/admin/loyalty/accounts/{id}
PATCH  /api/v2/admin/loyalty/accounts/{id}
```

## Edge Cases Handled

- Points redemption cannot exceed available balance (clamped)
- Discount cannot exceed order total (capped, points recalculated)
- Guest checkouts cannot use loyalty points (guarded)
- Disabled accounts are excluded from earning and redemption
- Duplicate point awards are prevented (idempotent per order)
- Points are reserved at checkout, deducted only on completion
- Cancelled/refunded orders restore redeemed points (idempotent)
- Birthday bonus awarded at most once per calendar year
- Tiers only upgrade, never demote (based on lifetime points)

## Running Tests

```bash
composer install
vendor/bin/phpunit
```

## License

This plugin is released under the [MIT License](LICENSE).
