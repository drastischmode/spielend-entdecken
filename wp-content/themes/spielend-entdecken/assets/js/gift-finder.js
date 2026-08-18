(function() {
    'use strict';

    const GiftFinder = {
        form: null,
        currentStep: 1,
        steps: 3,
        data: { alter: '', interesse: '', budget: '' },

        init() {
            this.form = document.getElementById('gift-finder-form');
            if (!this.form) return;

            this.bindEvents();
            this.updateProgress();
        },

        bindEvents() {
            // Radio-Änderungen
            this.form.addEventListener('change', (e) => {
                if (e.target.matches('input[type="radio"]')) {
                    this.data[e.target.name] = e.target.value;
                    this.validateStep(this.currentStep);
                }
            });

            // Weiter-Buttons
            this.form.querySelectorAll('.gift-finder-btn-next').forEach(btn => {
                btn.addEventListener('click', () => this.nextStep(parseInt(btn.dataset.next)));
            });

            // Zurück-Buttons
            this.form.querySelectorAll('.gift-finder-btn-back').forEach(btn => {
                btn.addEventListener('click', () => this.prevStep(parseInt(btn.dataset.prev)));
            });

            // Submit (Ergebnisse laden)
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.loadResults();
            });

            // Neustart
            this.form.querySelector('.gift-finder-btn-restart')?.addEventListener('click', () => this.restart());
        },

        validateStep(step) {
            const field = this.form.querySelector(`input[name="${this.getStepField(step)}"]:checked`);
            const nextBtn = this.form.querySelector(`.gift-finder-btn-next[data-next="${step + 1}"]`);
            if (nextBtn) nextBtn.disabled = !field;
        },

        getStepField(step) {
            return ['alter', 'interesse', 'budget'][step - 1];
        },

        nextStep(step) {
            if (!this.validateStep(this.currentStep)) return;
            this.showStep(step);
        },

        prevStep(step) {
            this.showStep(step);
        },

        showStep(step) {
            this.currentStep = step;
            this.form.querySelectorAll('.gift-finder-step').forEach(el => {
                el.hidden = parseInt(el.dataset.step) !== step;
            });
            this.updateProgress();
            this.updateActions();
        },

        updateProgress() {
            document.querySelectorAll('.gift-finder-progress__step').forEach(el => {
                const step = parseInt(el.dataset.step);
                el.classList.toggle('active', step <= this.currentStep);
            });
            const fill = document.querySelector('.gift-finder-progress__fill');
            if (fill) fill.style.width = `${(this.currentStep / this.steps) * 100}%`;
            document.querySelector('.gift-finder-progress').setAttribute('aria-valuenow', this.currentStep);
        },

        updateActions() {
            const backBtn = this.form.querySelector('.gift-finder-btn-back');
            const nextBtn = this.form.querySelector('.gift-finder-btn-next');
            const submitBtn = this.form.querySelector('button[type="submit"]');

            if (backBtn) backBtn.hidden = this.currentStep === 1;
            if (nextBtn) nextBtn.hidden = this.currentStep === this.steps;
            if (submitBtn) submitBtn.hidden = this.currentStep !== this.steps;
        },

        async loadResults() {
            const formData = new FormData(this.form);
            const btn = this.form.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Lade Empfehlungen...';

            try {
                const response = await fetch(spielend_gift_finder.ajaxurl, {
                    method: 'POST',
                    body: new FormData(this.form),
                    credentials: 'same-origin'
                });
                const data = await response.json();

                if (data.success) {
                    this.showResults(data.data.html);
                } else {
                    alert(data.data.message || 'Fehler beim Laden der Empfehlungen');
                }
            } catch (e) {
                alert('Fehler beim Laden der Empfehlungen');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Empfehlungen anzeigen';
            }
        },

        showResults(html) {
            this.form.hidden = true;
            const results = document.querySelector('.gift-finder-results');
            const grid = results.querySelector('.gift-finder-results__grid');
            results.hidden = false;
            results.querySelector('.gift-finder-results__grid').innerHTML = html;

            // Re-init Wishlist/QuickView Buttons
            if (typeof initWishlistButtons === 'function') initWishlistButtons();
            if (typeof initQuickView === 'function') initQuickView();

            // Scroll to results
            results.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        restart() {
            this.currentStep = 1;
            this.data = { alter: '', interesse: '', budget: '' };
            this.form.reset();
            this.form.hidden = false;
            document.querySelector('.gift-finder-results').hidden = true;
            this.showStep(1);
        }
    };

    document.addEventListener('DOMContentLoaded', () => GiftFinder.init());
})();