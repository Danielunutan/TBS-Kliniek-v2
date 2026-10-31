/**
 * Alpine.js Collapse Plugin (Self-contained Relative Module)
 * Enables smooth expansion and collapsing of elements via `x-collapse`.
 */
export default function collapsePlugin(Alpine) {
    Alpine.directive('collapse', collapse);
    collapse.inline = (el, { modifiers }) => {
        if (!modifiers.includes('min')) return;
        el._x_doShow = () => {};
        el._x_doHide = () => {};
    };

    function collapse(el, { modifiers }) {
        const duration = modifierValue(modifiers, 'duration', 250) / 1000;
        const floor = modifierValue(modifiers, 'min', 0);
        const fullyHide = !modifiers.includes('min');

        if (!el._x_isShown) {
            el.style.height = `${floor}px`;
        }
        if (!el._x_isShown && fullyHide) {
            el.hidden = true;
        }
        if (!el._x_isShown) {
            el.style.overflow = 'hidden';
        }

        const setFunction = (el2, styles) => {
            const revertFunction = Alpine.setStyles(el2, styles);
            return styles.height ? () => {} : revertFunction;
        };

        const transitionStyles = {
            transitionProperty: 'height',
            transitionDuration: `${duration}s`,
            transitionTimingFunction: 'cubic-bezier(0.4, 0.0, 0.2, 1)',
        };

        el._x_transition = {
            in(before = () => {}, after = () => {}) {
                if (fullyHide) el.hidden = false;
                if (fullyHide) el.style.display = null;
                let current = el.getBoundingClientRect().height;
                el.style.height = 'auto';
                const full = el.getBoundingClientRect().height;
                if (current === full) {
                    current = floor;
                }
                Alpine.transition(el, Alpine.setStyles, {
                    during: transitionStyles,
                    start: { height: current + 'px' },
                    end: { height: full + 'px' },
                }, () => {
                    el._x_isShown = true;
                }, () => {
                    if (Math.abs(el.getBoundingClientRect().height - full) < 1) {
                        el.style.overflow = null;
                    }
                });
            },
            out(before = () => {}, after = () => {}) {
                const full = el.getBoundingClientRect().height;
                Alpine.transition(el, setFunction, {
                    during: transitionStyles,
                    start: { height: full + 'px' },
                    end: { height: floor + 'px' },
                }, () => {
                    el.style.overflow = 'hidden';
                }, () => {
                    el._x_isShown = false;
                    if (el.style.height === `${floor}px` && fullyHide) {
                        el.style.display = 'none';
                        el.hidden = true;
                    }
                });
            },
        };
    }
}

function modifierValue(modifiers, key, fallback) {
    if (modifiers.indexOf(key) === -1) return fallback;
    const rawValue = modifiers[modifiers.indexOf(key) + 1];
    if (!rawValue) return fallback;
    if (key === 'duration') {
        const match = rawValue.match(/([0-9]+)ms/);
        if (match) return match[1];
    }
    if (key === 'min') {
        const match = rawValue.match(/([0-9]+)px/);
        if (match) return match[1];
    }
    return rawValue;
}
