# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-03-26

### Added
- Points earning on orders with tier-based multipliers
- Checkout redemption widget (Stimulus) with live discount updates
- Point expiry via `loyalty:expire-points` CLI command
- Birthday bonus via `loyalty:birthday-bonus` CLI command
- Registration, first-order, and birthday bonus events
- Tier system with earning multipliers (promote only, never demote)
- Admin panel for accounts, transactions, manual adjustments, and configuration
- Customer loyalty account page with transaction history
- REST API for redemption and account access
- DB-driven configuration editable from admin UI
- Workflow listeners for cancel/refund point restoration
- `LoyaltyOrderTrait` for extending the host app Order entity
- `Deduct` transaction type for negative adjustments
- Stimulus controller auto-discovery via `assets/package.json`
- GitHub Actions CI with PHPUnit and PHPStan (level 5)

[Unreleased]: https://github.com/abderrahimghazali/sylius-loyalty-plugin/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/abderrahimghazali/sylius-loyalty-plugin/releases/tag/v2.0.0
