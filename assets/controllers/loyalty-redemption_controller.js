import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller for the cart loyalty points redemption widget.
 *
 * Targets:
 *   - input: the points input field
 *   - error: element for displaying validation errors
 *   - refresh: hidden button that triggers a live component re-render
 *
 * Values:
 *   - token: the order tokenValue (for API calls)
 *   - balance: available points balance (int)
 *   - rate: redemption rate (points per currency unit)
 *   - currency: currency symbol
 */
export default class extends Controller {
    static targets = ['input', 'error', 'refresh'];
    static values = {
        token: String,
        balance: Number,
        rate: Number,
        currency: { type: String, default: '€' },
    };

    /**
     * "Apply points" button handler.
     */
    async applyPoints() {
        this.clearError();

        const points = parseInt(this.inputTarget.value, 10);

        if (isNaN(points) || points <= 0) {
            this.showError('Please enter a valid number of points.');
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

            this._triggerRefresh();
        } catch {
            this.showError('Network error. Please try again.');
        }
    }

    /**
     * Remove redemption (trash icon handler).
     */
    async clear() {
        this.clearError();

        try {
            await fetch(`/api/v2/shop/orders/${this.tokenValue}/loyalty-redemption`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
            });

            this._triggerRefresh();
        } catch {
            this.showError('Failed to clear redemption.');
        }
    }

    /**
     * Trigger the parent live component to re-render,
     * so the cart summary updates with the new totals.
     */
    _triggerRefresh() {
        if (this.hasRefreshTarget) {
            this.refreshTarget.click();
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
