import { Controller } from '@hotwired/stimulus';

/**
 * Minimal Stimulus controller for the cart loyalty points widget.
 * Handles only the "clear/remove" action — everything else goes
 * through the Live Component data-model binding (same as coupon).
 */
export default class extends Controller {
    static targets = ['input', 'renderButton'];

    /**
     * Clear applied loyalty points: set the model value to 0,
     * then trigger a Live Component re-render so the cart is saved.
     */
    clear() {
        if (this.hasInputTarget) {
            this.inputTarget.value = '0';
            this.inputTarget.dispatchEvent(new Event('input', { bubbles: true }));
        }

        if (this.hasRenderButtonTarget) {
            this.renderButtonTarget.click();
        }
    }
}
