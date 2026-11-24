import { initAjaxForm } from './ajax-form';
import { initBookingWizard } from './booking-wizard';
import { initHomepageMotion } from './homepage-motion';

initHomepageMotion(document.querySelector('.home-page'));

const aboutPage = document.querySelector('.about-page');
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (aboutPage && !prefersReducedMotion && 'IntersectionObserver' in window) {
    const revealGroups = [
        ['.about-intro-copy > *', 80],
        ['.about-details > *', 65],
        ['.about-team-photo, .about-services-heading, .about-values-heading', 0],
        ['.about-service-card', 100],
        ['.about-value-item', 90],
    ];

    revealGroups.forEach(([selector, stagger]) => {
        aboutPage.querySelectorAll(selector).forEach((element, index) => {
            element.classList.add('about-reveal');
            element.style.setProperty('--about-reveal-delay', `${Math.min(index * stagger, 360)}ms`);
        });
    });

    aboutPage.classList.add('about-motion-enabled');

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.14, rootMargin: '0px 0px -7% 0px' });

    aboutPage.querySelectorAll('.about-reveal').forEach((element) => revealObserver.observe(element));
}

const backToTop = document.querySelector('.back-to-top');

if (backToTop) {
    const updateBackToTop = () => {
        backToTop.hidden = window.scrollY < 480 && document.activeElement !== backToTop;
    };

    window.addEventListener('scroll', updateBackToTop, { passive: true });
    window.addEventListener('pageshow', updateBackToTop);
    backToTop.addEventListener('blur', () => requestAnimationFrame(updateBackToTop));
    backToTop.addEventListener('click', (event) => {
        event.preventDefault();
        document.querySelector('#page-top').focus({ preventScroll: true });
        const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth';
        window.scrollTo({ top: 0, behavior });
    });
    updateBackToTop();
}

const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#site-nav');
const siteHeader = document.querySelector('.site-header');

if (siteHeader) {
    const updateHeaderSize = () => {
        siteHeader.classList.toggle('is-compact', window.scrollY > 72);
    };

    window.addEventListener('scroll', updateHeaderSize, { passive: true });
    window.addEventListener('pageshow', updateHeaderSize);
    updateHeaderSize();
}

menuButton?.addEventListener('click', () => {
    const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
    setNavigationState(!isOpen);
    navigation?.classList.toggle('open', !isOpen);
});

const setNavigationState = (isOpen) => {
    menuButton?.setAttribute('aria-expanded', String(isOpen));
    menuButton?.setAttribute('aria-label', isOpen ? 'Close site navigation' : 'Open site navigation');
    const menuLabel = menuButton?.querySelector('.menu-toggle-label');
    if (menuLabel) menuLabel.textContent = isOpen ? 'Close' : 'Menu';
};

const closeNavigation = () => {
    setNavigationState(false);
    navigation?.classList.remove('open');
};

document.addEventListener('click', (event) => {
    if (event.target instanceof Node && !navigation?.contains(event.target) && !menuButton?.contains(event.target)) {
        closeNavigation();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && menuButton?.getAttribute('aria-expanded') === 'true') {
        closeNavigation();
        menuButton.focus();
    }
});

const bookingWizard = document.querySelector('[data-booking-wizard]');
if (bookingWizard) initBookingWizard(bookingWizard);
document.querySelectorAll('[data-ajax-form]').forEach(initAjaxForm);

document.querySelectorAll('[data-seo-editor]').forEach((form) => {
    const previewTitle = form.querySelector('[data-seo-preview-title]');
    const previewDescription = form.querySelector('[data-seo-preview-description]');
    const previewUrl = form.querySelector('[data-seo-preview-url]');
    if (!previewTitle || !previewDescription || !previewUrl) return;
    const title = form.querySelector('[name="seo_title"]');
    const description = form.querySelector('[name="seo_description"]');
    const canonical = form.querySelector('[name="canonical_url"]');
    const updatePreview = () => {
        previewTitle.textContent = title?.value.trim() || form.dataset.seoDefaultTitle || '';
        previewDescription.textContent = description?.value.trim() || form.dataset.seoDefaultDescription || '';
        previewUrl.textContent = canonical?.value.trim() || form.dataset.seoDefaultUrl || '';
    };
    form.addEventListener('input', updatePreview);
    updatePreview();
});

