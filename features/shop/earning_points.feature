@loyalty @shop
Feature: Earning loyalty points
    In order to accumulate rewards
    As a customer
    I want to earn points when I complete orders

    Background:
        Given the store operates on a single channel in "United States"
        And the loyalty configuration has 1 point per currency unit
        And the store has a product "T-Shirt" priced at "$50.00"

    Scenario: Earning points on a completed order
        Given I am a logged in customer with email "shop@example.com"
        And I have placed an order for "T-Shirt"
        When the order is completed
        Then my loyalty account should have 50 points earned
        And I should see a "Points earned" transaction in my history

    Scenario: Points are revoked when an order is cancelled
        Given I am a logged in customer with 50 earned points from order "#000001"
        When order "#000001" is cancelled
        Then my loyalty points should be reduced by 50
        And I should see a "Points revoked" transaction in my history

    Scenario: Points are restored when a payment is refunded
        Given I am a logged in customer who redeemed 200 points on order "#000001"
        When the payment for order "#000001" is refunded
        Then 200 points should be restored to my account
        And the restoration should be idempotent

    Scenario: Duplicate point awards are prevented
        Given I am a logged in customer with 50 earned points from order "#000001"
        When the order complete event fires again for order "#000001"
        Then my loyalty account should still have 50 points
        And no duplicate transaction should be created

    Scenario: Registration bonus is awarded
        Given the loyalty configuration has registration bonus of 100 points enabled
        When a new customer registers with email "new@example.com"
        Then the customer should receive 100 bonus points

    Scenario: First order bonus is awarded
        Given the loyalty configuration has first order bonus of 50 points enabled
        And I am a logged in customer with email "first@example.com"
        When I complete my first order
        Then I should receive 50 first order bonus points

    Scenario: Birthday bonus is awarded
        Given the loyalty configuration has birthday bonus of 200 points enabled
        And customer "birthday@example.com" has a birthday today
        When the birthday bonus command runs
        Then the customer should receive 200 birthday bonus points
        And running the command again should not award duplicate points
