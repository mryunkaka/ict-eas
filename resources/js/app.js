import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('persistedForm', (formKey) => ({
        key: `ict-eas:${formKey}`,
        init() {
            const draft = localStorage.getItem(this.key);

            if (draft) {
                const data = JSON.parse(draft);

                Object.entries(data).forEach(([name, value]) => {
                    const field = this.$root.querySelector(`[name="${name}"]`);

                    if (field && field.type !== 'file') {
                        field.value = value ?? '';
                    }
                });
            }

            this.$root.addEventListener('input', () => this.store());
            this.$root.addEventListener('change', () => this.store());
        },
        store() {
            const payload = {};
            const fields = this.$root.querySelectorAll('input, select, textarea');

            fields.forEach((field) => {
                if (!field.name || ['password', 'file', 'hidden'].includes(field.type)) {
                    return;
                }

                payload[field.name] = field.value;
            });

            localStorage.setItem(this.key, JSON.stringify(payload));
        },
        clearDraft() {
            localStorage.removeItem(this.key);
            this.$root.reset();
        },
        clearOnSubmit() {
            localStorage.removeItem(this.key);
        },
    }));
});

Alpine.start();