const analyticsConsent = document.querySelector('[data-analytics-consent]');
const analyticsConfig = window.siteAnalyticsConfig;
let analyticsLoaded = false;
let analyticsAllowed = false;
const loadAnalytics = () => {
    if (analyticsLoaded || !analyticsConfig) return;
    analyticsLoaded = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = (...args) => window.dataLayer.push(args);
    if (analyticsConfig.gtm) {
        window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
        const script = document.createElement('script');
        script.src = `https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(analyticsConfig.gtm)}`;
        script.async = true;
        document.head.append(script);
    } else if (analyticsConfig.ga4) {
        window.gtag('consent', 'default', { analytics_storage: 'granted' });
        const script = document.createElement('script');
        script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(analyticsConfig.ga4)}`;
        script.async = true;
        document.head.append(script);
        window.gtag('js', new Date());
        window.gtag('config', analyticsConfig.ga4, { send_page_view: true });
    }
};
if (analyticsConsent) {
    const storedChoice = window.localStorage.getItem('hmc-analytics-consent');
    analyticsAllowed = storedChoice === 'accept';
    if (!storedChoice) analyticsConsent.hidden = false;
    if (storedChoice === 'accept') loadAnalytics();
    analyticsConsent.addEventListener('click', (event) => {
        const button = event.target.closest('[data-analytics-choice]');
        if (!button) return;
        const choice = button.dataset.analyticsChoice;
        window.localStorage.setItem('hmc-analytics-consent', choice);
        analyticsAllowed = choice === 'accept';
        analyticsConsent.hidden = true;
        if (analyticsAllowed) loadAnalytics();
        else window.gtag?.('consent', 'update', { analytics_storage: 'denied' });
    });
    document.querySelector('[data-analytics-open]')?.addEventListener('click', () => { analyticsConsent.hidden = false; });
}
window.addEventListener('site-analytics-event', (event) => {
    if (!analyticsLoaded || !analyticsAllowed || !/^(booking_form_started|service_selected|booking_submitted|contact_form_submitted|whatsapp_clicked|phone_clicked)$/.test(event.detail?.name ?? '')) return;
    if (analyticsConfig.gtm) window.dataLayer.push({ event: event.detail.name });
    else window.gtag?.('event', event.detail.name);
});
document.querySelector('[data-booking-wizard]')?.addEventListener('focusin', () => {
    if (window.sessionStorage.getItem('hmc-booking-started')) return;
    window.sessionStorage.setItem('hmc-booking-started', '1');
    window.dispatchEvent(new CustomEvent('site-analytics-event', { detail: { name: 'booking_form_started' } }));
});
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;
    const href = link.href.toLowerCase();
    if (href.startsWith('tel:')) window.dispatchEvent(new CustomEvent('site-analytics-event', { detail: { name: 'phone_clicked' } }));
    if (href.includes('wa.me') || href.includes('whatsapp')) window.dispatchEvent(new CustomEvent('site-analytics-event', { detail: { name: 'whatsapp_clicked' } }));
});

document.querySelectorAll('[data-copy-value]').forEach((button) => {
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copyValue ?? '');
            const label = button.textContent;
            button.textContent = 'Copied';
            window.setTimeout(() => { button.textContent = label; }, 1500);
        } catch {
            button.textContent = 'Copy unavailable';
        }
    });
});

const formatUploadSize = (bytes) => {
    if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

document.querySelectorAll('input[type="file"]').forEach((input, index) => {
    if (input.dataset.dropzoneReady === 'true') return;
    input.dataset.dropzoneReady = 'true';

    const dropzone = document.createElement('div');
    dropzone.className = 'upload-dropzone';
    dropzone.innerHTML = `<span class="upload-dropzone-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 15.5v3A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5v-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="upload-dropzone-copy"><strong>Drop images here or <u>browse files</u></strong><small class="upload-dropzone-hint">${input.multiple ? 'Select one or more images' : 'Choose an image from your device'}</small></span><span class="upload-dropzone-selection" aria-live="polite"></span><span class="upload-preview-list" hidden></span>`;
    input.parentNode.insertBefore(dropzone, input);
    dropzone.append(input);
    const selection = dropzone.querySelector('.upload-dropzone-selection');
    const previews = dropzone.querySelector('.upload-preview-list');
    const hint = dropzone.querySelector('.upload-dropzone-hint');
    const maxFiles = Number(input.dataset.maxFiles || 0);
    const maxFileSize = Number(input.dataset.maxSize || 0) * 1024;
    if (hint && maxFiles) hint.textContent = `Up to ${maxFiles} images, ${formatUploadSize(maxFileSize)} each`;
    let previewUrls = [];

    const renderSelection = () => {
        previewUrls.forEach((url) => URL.revokeObjectURL(url));
        previewUrls = [];
        const files = [...(input.files ?? [])];
        const invalid = maxFiles && files.length > maxFiles
            ? `Choose no more than ${maxFiles} images.`
            : maxFileSize && files.some((file) => file.size > maxFileSize)
                ? `Each image must be ${formatUploadSize(maxFileSize)} or smaller.`
                : '';
        input.setCustomValidity(invalid);
        if (invalid) {
            selection.textContent = invalid;
            dropzone.classList.add('has-error');
            return;
        }
        dropzone.classList.remove('has-error');
        selection.textContent = files.length ? `${files.length} ${files.length === 1 ? 'image' : 'images'} selected · ${formatUploadSize(files.reduce((total, file) => total + file.size, 0))}` : '';
        previews.replaceChildren();
        previews.hidden = files.length === 0;
        files.slice(0, 6).forEach((file) => {
            const item = document.createElement('span');
            item.className = 'upload-preview-item';
            item.title = `${file.name} · ${formatUploadSize(file.size)}`;
            if (file.type.startsWith('image/')) {
                const image = document.createElement('img');
                const url = URL.createObjectURL(file);
                previewUrls.push(url);
                image.src = url;
                image.alt = '';
                item.append(image);
            }
            const name = document.createElement('small');
            name.textContent = file.name;
            item.append(name);
            previews.append(item);
        });
        if (files.length > 6) {
            const extra = document.createElement('span');
            extra.className = 'upload-preview-more';
            extra.textContent = `+${files.length - 6}`;
            previews.append(extra);
        }
        dropzone.classList.toggle('has-files', files.length > 0);
        dropzone.closest('form')?.querySelector('[data-upload-status]')?.replaceChildren();
    };

    input.addEventListener('change', renderSelection);
    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragging');
    });
    dropzone.addEventListener('dragleave', (event) => {
        if (!dropzone.contains(event.relatedTarget)) dropzone.classList.remove('is-dragging');
    });
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragging');
        const files = [...(event.dataTransfer?.files ?? [])];
        if (!files.length) return;
        const accepted = (input.accept || '').split(',').map((type) => type.trim().toLowerCase()).filter(Boolean);
        const invalid = accepted.length && files.some((file) => !accepted.some((type) => type.endsWith('/*') ? file.type.startsWith(type.slice(0, -1)) : type.startsWith('.') ? file.name.toLowerCase().endsWith(type) : file.type.toLowerCase() === type));
        const tooMany = maxFiles && files.length > maxFiles;
        const tooLarge = maxFileSize && files.some((file) => file.size > maxFileSize);
        if (invalid || tooMany || tooLarge) {
            selection.textContent = invalid ? `Please choose ${accepted.join(', ')} files.` : tooMany
                ? `Choose no more than ${maxFiles} images.`
                : `Each image must be ${formatUploadSize(maxFileSize)} or smaller.`;
            input.setCustomValidity(selection.textContent);
            dropzone.classList.add('has-error');
            return;
        }
        input.setCustomValidity('');
        dropzone.classList.remove('has-error');
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
});

