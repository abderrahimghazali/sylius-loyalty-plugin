@loyalty @admin
Feature: Managing loyalty accounts
    In order to manage customer loyalty
    As an administrator
    I want to view accounts and adjust points

    Background:
        Given I am logged in as an administrator

    Scenario: Viewing the loyalty accounts list
        Given there are 5 customers with loyalty accounts
        When I browse loyalty accounts
        Then I should see 5 loyalty accounts in the list

    Scenario: Viewing a loyalty account detail
        Given customer "shop@example.com" has a loyalty account with 500 points
        When I view the loyalty account for "shop@example.com"
        Then I should see the points balance as "500"
        And I should see the transaction history

    Scenario: Manually adding points
        Given customer "shop@example.com" has a loyalty account with 500 points
        When I adjust points by 100 with reason "Compensation"
        Then the account balance should be 600
        And I should see a "Manual adjustment" transaction

    Scenario: Manually deducting points
        Given customer "shop@example.com" has a loyalty account with 500 points
        When I adjust points by -200 with reason "Correction"
        Then the account balance should be 300
        And I should see a "Manual deduction" transaction

    Scenario: Transaction history is paginated
        Given customer "shop@example.com" has 50 transactions
        When I view the loyalty account for "shop@example.com"
        Then I should see 20 transactions on the first page
        And I should see pagination controls
