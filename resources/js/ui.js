/**
 * UI motion helpers — scroll reveal + animated counters.
 * Both degrade gracefully: no IntersectionObserver → content simply shows.
 */

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* Scroll reveal: add .reveal (optionally style="--reveal-delay: 120ms") */
function initReveal() {
    const nodes = document.querySelectorAll('.reveal');
    if (nodes.length === 0) return;

    if (!('IntersectionObserver' in window) || prefersReducedMotion()) {
        nodes.forEach((n) => n.classList.add('revealed'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -6% 0px' },
    );

    nodes.forEach((n) => observer.observe(n));
}

/* Counters: <span data-counter="12000" data-counter-suffix="+">0</span> */
function animateCounter(el) {
    const target = parseFloat(el.dataset.counter ?? '0');
    const suffix = el.dataset.counterSuffix ?? '';
    const decimals = parseInt(el.dataset.counterDecimals ?? '0', 10);
    const duration = 1600;

    if (prefersReducedMotion()) {
        el.textContent = target.toLocaleString(undefined, { maximumFractionDigits: decimals }) + suffix;
        return;
    }

    const start = performance.now();
    const easeOut = (t) => 1 - Math.pow(1 - t, 3);

    function frame(now) {
        const progress = Math.min((now - start) / duration, 1);
        const value = target * easeOut(progress);
        el.textContent = value.toLocaleString(undefined, {
            minimumFractionDigits: progress === 1 ? decimals : 0,
            maximumFractionDigits: decimals,
        }) + suffix;
        if (progress < 1) requestAnimationFrame(frame);
    }

    requestAnimationFrame(frame);
}

function initCounters() {
    const nodes = document.querySelectorAll('[data-counter]');
    if (nodes.length === 0) return;

    if (!('IntersectionObserver' in window)) {
        nodes.forEach(animateCounter);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.4 },
    );

    nodes.forEach((n) => observer.observe(n));
}

document.addEventListener('DOMContentLoaded', () => {
    initReveal();
    initCounters();
});