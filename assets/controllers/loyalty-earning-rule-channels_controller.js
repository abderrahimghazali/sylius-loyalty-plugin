import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['checkbox', 'tab', 'pane', 'tabList'];

    toggle(event) {
        const channelCode = event.params.channel;
        const checked = event.target.checked;

        // Find the matching tab and pane
        const tab = this.tabTargets.find(t => t.dataset.channel === channelCode);
        const pane = this.paneTargets.find(p => p.dataset.channel === channelCode);

        if (!tab || !pane) return;

        if (checked) {
            tab.style.display = '';
            // If no tab is currently active, activate this one
            if (!this.tabTargets.some(t => t.style.display !== 'none' && t.querySelector('.nav-link.active'))) {
                this._activate(tab, pane);
            }
        } else {
            const wasActive = tab.querySelector('.nav-link.active') !== null;
            tab.style.display = 'none';
            pane.classList.remove('show', 'active');
            tab.querySelector('.nav-link')?.classList.remove('active');

            // Clear the points value for unchecked channels
            const input = pane.querySelector('input[type="number"], input[type="text"]');
            if (input) input.value = '0';

            // If this was the active tab, activate the first visible one
            if (wasActive) {
                const firstVisible = this.tabTargets.find(t => t.style.display !== 'none');
                if (firstVisible) {
                    const code = firstVisible.dataset.channel;
                    const firstPane = this.paneTargets.find(p => p.dataset.channel === code);
                    this._activate(firstVisible, firstPane);
                }
            }
        }
    }

    _activate(tab, pane) {
        // Deactivate all
        this.tabTargets.forEach(t => t.querySelector('.nav-link')?.classList.remove('active'));
        this.paneTargets.forEach(p => p.classList.remove('show', 'active'));

        // Activate target
        tab.querySelector('.nav-link')?.classList.add('active');
        pane?.classList.add('show', 'active');
    }
}
