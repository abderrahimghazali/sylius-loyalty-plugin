@loyalty @api
Feature: Loyalty redemption REST API
    In order to integrate loyalty with headless storefronts
    As an API consumer
    I want to apply and remove loyalty points via the API

    Background:
        Given the store operates on a single channel in "United States"
        And customer "shop@example.com" has a loyalty account with 500 points
        And customer "shop@example.com" has an order with token "abc123"

    Scenario: Applying loyalty points via API
        Given I am authenticated as "shop@example.com"
        When I send a POST request to "/api/v2/shop/orders/abc123/loyalty-redemption" with:
            | pointsToRedeem | 200 |
        Then the response status should be 200
        And the response should contain "pointsRedeemed" equal to 200

    Scenario: Removing loyalty points via API
        Given I am authenticated as "shop@example.com"
        And I have applied 200 points to order "abc123"
        When I send a DELETE request to "/api/v2/shop/orders/abc123/loyalty-redemption"
        Then the response status should be 200
        And the response should contain "pointsRedeemed" equal to 0

    Scenario: Cannot apply points to another customer's order
        Given I am authenticated as "other@example.com"
        When I send a POST request to "/api/v2/shop/orders/abc123/loyalty-redemption" with:
            | pointsToRedeem | 100 |
        Then the response status should be 403

    Scenario: Cannot exceed available balance
        Given I am authenticated as "shop@example.com"
        When I send a POST request to "/api/v2/shop/orders/abc123/loyalty-redemption" with:
            | pointsToRedeem | 9999 |
        Then the response status should be 422
        And the response should contain "Insufficient points"

    Scenario: Cannot apply negative points
        Given I am authenticated as "shop@example.com"
        When I send a POST request to "/api/v2/shop/orders/abc123/loyalty-redemption" with:
            | pointsToRedeem | -5 |
        Then the response status should be 400

    Scenario: Unauthenticated request is denied
        When I send a POST request to "/api/v2/shop/orders/abc123/loyalty-redemption" without authentication
        Then the response status should be 401
