@loyalty @admin
Feature: Managing loyalty tiers
    In order to incentivize loyal customers
    As an administrator
    I want to create and manage tier levels

    Background:
        Given I am logged in as an administrator

    Scenario: Creating a new tier
        When I create a tier named "Gold" with minimum 1000 points and 2x multiplier
        Then the tier should be created with code "GOLD"
        And the tier position should equal its minimum points

    Scenario: Editing a tier
        Given there is a tier "Silver" with minimum 500 points
        When I change the multiplier to 1.75
        Then the tier should be updated

    Scenario: Tier codes are unique
        Given there is a tier "Gold" with code "GOLD"
        When I try to create another tier named "Gold"
        Then I should see a validation error about duplicate code

    Scenario: Deleting a tier
        Given there is a tier "Bronze" with minimum 0 points
        When I delete the "Bronze" tier
        Then the tier should no longer exist

    Scenario: Customers are automatically upgraded
        Given there are tiers Bronze (0 pts), Silver (500 pts), Gold (1000 pts)
        And customer "shop@example.com" has 600 lifetime points
        Then the customer should be in the Silver tier

    Scenario: Customers are never downgraded
        Given customer "shop@example.com" is in the Gold tier
        And the customer's lifetime points are 600
        When tier evaluation runs
        Then the customer should still be in the Gold tier
