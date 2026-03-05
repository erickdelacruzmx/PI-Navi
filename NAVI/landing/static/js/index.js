document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const revealElements = document.querySelectorAll('.reveal');
    const revealGroups = new Map();

    revealElements.forEach((element) => {
        const group = element.closest('section') || document.body;
        if (!revealGroups.has(group)) {
            revealGroups.set(group, []);
        }
        revealGroups.get(group).push(element);
    });

    revealGroups.forEach((groupElements) => {
        groupElements.forEach((element, index) => {
            if (!element.style.getPropertyValue('--reveal-delay')) {
                element.style.setProperty('--reveal-delay', `${Math.min(index * 110, 550)}ms`);
            }
        });
    });

    if (!prefersReducedMotion && revealElements.length) {
        revealElements.forEach((element) => element.classList.add('is-pending'));

        const revealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.remove('is-pending');
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            { threshold: 0.16, rootMargin: '0px 0px -8% 0px' }
        );

        revealElements.forEach((element) => revealObserver.observe(element));
    } else {
        revealElements.forEach((element) => {
            element.classList.remove('is-pending');
            element.classList.add('is-visible');
        });
    }

    const countElements = document.querySelectorAll('.count-up');
    if (countElements.length) {
        const formatValue = (value) => `+${Math.round(value).toLocaleString('es-MX')}`;

        const animateCount = (element) => {
            const target = Number(element.dataset.target || 0);
            const duration = 1450;
            const startTime = performance.now();

            const tick = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                element.textContent = formatValue(target * eased);

                if (progress < 1) {
                    window.requestAnimationFrame(tick);
                }
            };

            window.requestAnimationFrame(tick);
        };

        if (!prefersReducedMotion) {
            const countObserver = new IntersectionObserver(
                (entries, observer) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        animateCount(entry.target);
                        observer.unobserve(entry.target);
                    });
                },
                { threshold: 0.5 }
            );

            countElements.forEach((element) => countObserver.observe(element));
        } else {
            countElements.forEach((element) => {
                const target = Number(element.dataset.target || 0);
                element.textContent = formatValue(target);
            });
        }
    }

    if (!prefersReducedMotion) {
        const supportsHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        const spotlightCards = supportsHover
            ? document.querySelectorAll(
                  '.edu-card, .goal-card, .plan-card, .help-card, .impact-stat, .info-box, .testimonial, .support-box, .hero-metric'
              )
            : [];

        spotlightCards.forEach((card) => {
            card.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                const x = ((event.clientX - rect.left) / rect.width) * 100;
                const y = ((event.clientY - rect.top) / rect.height) * 100;
                card.style.setProperty('--pointer-x', `${x.toFixed(2)}%`);
                card.style.setProperty('--pointer-y', `${y.toFixed(2)}%`);
            });

            card.addEventListener('pointerleave', () => {
                card.style.removeProperty('--pointer-x');
                card.style.removeProperty('--pointer-y');
            });
        });

        const hero = document.querySelector('.hero');
        const parallaxNodes = document.querySelectorAll('.orb');

        if (hero && parallaxNodes.length) {
            let ticking = false;

            const applyParallax = () => {
                const heroRect = hero.getBoundingClientRect();
                const offset = Math.max(Math.min(-heroRect.top * 0.08, 20), -20);

                parallaxNodes.forEach((node, index) => {
                    const signed = index % 2 === 0 ? offset : offset * -0.8;
                    node.style.setProperty('--parallax-offset', `${signed}px`);
                    node.classList.add('parallax-shift');
                });

                ticking = false;
            };

            const onScroll = () => {
                if (ticking) {
                    return;
                }

                ticking = true;
                window.requestAnimationFrame(applyParallax);
            };

            applyParallax();
            window.addEventListener('scroll', onScroll, { passive: true });
        }
    }
});