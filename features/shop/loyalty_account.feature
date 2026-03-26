@loyalty @shop
Feature: Viewing my loyalty account
    In order to track my loyalty points
    As a logged-in customer
    I want to see my balance, tier, and transaction history

    Background:
        Given the store operates on a single channel in "United States"
        And I am a logged in customer with email "shop@example.com"
        And my loyalty account has 500 points with Silver tier

    Scenario: Viewing loyalty points balance
        When I visit my loyalty account page
        Then I should see my available points as "500"
        And I should see my tier badge as "Silver"

    Scenario: Transaction history is paginated
        Given I have 30 transactions in my loyalty history
        When I visit my loyalty account page
        Then I should see 15 transactions on the first page
        And I should see pagination controls

    Scenario: Points expiry warning is shown
        Given I have 100 points expiring within 30 days
        When I visit my loyalty account page
        Then I should see a warning about expiring points