document.querySelectorAll('form[enctype="multipart/form-data"]').forEach((form, formIndex) => {
    if (!form.querySelector('input[type="file"]')) return;
    const submit = form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
    if (!submit) return;

    const feedback = document.createElement('div');
    feedback.className = 'upload-feedback';
    feedback.hidden = true;
    feedback.innerHTML = `<span class="upload-progress-label" data-upload-status role="status" aria-live="polite"></span><span class="upload-progress-track" role="progressbar" aria-label="Image upload progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span data-upload-progress-bar></span></span>`;
    submit.before(feedback);
    const status = feedback.querySelector('[data-upload-status]');
    const track = feedback.querySelector('[role="progressbar"]');
    const bar = feedback.querySelector('[data-upload-progress-bar]');
    let uploading = false;

    form.addEventListener('submit', (event) => {
        const selectedFiles = [...form.querySelectorAll('input[type="file"]')].some((input) => !input.disabled && input.files?.length);
        if (!selectedFiles) return;
        if (uploading) {
            event.preventDefault();
            return;
        }
        event.preventDefault();
        uploading = true;
        const originalLabel = submit.tagName === 'INPUT' ? submit.value : submit.innerHTML;
        const setSubmitLabel = (label) => {
            if (submit.tagName === 'INPUT') submit.value = label;
            else submit.textContent = label;
        };
        const request = new XMLHttpRequest();
        request.open((form.method || 'POST').toUpperCase(), form.action);
        request.setRequestHeader('Accept', 'application/json');
        const csrf = form.querySelector('[name="_token"]')?.value;
        if (csrf) request.setRequestHeader('X-CSRF-TOKEN', csrf);
        request.timeout = 180000;
        submit.disabled = true;
        setSubmitLabel('Uploading images...');
        feedback.hidden = false;
        track.setAttribute('aria-valuenow', '0');
        bar.style.width = '0%';
        status.textContent = 'Preparing your images...';
        form.setAttribute('aria-busy', 'true');

        request.upload.addEventListener('progress', (uploadEvent) => {
            if (!uploadEvent.lengthComputable) return;
            const percent = Math.round(uploadEvent.loaded / uploadEvent.total * 100);
            bar.style.width = `${percent}%`;
            track.setAttribute('aria-valuenow', String(percent));
            status.textContent = percent >= 100 ? 'Images uploaded. Saving your changes...' : `Uploading images... ${percent}%`;
            setSubmitLabel(percent >= 100 ? 'Saving...' : `Uploading ${percent}%`);
        });
        request.addEventListener('load', () => {
            const contentType = request.getResponseHeader('content-type') ?? '';
            let response = null;
            if (contentType.includes('application/json')) {
                try { response = JSON.parse(request.responseText); } catch { response = null; }
            }
            if (request.status >= 200 && request.status < 300 && response?.redirect) {
                status.textContent = 'Upload complete. Opening your booking confirmation...';
                bar.style.width = '100%';
                track.setAttribute('aria-valuenow', '100');
                window.location.assign(response.redirect);
                return;
            }
            if (request.status >= 200 && request.status < 300 && !response?.errors) {
                status.textContent = 'Upload complete. Refreshing...';
                bar.style.width = '100%';
                track.setAttribute('aria-valuenow', '100');
                window.location.assign(request.responseURL || window.location.href);
                return;
            }
            const errors = response?.errors ? Object.values(response.errors).flat().join(' ') : '';
            status.textContent = request.status === 413 ? 'The selected images are too large. Choose smaller files.' : errors || response?.message || 'Upload failed. Check the image and try again.';
            feedback.classList.add('has-error');
            if (response?.errors) {
                const firstField = Object.keys(response.errors)[0];
                const fieldName = firstField.split('.')[0];
                form.dispatchEvent(new CustomEvent('form-invalid', { detail: fieldName }));
                const field = form.querySelector('[name="' + CSS.escape(fieldName) + '"]')
                    ?? form.querySelector('[name="' + CSS.escape(fieldName) + '[]"]');
                field?.setAttribute('aria-invalid', 'true');
                const formStatus = form.querySelector('[data-form-status]');
                if (formStatus) {
                    formStatus.textContent = status.textContent;
                    formStatus.focus();
                }
            }
        });
        request.addEventListener('error', () => {
            status.textContent = 'The connection was interrupted. Please try uploading again.';
            feedback.classList.add('has-error');
        });
        request.addEventListener('timeout', () => {
            status.textContent = 'The upload took too long. Try fewer or smaller images.';
            feedback.classList.add('has-error');
        });
        request.addEventListener('loadend', () => {
            uploading = false;
            submit.disabled = false;
            if (submit.tagName === 'INPUT') submit.value = originalLabel;
            else submit.innerHTML = originalLabel;
            form.removeAttribute('aria-busy');
        });
        request.send(new FormData(form));
    });
});

