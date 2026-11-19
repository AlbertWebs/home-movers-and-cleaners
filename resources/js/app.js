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
    menuButton.setAttribute('aria-expanded', String(!isOpen));
    navigation?.classList.toggle('open', !isOpen);
});

const closeNavigation = () => {
    menuButton?.setAttribute('aria-expanded', 'false');
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

document.querySelectorAll('[data-upload-progress-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const button = form.querySelector('[type="submit"]');
        const progress = form.querySelector('[data-upload-progress]');
        const status = form.querySelector('[data-upload-status]');
        if (!button || !progress || !status || button.disabled) return;

        const request = new XMLHttpRequest();
        const defaultLabel = button.textContent;
        request.open('POST', form.action);
        request.setRequestHeader('Accept', 'application/json');
        request.setRequestHeader('X-CSRF-TOKEN', form.querySelector('[name="_token"]')?.value ?? '');
        request.timeout = 120000;
        button.disabled = true;
        button.textContent = 'Uploading';
        progress.hidden = false;
        progress.value = 0;
        status.textContent = 'Uploading images…';
        form.setAttribute('aria-busy', 'true');

        request.upload.addEventListener('progress', (uploadEvent) => {
            if (uploadEvent.lengthComputable) progress.value = Math.round(uploadEvent.loaded / uploadEvent.total * 100);
        });
        request.addEventListener('load', () => {
            if (request.status >= 200 && request.status < 300 && request.getResponseHeader('content-type')?.includes('application/json')) {
                status.textContent = 'Check the image fields and try again.';
                return;
            }
            if (request.status >= 200 && request.status < 300) {
                status.textContent = 'Upload complete. Refreshing the media library.';
                window.location.assign(request.responseURL || form.action);
                return;
            }
            status.textContent = request.status === 413 ? 'These images are too large. Choose smaller files.' : 'Upload failed. Check the file types and try again.';
        });
        request.addEventListener('error', () => { status.textContent = 'The connection was interrupted. Try uploading again.'; });
        request.addEventListener('timeout', () => { status.textContent = 'The upload took too long. Try fewer or smaller images.'; });
        request.addEventListener('loadend', () => {
            button.disabled = false;
            button.textContent = defaultLabel;
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
