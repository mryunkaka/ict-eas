import './bootstrap';

import Alpine from 'alpinejs';
import DataTable from 'datatables.net-dt';
import { wireIctRequestIdentifier } from './helpers/ict-request-identifier';
import { wireAutoCompressImageUploads } from './helpers/auto-compress-image-upload';

window.Alpine = Alpine;
window.DataTable = DataTable;
window.wireIctRequestIdentifier = wireIctRequestIdentifier;
window.wireAutoCompressImageUploads = wireAutoCompressImageUploads;

const sidebarPreferenceKey = 'ict-eas:sidebar-open';

document.addEventListener('alpine:init', () => {
    Alpine.data('adminShell', () => ({
        sidebarOpen: false,
        activeNavFocusTimer: null,
        currentWitaLabel: '',
        clockTimer: null,
        init() {
            this.sidebarOpen = localStorage.getItem(sidebarPreferenceKey) === 'true';

            this.$watch('sidebarOpen', (value) => {
                localStorage.setItem(sidebarPreferenceKey, String(value));

                if (value) {
                    this.queueActiveNavFocus(220);
                }
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    this.sidebarOpen = false;
                }
            });

            this.updateWitaClock();
            this.clockTimer = window.setInterval(() => this.updateWitaClock(), 1000);
            this.queueActiveNavFocus(0);
        },
        destroy() {
            if (this.activeNavFocusTimer) {
                window.clearTimeout(this.activeNavFocusTimer);
            }

            if (this.clockTimer) {
                window.clearInterval(this.clockTimer);
            }
        },
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        closeSidebar() {
            this.sidebarOpen = false;
        },
        navigateFromSidebar() {
            this.closeSidebar();
        },
        queueActiveNavFocus(delay = 0) {
            if (this.activeNavFocusTimer) {
                window.clearTimeout(this.activeNavFocusTimer);
            }

            this.activeNavFocusTimer = window.setTimeout(() => {
                this.$nextTick(() => this.focusActiveNav());
            }, delay);
        },
        focusActiveNav() {
            const activeNav = this.$root.querySelector('.ui-admin-nav-item.is-active');
            const sidebarScroll = this.$refs.sidebarScroll;

            if (!activeNav || !sidebarScroll) {
                return;
            }

            activeNav.scrollIntoView({
                block: 'center',
                inline: 'nearest',
                behavior: 'smooth',
            });
        },
        updateWitaClock() {
            const formatter = new Intl.DateTimeFormat('id-ID', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Asia/Makassar',
            });

            this.currentWitaLabel = `${formatter.format(new Date())} WITA`;
        },
    }));

    Alpine.data('dashboardStats', (url) => ({
        url,
        values: {},
        loading: true,
        error: false,
        updatedAt: null,
        timerId: null,
        init() {
            this.load();
            this.timerId = window.setInterval(() => this.load({ silent: true }), 30000);
        },
        destroy() {
            if (this.timerId) {
                window.clearInterval(this.timerId);
            }
        },
        async load({ silent = false } = {}) {
            if (!silent) {
                this.loading = true;
            }

            try {
                const response = await fetch(this.url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();

                this.values = payload.stats ?? {};
                this.updatedAt = payload.generated_at ?? null;
                this.error = false;
            } catch (error) {
                this.error = true;
            } finally {
                this.loading = false;
            }
        },
        formatValue(key) {
            const value = this.values[key];

            if (value === null || value === undefined) {
                return this.loading ? '...' : '0';
            }

            return new Intl.NumberFormat('id-ID').format(value);
        },
    }));

    Alpine.data('approvalCenter', (historyMap = {}) => ({
        historyMap,
        reviewModalOpen: false,
        reviewAction: 'revise',
        reviewUrl: '',
        reviewSubject: '',
        historyModalOpen: false,
        historySubject: '',
        currentHistoryItems: [],
        openReviewModal(url, subject, action = 'revise') {
            this.reviewModalOpen = true;
            this.reviewAction = action;
            this.reviewUrl = url;
            this.reviewSubject = subject;
        },
        closeReviewModal() {
            this.reviewModalOpen = false;
            this.reviewAction = 'revise';
            this.reviewUrl = '';
            this.reviewSubject = '';

            if (this.$refs.reviewForm) {
                this.$refs.reviewForm.reset();
            }
        },
        openHistoryModal(id, subject) {
            this.historySubject = subject;
            this.currentHistoryItems = this.historyMap?.[id] ?? [];
            this.historyModalOpen = true;
        },
        closeHistoryModal() {
            this.historyModalOpen = false;
            this.historySubject = '';
            this.currentHistoryItems = [];
        },
    }));

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

    Alpine.data('ictRequestForm', ({ formKey, ptaEnabled, quotationMode, initialItems, initialGlobalQuotations }) => ({
        key: `ict-eas:${formKey}`,
        ptaEnabled,
        quotationMode,
        items: [],
        globalQuotations: [],
        nextItemId: 0,
        isHydratingDraft: false,
        init() {
            this.items = (initialItems?.length ? initialItems : [this.defaultItem()])
                .map((item) => this.normalizeItem(item));
            this.globalQuotations = (initialGlobalQuotations?.length ? initialGlobalQuotations : this.defaultQuotations())
                .map((quotation) => this.normalizeQuotation(quotation));

            const draft = localStorage.getItem(this.key);

            if (draft) {
                try {
                    const data = JSON.parse(draft);

                    this.isHydratingDraft = true;
                    this.ptaEnabled = data.ptaEnabled ?? this.ptaEnabled;
                    this.quotationMode = data.quotationMode ?? this.quotationMode;
                    this.hydrateStaticFields(data.fields ?? {});
                    this.globalQuotations = (data.globalQuotations?.length ? data.globalQuotations : this.defaultQuotations())
                        .map((quotation) => this.normalizeQuotation(quotation));
                    this.items = (data.items?.length ? data.items : [this.defaultItem()])
                        .map((item) => this.normalizeItem(item));
                } catch (error) {
                    localStorage.removeItem(this.key);
                } finally {
                    this.isHydratingDraft = false;
                }
            }

            this.$nextTick(() => this.store());
            this.$root.addEventListener('input', () => this.store());
            this.$root.addEventListener('change', () => this.store());
        },
        defaultItem() {
            return {
                id: null,
                item_name: '',
                item_category: '',
                brand_type: '',
                quantity: 1,
                unit: '',
                estimated_price: '',
                item_notes: '',
                photo_name: '',
                photo_label: '',
                photo_size_label: '',
                current_photo_name: '',
                current_photo_path: '',
                quotations: this.defaultQuotations(),
            };
        },
        defaultQuotation() {
            return {
                vendor_name: '',
                attachment_label: '',
                attachment_size_label: '',
                current_attachment_name: '',
                current_attachment_path: '',
                current_attachment_mime: '',
            };
        },
        defaultQuotations() {
            return Array.from({ length: 3 }, () => this.defaultQuotation());
        },
        normalizeQuotation(quotation = {}) {
            return {
                ...this.defaultQuotation(),
                ...quotation,
            };
        },
        normalizeItem(item = {}) {
            const normalizedQuotations = (item.quotations?.length ? item.quotations : this.defaultQuotations())
                .map((quotation) => this.normalizeQuotation(quotation));

            return {
                _id: this.nextItemId++,
                ...this.defaultItem(),
                ...item,
                quotations: normalizedQuotations,
            };
        },
        hydrateStaticFields(fields) {
            Object.entries(fields).forEach(([name, value]) => {
                const field = this.$root.querySelector(`[name="${name}"]`);

                if (!field || field.type === 'file') {
                    return;
                }

                field.value = value ?? '';
            });
        },
        addItem() {
            this.items.push(this.normalizeItem({ quantity: 1 }));
            this.store();
        },
        removeItem(index) {
            if (this.items.length === 1) {
                this.items = [this.normalizeItem({ quantity: 1 })];
            } else {
                this.items.splice(index, 1);
            }

            this.store();
        },
        async handlePhotoChange(event, index) {
            const [file] = event.target.files ?? [];

            if (!file) {
                this.items[index].photo_label = '';
                this.items[index].photo_size_label = '';
                this.store();
                return;
            }

            try {
                const compressedFile = await this.compressImage(file);
                const dataTransfer = new DataTransfer();

                dataTransfer.items.add(compressedFile);
                event.target.files = dataTransfer.files;

                if (!this.items[index].photo_name) {
                    this.items[index].photo_name = this.buildShortPhotoName(compressedFile.name);
                }

                this.items[index].photo_label = compressedFile.name;
                this.items[index].photo_size_label = this.formatBytes(compressedFile.size);
            } catch (error) {
                this.items[index].photo_label = file.name;
                this.items[index].photo_size_label = this.formatBytes(file.size);
            }

            this.store();
        },
        handleQuotationChange(event, quotation) {
            const [file] = event.target.files ?? [];

            quotation.attachment_label = file ? file.name : '';
            quotation.attachment_size_label = file ? this.formatBytes(file.size) : '';
            this.store();
        },
        buildShortPhotoName(filename) {
            const baseName = filename.replace(/\.[^.]+$/, '').replace(/[^a-zA-Z0-9_-]/g, '');

            return baseName.slice(0, 15);
        },
        async compressImage(file) {
            const maxBytes = 500 * 1024;

            if (!file.type.startsWith('image/') || file.size <= maxBytes) {
                return file;
            }

            const image = await this.loadImage(file);
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d', { alpha: false });

            if (!context) {
                return file;
            }

            let width = image.width;
            let height = image.height;
            const maxDimension = 1920;

            if (width > maxDimension || height > maxDimension) {
                const scale = Math.min(maxDimension / width, maxDimension / height);

                width = Math.max(1, Math.round(width * scale));
                height = Math.max(1, Math.round(height * scale));
            }

            let quality = 0.9;
            let blob = null;

            for (let attempt = 0; attempt < 12; attempt += 1) {
                canvas.width = width;
                canvas.height = height;
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, width, height);
                context.drawImage(image, 0, 0, width, height);

                blob = await this.canvasToBlob(canvas, quality);

                if (blob && blob.size <= maxBytes) {
                    break;
                }

                if (quality > 0.35) {
                    quality -= 0.1;
                } else {
                    width = Math.max(400, Math.round(width * 0.8));
                    height = Math.max(400, Math.round(height * 0.8));
                    quality = 0.75;
                }
            }

            if (!blob) {
                return file;
            }

            const outputName = `${file.name.replace(/\.[^.]+$/, '')}.jpg`;

            return new File([blob], outputName, {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });
        },
        loadImage(file) {
            return new Promise((resolve, reject) => {
                const image = new Image();
                const objectUrl = URL.createObjectURL(file);

                image.onload = () => {
                    URL.revokeObjectURL(objectUrl);
                    resolve(image);
                };

                image.onerror = () => {
                    URL.revokeObjectURL(objectUrl);
                    reject(new Error('Gagal membaca gambar.'));
                };

                image.src = objectUrl;
            });
        },
        canvasToBlob(canvas, quality) {
            return new Promise((resolve) => {
                canvas.toBlob(resolve, 'image/jpeg', quality);
            });
        },
        formatBytes(bytes) {
            if (!bytes) {
                return '';
            }

            return `${(bytes / 1024).toFixed(0)} KB`;
        },
        collectFields() {
            const payload = {};
            const fields = this.$root.querySelectorAll('input, select, textarea');

            fields.forEach((field) => {
                if (!field.name || ['password', 'file', 'hidden'].includes(field.type) || field.name.startsWith('items[')) {
                    return;
                }

                payload[field.name] = field.value;
            });

            return payload;
        },
        store() {
            if (this.isHydratingDraft) {
                return;
            }

            const items = this.items.map(({ _id, ...item }) => item);
            const globalQuotations = this.globalQuotations.map((quotation) => ({ ...quotation }));

            localStorage.setItem(this.key, JSON.stringify({
                ptaEnabled: this.ptaEnabled,
                quotationMode: this.quotationMode,
                fields: this.collectFields(),
                globalQuotations,
                items,
            }));
        },
        clearDraft() {
            localStorage.removeItem(this.key);
            this.items = [this.normalizeItem({ quantity: 1 })];
            this.globalQuotations = this.defaultQuotations().map((quotation) => this.normalizeQuotation(quotation));
            this.$root.reset();
            this.ptaEnabled = false;
            this.quotationMode = 'global';
        },
        clearOnSubmit() {
            localStorage.removeItem(this.key);
        },
    }));
});

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    window.wireAutoCompressImageUploads?.();
});
