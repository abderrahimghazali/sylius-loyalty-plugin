@loyalty @shop
Feature: Redeeming loyalty points on the cart page
    In order to get a discount using my loyalty points
    As a logged-in customer
    I want to apply points on the cart page

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "T-Shirt" priced at "$50.00"
        And I am a logged in customer with email "shop@example.com"
        And my loyalty account has 500 points

    Scenario: Applying loyalty points as a discount
        Given I have product "T-Shirt" in the cart
        When I enter 200 points to redeem
        And I click "Apply points"
        Then I should see "Loyalty points applied: 200 points"
        And the loyalty discount should be "-$2.00"
        And the cart total should reflect the discount

    Scenario: Removing applied loyalty points
        Given I have product "T-Shirt" in the cart
        And I have applied 200 loyalty points
        When I click the remove loyalty points button
        Then I should not see "Loyalty points applied"
        And the cart total should be "$50.00"

    Scenario: Cannot apply more points than available balance
        Given I have product "T-Shirt" in the cart
        When I enter 9999 points to redeem
        And I click "Apply points"
        Then the points should be clamped to my available balance

    Scenario: Loyalty widget is not shown for guest customers
        Given I am not logged in
        And I have product "T-Shirt" in the cart
        Then I should not see the loyalty points widget

    Scenario: Loyalty discount appears in checkout summary
        Given I have product "T-Shirt" in the cart
        And I have applied 200 loyalty points
        When I proceed to checkout
        Then I should see "Loyalty discount" in the order summary
