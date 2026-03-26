@loyalty @admin
Feature: Configuring the loyalty system
    In order to customize the loyalty program
    As an administrator
    I want to manage global loyalty settings

    Background:
        Given I am logged in as an administrator
        And the default loyalty configuration exists

    Scenario: Updating the earning rate
        When I set points per currency unit to 2
        And I save the configuration
        Then customers should earn 2 points per currency unit

    Scenario: Updating the redemption rate
        When I set the redemption rate to 50
        And I save the configuration
        Then 50 points should equal 1 currency unit for redemption

    Scenario: Toggling bonus events
        When I disable the registration bonus
        And I save the configuration
        Then new registrations should not receive bonus points

    Scenario: Setting points expiry
        When I set points expiry to 180 days
        And I save the configuration
        Then earned points should expire after 180 days
