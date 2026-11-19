const revealGroups = [
    ['.hero-copy > *', 'rise'],
    ['.hero-visual', 'image'],
    ['.services-showcase-copy > :not(svg), .story-copy > *', 'rise'],
    ['.service-showcase-card', 'card'],
    ['.story-photo', 'image'],
    ['.details-heading > *, .steps-heading > *', 'rise'],
    ['.details-card, .steps-card', 'card'],
    ['.steps-connector svg', 'arrow'],
    ['.steps-action, .fresh-start-copy > *', 'rise'],
    ['.fresh-start-art', 'art'],
    ['.footer-grid > div, .footer-bottom', 'rise'],
];

const revealStyles = {
    rise: [{ opacity: 0.3, translate: '0 18px' }, { opacity: 1, translate: '0 0' }],
    card: [{ opacity: 0.25, translate: '0 32px', scale: 0.97 }, { opacity: 1, translate: '0 0', scale: 1 }],
    image: [
        { opacity: 0.65, clipPath: 'inset(0 14% 0 0)', scale: 1.025 },
        { opacity: 1, clipPath: 'inset(0 -12% 0 -25%)', scale: 1 },
    ],
    arrow: [{ opacity: 0.25, translate: '-10px 0' }, { opacity: 1, translate: '0 0' }],
    art: [{ opacity: 0.35, scale: 1.05 }, { opacity: 1, scale: 1 }],
};

export function initHomepageMotion(homepage) {
    if (!homepage || !('IntersectionObserver' in window) || !Element.prototype.animate) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (reducedMotion.matches) {
        return;
    }

    const revealOptions = new Map();
    const activeAnimations = new Map();
    const observer = new IntersectionObserver((entries) => {
        for (const { target, isIntersecting, boundingClientRect } of entries) {
            if (!isIntersecting && boundingClientRect.bottom > 0) {
                continue;
            }

            observer.unobserve(target);
            if (!isIntersecting || target.matches(':focus-within')) {
                continue;
            }

            const { style, order } = revealOptions.get(target);
            const animation = target.animate(revealStyles[style], {
                duration: style === 'image' || style === 'art' ? 950 : 620,
                delay: Math.min(order, 4) * 75,
                easing: 'cubic-bezier(.2,.75,.25,1)',
                fill: 'backwards',
            });

            activeAnimations.set(target, animation);
            animation.finished.then(
                () => activeAnimations.delete(target),
                () => activeAnimations.delete(target),
            );
        }
    });

    const siblingOrders = new Map();
    for (const [selector, style] of revealGroups) {
        for (const target of homepage.querySelectorAll(selector)) {
            const parent = target.parentElement;
            const order = siblingOrders.get(parent) ?? 0;
            siblingOrders.set(parent, order + 1);
            revealOptions.set(target, { style, order });
            observer.observe(target);
        }
    }

    homepage.addEventListener('focusin', ({ target }) => {
        for (const [element, animation] of activeAnimations) {
            if (element.contains(target)) {
                animation.cancel();
            }
        }
    });

    reducedMotion.addEventListener('change', ({ matches }) => {
        if (matches) {
            observer.disconnect();
            activeAnimations.forEach((animation) => animation.cancel());
        }
    });
}