document.querySelectorAll('[data-markdown-field]').forEach((editor) => {
    const input = editor.querySelector('textarea');
    editor.addEventListener('click', (event) => {
        const button = event.target.closest('[data-markdown-prefix]');
        if (!button) return;
        const selected = input.value.slice(input.selectionStart, input.selectionEnd);
        input.focus();
        input.setRangeText(button.dataset.markdownPrefix + selected + (button.dataset.markdownSuffix ?? ''), input.selectionStart, input.selectionEnd, 'end');
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
});
document.querySelectorAll('[data-project-gallery]').forEach((gallery) => {
    const filters = gallery.querySelector('.gallery-filters');
    const dialog = gallery.querySelector('dialog');
    if (!filters || !dialog) return;
    filters.hidden = false;
    let openLink;
    gallery.addEventListener('click', (event) => {
        const filter = event.target.closest('[data-gallery-filter]');
        if (filter) {
            filters.querySelectorAll('button').forEach((button) => button.setAttribute('aria-pressed', String(button === filter)));
            gallery.querySelectorAll('[data-gallery-category]').forEach((item) => {
                item.hidden = !!filter.dataset.galleryFilter && item.dataset.galleryCategory !== filter.dataset.galleryFilter;
            });
        }
        const link = event.target.closest('[data-gallery-zoom]');
        if (!link || !dialog.showModal) return;
        event.preventDefault();
        openLink = link;
        const image = dialog.querySelector('img');
        image.src = link.href;
        image.alt = link.querySelector('img').alt;
        dialog.querySelector('p').textContent = link.closest('figure').querySelector('figcaption')?.textContent ?? '';
        dialog.showModal();
    });
    dialog.addEventListener('close', () => openLink?.focus());
});

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const password = toggle.closest('.login-control')?.querySelector('input');
        if (!password) return;
        const isVisible = password.type === 'text';
        password.type = isVisible ? 'password' : 'text';
        toggle.textContent = isVisible ? 'Show' : 'Hide';
        toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
        toggle.setAttribute('aria-pressed', String(!isVisible));
    });
});
