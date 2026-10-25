import * as Turbo from '@hotwired/turbo';
import 'alpine-turbo-drive-adapter';
import Alpine from 'alpinejs';

window.Turbo = Turbo;
window.Alpine = Alpine;

// Disable Turbo automatic form submission interception so our in-app AJAX action interceptor handles all game forms
if (Turbo.config && Turbo.config.forms) {
    Turbo.config.forms.mode = 'off';
}
if (typeof Turbo.setFormMode === 'function') {
    Turbo.setFormMode('off');
}

// Forward CSRF token and XMLHttpRequest header to all Turbo fetch requests
document.addEventListener('turbo:before-fetch-request', (event) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        event.detail.fetchOptions.headers['X-CSRF-TOKEN'] = token;
        event.detail.fetchOptions.headers['X-Requested-With'] = 'XMLHttpRequest';
    }
});

// Update stats and trigger page lifecycle hooks on Turbo navigation
document.addEventListener('turbo:load', () => {
    if (typeof window.tbsRefreshStats === 'function') {
        window.tbsRefreshStats();
    }
});

// Initialize Alpine
if (!window.AlpineInitialized) {
    Alpine.start();
    window.AlpineInitialized = true;
}

// Native Haptic Vibration helper for mobile touch feedback
window.tbsVibrate = function(pattern = 15) {
    if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
        try {
            navigator.vibrate(pattern);
        } catch (e) {
            // Silently ignore if unsupported or permission not granted
        }
    }
};

// Global In-App Form Action Interceptor (Zero Reloads, Debounced, No Duplicate Submits)
if (!window.tbsFormInterceptorInitialized) {
    window.tbsFormInterceptorInitialized = true;

    document.addEventListener('submit', async function(e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        // Strict guard: ONLY intercept explicit Laravel POST action forms
        const actionAttr = form.getAttribute('action') || '';
        const methodAttr = (form.getAttribute('method') || '').toLowerCase();
        const hasCsrf = form.querySelector('input[name="_token"]');

        if (!actionAttr ||
            methodAttr !== 'post' ||
            !hasCsrf ||
            form.hasAttribute('@submit.prevent') ||
            form.hasAttribute('x-on:submit.prevent') ||
            form.hasAttribute('data-no-async') ||
            window.location.pathname.startsWith('/chat') ||
            actionAttr.includes('/chat') ||
            actionAttr.includes('/logout') ||
            actionAttr.includes('/login') ||
            actionAttr.includes('/auth') ||
            form.closest('[x-data*="Messenger"]') ||
            form.closest('#chat-messages-container') ||
            form.querySelector('input[type="file"]')) {
            return;
        }

        // Hard lock against concurrent double-submits
        if (form.dataset.tbsSubmitting === 'true') {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        // Debounce: prevent duplicate submits of the same form within 1200ms
        const now = Date.now();
        if (form._lastSubmitTime && (now - form._lastSubmitTime < 1200)) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }
        form._lastSubmitTime = now;
        form.dataset.tbsSubmitting = 'true';

        e.preventDefault();
        e.stopImmediatePropagation();
        window.tbsVibrate(15);

        const savedScrollY = window.scrollY;
        const submitBtn = e.submitter || form.querySelector('button[type="submit"]');
        let origHtml = null;

        if (submitBtn) {
            submitBtn.disabled = true;
            origHtml = submitBtn.innerHTML;
            submitBtn.classList.add('opacity-75', 'cursor-wait');
        }

        try {
            const formData = new FormData(form);
            if (e.submitter && e.submitter.name && !formData.has(e.submitter.name)) {
                formData.append(e.submitter.name, e.submitter.value);
            }

            const formTargetUrl = actionAttr || (typeof form.action === 'string' ? form.action : window.location.href);

            const response = await fetch(formTargetUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-TBS-Action': 'true',
                    'Accept': 'application/json, text/html'
                }
            });

            // 1. JSON response from PreserveSessionFlashOnAjax:
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                const data = await response.json();

                // Redirect away if needed (solitary confinement / arena / logout)
                if (data.redirect) {
                    if (window.Turbo && typeof window.Turbo.visit === 'function') {
                        window.Turbo.visit(data.redirect);
                    } else {
                        window.location.href = data.redirect;
                    }
                    return;
                }

                // Apply realtime smooth stats animation
                if (data.character && window.tbsApplyStats) {
                    window.tbsApplyStats(data.character);
                }

                // Display in-app toast notification with haptic feedback
                if (data.flash && data.flash.message && window.tbsToast) {
                    window.tbsToast(data.flash.message, data.flash.type || 'success');
                    if (data.flash.type === 'error') {
                        window.tbsVibrate([30, 40, 30]);
                    } else {
                        window.tbsVibrate(25);
                    }
                }

                // Background refresh the main container to show updated counts/states without reload
                try {
                    const getRes = await fetch(window.location.href, {
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-TBS-Background-Refresh': 'true'
                        }
                    });
                    if (getRes.ok) {
                        const html = await getRes.text();
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const newMain = doc.querySelector('main#tbs-main-container');
                        const curMain = document.querySelector('main#tbs-main-container');
                        if (newMain && curMain) {
                            curMain.innerHTML = newMain.innerHTML;
                            if (window.Alpine && window.Alpine.initTree) {
                                window.Alpine.initTree(curMain);
                            }
                            window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                        }
                    }
                } catch (fetchErr) {
                    console.error('TBS Refresh Error:', fetchErr);
                }

                return;
            }

            // 2. Direct HTML redirect response fallback
            const responseUrl = response.url ? new URL(response.url) : null;
            if (responseUrl && responseUrl.pathname !== window.location.pathname) {
                if (window.Turbo && typeof window.Turbo.visit === 'function') {
                    window.Turbo.visit(response.url);
                } else {
                    window.location.href = response.url;
                }
                return;
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newMain = doc.querySelector('main#tbs-main-container');
            const curMain = document.querySelector('main#tbs-main-container');

            if (newMain && curMain) {
                curMain.innerHTML = newMain.innerHTML;
                if (window.Alpine && window.Alpine.initTree) {
                    window.Alpine.initTree(curMain);
                }
                window.scrollTo({ top: savedScrollY, behavior: 'instant' });
            }

            if (window.tbsRefreshStats) {
                window.tbsRefreshStats();
            }

        } catch (err) {
            console.error('TBS Interceptor Error:', err);
            // DO NOT call form.submit() here! Re-submitting causes duplicate database actions.
            if (window.tbsToast) {
                window.tbsToast('Er is een fout opgetreden bij het verwerken. Ververs de pagina als dit aanhoudt.', 'error');
            }
        } finally {
            form.dataset.tbsSubmitting = 'false';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-wait');
                if (origHtml) submitBtn.innerHTML = origHtml;
            }
        }
    });
}
