import * as Turbo from '@hotwired/turbo';
import 'alpine-turbo-drive-adapter';
import Alpine from 'alpinejs';
import collapse from './plugins/collapse';

// Cache clearing bridge for Turbo
const clearTurboCache = () => {
    if (Turbo.cache && typeof Turbo.cache.clear === 'function') {
        Turbo.cache.clear();
    } else if (Turbo.session && typeof Turbo.session.clearCache === 'function') {
        Turbo.session.clearCache();
    }
};

window.Turbo = Object.create(Turbo);
window.Turbo.clearCache = clearTurboCache;

// Register Alpine plugins before Alpine.start()
Alpine.plugin(collapse);
window.Alpine = Alpine;

// Disable Turbo automatic form submission interception so our in-app AJAX action interceptor handles all game forms
if (Turbo.config && Turbo.config.forms) {
    Turbo.config.forms.mode = 'off';
} else if (typeof Turbo.setFormMode === 'function') {
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

// Force full browser reload onto maintenance screen when any Turbo visit receives HTTP 503
document.addEventListener('turbo:before-fetch-response', (event) => {
    const fetchResponse = event.detail?.fetchResponse;
    if (fetchResponse && fetchResponse.statusCode === 503) {
        event.preventDefault();
        window.location.replace('/');
    }
});

// Update stats and trigger page lifecycle hooks on Turbo navigation
document.addEventListener('turbo:load', () => {
    if (typeof window.tbsRefreshStats === 'function') {
        window.tbsRefreshStats();
    }
    if (typeof window.tbsInitTurnstile === 'function' && document.getElementById('turnstile-widget')) {
        window.tbsInitTurnstile(false);
    }
    if (typeof window.tbsInitAuthTurnstile === 'function' && (document.getElementById('turnstile-login-widget') || document.getElementById('turnstile-register-widget'))) {
        window.tbsInitAuthTurnstile();
    }
    if (typeof window.tbsMountForgotTurnstile === 'function' && document.getElementById('turnstile-forgot-widget')) {
        window.tbsMountForgotTurnstile(false);
    }
    if (typeof window.tbsMountDiscordTurnstile === 'function' && document.getElementById('turnstile-discord-widget')) {
        window.tbsMountDiscordTurnstile(false);
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

// Interactive TBS Confetti Cannon (Purple, Violet, Gold & Emerald Syndicate Particles)
window.tbsFireConfetti = function(mode = 'normal') {
    try {
        let canvas = document.getElementById('tbs-confetti-canvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.id = 'tbs-confetti-canvas';
            canvas.style.position = 'fixed';
            canvas.style.inset = '0';
            canvas.style.width = '100vw';
            canvas.style.height = '100vh';
            canvas.style.pointerEvents = 'none';
            canvas.style.zIndex = '99999';
            document.body.appendChild(canvas);
        }

        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const colors = ['#a855f7', '#7c3aed', '#f59e0b', '#fbbf24', '#c084fc', '#10b981', '#e879f9', '#ffffff'];
        const count = mode === 'mega' ? 160 : 85;
        const particles = [];

        const spawnBurst = (originX, originY, burstCount) => {
            for (let i = 0; i < burstCount; i++) {
                const angle = (Math.random() * Math.PI) + Math.PI; // Upward cone
                const speed = 7 + Math.random() * 14;
                particles.push({
                    x: originX + (Math.random() - 0.5) * 80,
                    y: originY,
                    vx: Math.cos(angle) * speed * (0.6 + Math.random() * 0.8),
                    vy: Math.sin(angle) * speed - (Math.random() * 5),
                    size: 6 + Math.random() * 7,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    rotation: Math.random() * 360,
                    rotationSpeed: (Math.random() - 0.5) * 14,
                    gravity: 0.28 + Math.random() * 0.12,
                    drag: 0.985,
                    wobble: Math.random() * 10,
                    wobbleSpeed: 0.08 + Math.random() * 0.08,
                    opacity: 1,
                    shape: Math.random() > 0.35 ? 'rect' : 'circle'
                });
            }
        };

        if (mode === 'mega') {
            spawnBurst(canvas.width * 0.2, canvas.height * 0.75, Math.floor(count / 2));
            spawnBurst(canvas.width * 0.8, canvas.height * 0.75, Math.floor(count / 2));
            setTimeout(() => spawnBurst(canvas.width * 0.5, canvas.height * 0.65, 90), 220);
        } else {
            spawnBurst(canvas.width * 0.5, canvas.height * 0.7, count);
        }

        let animFrame;
        const render = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let active = 0;

            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];
                if (p.opacity <= 0.02 || p.y > canvas.height + 40) continue;
                active++;

                p.vx *= p.drag;
                p.vy = (p.vy * p.drag) + p.gravity;
                p.x += p.vx + Math.sin(p.wobble) * 1.2;
                p.y += p.vy;
                p.rotation += p.rotationSpeed;
                p.wobble += p.wobbleSpeed;

                if (p.y > canvas.height * 0.75) {
                    p.opacity -= 0.022;
                }

                ctx.save();
                ctx.globalAlpha = Math.max(0, p.opacity);
                ctx.translate(p.x, p.y);
                ctx.rotate((p.rotation * Math.PI) / 180);
                ctx.fillStyle = p.color;

                if (p.shape === 'circle') {
                    ctx.beginPath();
                    ctx.arc(0, 0, p.size * 0.5, 0, Math.PI * 2);
                    ctx.fill();
                } else {
                    ctx.fillRect(-p.size / 2, -p.size / 3, p.size, p.size * 0.66);
                }
                ctx.restore();
            }

            if (active > 0) {
                animFrame = requestAnimationFrame(render);
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        };

        if (animFrame) cancelAnimationFrame(animFrame);
        requestAnimationFrame(render);
    } catch (err) {
        console.error('Confetti error:', err);
    }
};

// Global In-App Form Action Interceptor (Zero Reloads, Debounced, No Duplicate Submits)
if (!window.tbsFormInterceptorInitialized) {
    window.tbsFormInterceptorInitialized = true;

    document.addEventListener('submit', async function(e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM' || e.defaultPrevented) return;

        // Strict guard: ONLY intercept explicit Laravel POST action forms
        const actionAttr = form.getAttribute('action') || '';
        const methodAttr = (form.getAttribute('method') || '').toLowerCase();
        const hasCsrf = form.querySelector('input[name="_token"]');

        if (!actionAttr ||
            methodAttr !== 'post' ||
            !hasCsrf ||
            e.defaultPrevented ||
            form.hasAttribute('@submit.prevent') ||
            form.hasAttribute('x-on:submit.prevent') ||
            form.hasAttribute('data-no-async') ||
            (form.hasAttribute('onsubmit') && form.getAttribute('onsubmit').includes('tbsQuickConsume')) ||
            form.dataset.tbsConsuming === 'true' ||
            window.location.pathname.startsWith('/chat') ||
            actionAttr.includes('/chat') ||
            actionAttr.includes('/logout') ||
            actionAttr.includes('/login') ||
            actionAttr.includes('/register') ||
            actionAttr.includes('/wachtwoord') ||
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

            const icon = submitBtn.querySelector('i.fa-solid, i.fa-regular, i.fa-brands');
            if (icon && !submitBtn.querySelector('.fa-spinner, .fa-circle-notch')) {
                icon.className = 'fa-solid fa-circle-notch fa-spin mr-1.5';
            }
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

            if (response.status === 503) {
                window.location.replace('/');
                return;
            }

            // 1. JSON response from PreserveSessionFlashOnAjax:
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                const data = await response.json();

                if (data.maintenance || data.reload) {
                    window.location.replace(data.redirect || '/');
                    return;
                }

                // Redirect away if needed (solitary confinement / arena / logout)
                if (data.redirect) {
                    const redirectFlashMsg = (data.flash && data.flash.message) ? data.flash.message : (data.message || null);
                    const redirectFlashType = (data.flash && data.flash.type) ? data.flash.type : (data.success ? 'success' : 'error');
                    if (redirectFlashMsg && window.tbsToast) {
                        window.tbsToast(redirectFlashMsg, redirectFlashType);
                    }
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
                const flashMsg = (data.flash && data.flash.message) ? data.flash.message : (data.message || null);
                const flashType = (data.flash && data.flash.type) ? data.flash.type : (data.success ? 'success' : 'error');
                if (flashMsg && window.tbsToast) {
                    window.tbsToast(flashMsg, flashType);
                    if (flashType === 'error') {
                        window.tbsVibrate([30, 40, 30]);
                    } else {
                        window.tbsVibrate(25);
                        if (actionAttr.includes('/battlepass/claim') && typeof window.tbsFireConfetti === 'function') {
                            window.tbsFireConfetti(actionAttr.includes('claim-all') ? 'mega' : 'normal');
                        }
                    }
                }

                if (flashMsg) {
                    window.dispatchEvent(new CustomEvent('tbs-action-feedback', {
                        detail: {
                            message: flashMsg,
                            type: flashType,
                            success: Boolean(data.success)
                        }
                    }));
                }

                // Background refresh the main container to show updated counts/states without reload
                try {
                    const refreshUrl = new URL(window.location.href);
                    refreshUrl.searchParams.set('_tbs_ts', String(Date.now()));
                    const getRes = await fetch(refreshUrl.toString(), {
                        cache: 'no-store',
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
                            if (window._prisonCountdownInterval) {
                                clearInterval(window._prisonCountdownInterval);
                                window._prisonCountdownInterval = null;
                            }
                            curMain.innerHTML = newMain.innerHTML;
                            if (window.Alpine && window.Alpine.initTree) {
                                window.Alpine.initTree(curMain);
                            }
                            if (typeof window.tbsInitTurnstile === 'function' && curMain.querySelector('#turnstile-widget')) {
                                window.tbsInitTurnstile(true);
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
                if (window._prisonCountdownInterval) {
                    clearInterval(window._prisonCountdownInterval);
                    window._prisonCountdownInterval = null;
                }
                curMain.innerHTML = newMain.innerHTML;
                if (window.Alpine && window.Alpine.initTree) {
                    window.Alpine.initTree(curMain);
                }
                if (typeof window.tbsInitTurnstile === 'function' && curMain.querySelector('#turnstile-widget')) {
                    window.tbsInitTurnstile(true);
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
