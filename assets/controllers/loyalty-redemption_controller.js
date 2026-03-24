import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller for the checkout loyalty points redemption widget.
 *
 * Targets:
 *   - input: the points input field
 *   - discount: element showing the discount amount
 *   - orderTotal: element showing the updated order total
 *   - balance: element showing available points balance
 *   - error: element for displaying validation errors
 *   - redeemAll: the "Use all points" button
 *
 * Values:
 *   - token: the order tokenValue (for API calls)
 *   - balance: available points balance (int)
 *   - rate: redemption rate (points per currency unit)
 *   - currency: currency symbol
 */
export default class extends Controller {
    static targets = ['input', 'discount', 'orderTotal', 'balance', 'error', 'redeemAll'];
    static values = {
        token: String,
        balance: Number,
        rate: Number,
        currency: { type: String, default: '€' },
    };

    connect() {
        this._debounceTimer = null;
    }

    disconnect() {
        if (this._debounceTimer) {
            clearTimeout(this._debounceTimer);
        }
    }

    /**
     * Called on input change — debounced to avoid excessive API calls.
     */
    onPointsInput() {
        this.clearError();

        if (this._debounceTimer) {
            clearTimeout(this._debounceTimer);
        }

        this._debounceTimer = setTimeout(() => this.applyRedemption(), 400);
    }

    /**
     * "Use all points" button handler.
     */
    useAll() {
        this.inputTarget.value = this.balanceValue;
        this.applyRedemption();
    }

    /**
     * Clear redemption entirely.
     */
    async clear() {
        this.inputTarget.value = '';
        this.clearError();

        try {
            const response = await fetch(`/api/v2/shop/orders/${this.tokenValue}/loyalty-redemption`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
            });

            const data = await response.json();
            this.updateDisplay(0, data.orderTotal);
        } catch {
            this.showError('Failed to clear redemption.');
        }
    }

    /**
     * Send the redemption request to the API.
     */
    async applyRedemption() {
        const points = parseInt(this.inputTarget.value, 10);

        if (isNaN(points) || points <= 0) {
            this.clear();
            return;
        }

        if (points > this.balanceValue) {
            this.showError(`You only have ${this.balanceValue} points available.`);
            return;
        }

        try {
            const response = await fetch(`/api/v2/shop/orders/${this.tokenValue}/loyalty-redemption`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ pointsToRedeem: points }),
            });

            const data = await response.json();

            if (!response.ok) {
                this.showError(data.message || 'Failed to apply points.');
                return;
            }

            this.inputTarget.value = data.pointsRedeemed;
            this.updateDisplay(data.discountAmount, data.orderTotal);
        } catch {
            this.showError('Network error. Please try again.');
        }
    }

    /**
     * Update the discount and total display.
     */
    updateDisplay(discountCents, totalCents) {
        const discountFormatted = (discountCents / 100).toFixed(2);
        const totalFormatted = (totalCents / 100).toFixed(2);

        if (this.hasDiscountTarget) {
            this.discountTarget.textContent = discountCents > 0
                ? `-${this.currencyValue}${discountFormatted}`
                : '';
        }

        if (this.hasOrderTotalTarget) {
            this.orderTotalTarget.textContent = `${this.currencyValue}${totalFormatted}`;
        }
    }

    showError(message) {
        if (this.hasErrorTarget) {
            this.errorTarget.textContent = message;
            this.errorTarget.classList.remove('d-none');
        }
    }

    clearError() {
        if (this.hasErrorTarget) {
            this.errorTarget.textContent = '';
            this.errorTarget.classList.add('d-none');
        }
    }
}
