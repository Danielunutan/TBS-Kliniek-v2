/**
 * Buy Me A Coffee - Clean, Modernized Widget for TBS Kliniek
 * Stripped of obsolete .eot font requests and deprecated 'payment' feature policies
 */
(function () {
    function initBmcWidget() {
        const script = document.querySelector('script[data-name="BMC-Widget"]');
        if (!script) return;

        const holder = document.getElementById('bmc-permanent-holder') || document.body;

        // If widget button already exists in DOM or holder, avoid duplicate initialization
        if (document.getElementById('bmc-wbtn')) {
            const existingBtn = document.getElementById('bmc-wbtn');
            if (holder && existingBtn.parentNode !== holder) {
                holder.appendChild(existingBtn);
            }
            const existingIframe = document.getElementById('bmc-iframe');
            if (existingIframe && holder) {
                const overlay = existingIframe.parentElement || existingIframe;
                if (overlay && overlay.parentNode !== holder) {
                    holder.appendChild(overlay);
                }
            }
            return;
        }

        const dataId = script.dataset.id || 'tbskliniek';
        const dataColor = script.dataset.color || '#7c3aed';
        const dataPosition = script.dataset.position || 'Right';
        const dataXMargin = script.dataset.x_margin || '24';
        const dataYMargin = script.dataset.y_margin || '74';
        const dataDescription = script.dataset.description || 'Support me on Buy me a coffee!';
        const dataMessage = script.dataset.message || '';

        const isDesktop = window.matchMedia('(min-width: 480px)');

        // Floating widget button
        const btn = document.createElement('div');
        btn.id = 'bmc-wbtn';
        btn.setAttribute('role', 'button');
        btn.setAttribute('aria-label', 'Support me on Buy me a coffee!');
        btn.style.display = 'flex';
        btn.style.alignItems = 'center';
        btn.style.justifyContent = 'center';
        btn.style.width = '64px';
        btn.style.height = '64px';
        btn.style.background = dataColor;
        btn.style.color = 'white';
        btn.style.borderRadius = '32px';
        btn.style.position = 'fixed';
        if (dataPosition.toLowerCase() === 'left') {
            btn.style.left = dataXMargin + 'px';
        } else {
            btn.style.right = dataXMargin + 'px';
        }
        btn.style.bottom = dataYMargin + 'px';
        btn.style.boxShadow = '0 4px 8px rgba(0,0,0,.15)';
        btn.innerHTML = '<img src="https://cdn.buymeacoffee.com/widget/assets/coffee%20cup.svg" alt="Buy Me a Coffee">';
        btn.style.zIndex = '9999';
        btn.style.cursor = 'pointer';
        btn.style.fontWeight = '600';
        btn.style.transition = '.25s ease all';

        // Modal backdrop overlay
        const overlay = document.createElement('div');
        overlay.id = 'bmc-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '0';
        overlay.style.height = '0';
        overlay.style.background = 'rgba(0, 0, 0, 0)';
        overlay.style.textAlign = 'center';
        overlay.style.zIndex = '9999999';

        // Modal close button
        const closeBtn = document.createElement('div');
        closeBtn.id = 'bmc-close-btn';
        closeBtn.style.position = 'fixed';
        closeBtn.style.alignItems = 'center';
        closeBtn.style.justifyContent = 'center';
        closeBtn.style.display = 'flex';
        closeBtn.style.visibility = 'hidden';
        closeBtn.style.borderRadius = '100px';
        closeBtn.style.width = '40px';
        closeBtn.style.height = '40px';
        closeBtn.style.top = '16px';
        closeBtn.style.right = '16px';
        closeBtn.style.zIndex = '9999999';
        closeBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 1L1 13M1 1L13 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        overlay.appendChild(closeBtn);

        // Buy Me A Coffee embed iframe (without unsupported 'payment *' policy that triggers browser warnings)
        const iframe = document.createElement('iframe');
        iframe.id = 'bmc-iframe';
        iframe.setAttribute('allow', 'publickey-credentials-get *');
        iframe.title = 'Buy Me a Coffee';
        iframe.style.position = 'fixed';
        iframe.style.margin = '0';
        iframe.style.border = '0';
        if (isDesktop.matches) {
            if (dataPosition.toLowerCase() === 'left') {
                iframe.style.left = dataXMargin + 'px';
            } else {
                iframe.style.right = dataXMargin + 'px';
            }
            iframe.style.bottom = (parseInt(dataYMargin, 10) + 72) + 'px';
            iframe.style.width = '420px';
            iframe.style.maxWidth = '420px';
            iframe.style.minHeight = (window.innerHeight - 120) + 'px';
            iframe.style.maxHeight = (window.innerHeight - 120) + 'px';
        } else {
            iframe.style.left = '0px';
            iframe.style.right = '0px';
            iframe.style.top = '0px';
            iframe.style.bottom = '32px';
            iframe.style.width = '100vw';
            iframe.style.maxWidth = '100vw';
            iframe.style.minHeight = '100%';
            iframe.style.maxHeight = '100%';
        }
        iframe.style.height = '0';
        iframe.style.opacity = '0';
        iframe.style.borderRadius = '10px';
        iframe.style.boxShadow = '-6px 0px 30px rgba(13, 12, 34, 0.1)';
        iframe.style.background = '#fff';
        iframe.style.backgroundImage = 'url(https://cdn.buymeacoffee.com/assets/img/widget/loader.svg)';
        iframe.style.backgroundPosition = 'center';
        iframe.style.backgroundSize = '64px';
        iframe.style.backgroundRepeat = 'no-repeat';
        iframe.style.zIndex = '999999';
        iframe.style.transition = 'all .25s ease';
        iframe.style.transformOrigin = 'right bottom';
        iframe.style.transform = 'scale(0)';
        iframe.style.userSelect = 'none';

        // Optional tooltip element
        let tooltip = null;
        if (dataMessage && dataMessage.trim() !== '') {
            tooltip = document.createElement('div');
            tooltip.style.position = 'fixed';
            if (dataPosition.toLowerCase() === 'left') {
                tooltip.style.left = (parseInt(dataXMargin, 10) + 84) + 'px';
            } else {
                tooltip.style.right = (parseInt(dataXMargin, 10) + 84) + 'px';
            }
            tooltip.style.bottom = (parseInt(dataYMargin, 10) - 2) + 'px';
            tooltip.style.transformOrigin = 'right bottom';
            tooltip.style.transform = 'scale(0.7)';
            tooltip.style.opacity = '0';
            tooltip.style.transition = '.25s ease all';
            tooltip.style.visibility = 'hidden';
            tooltip.style.background = '#ffffff';
            tooltip.style.zIndex = '9999';
            tooltip.innerText = dataMessage;
            tooltip.style.boxShadow = '0px 2px 5px rgba(0, 0, 0, 0.05), 0px 8px 40px rgba(0, 0, 0, 0.04), 0px 0px 2px rgba(0, 0, 0, 0.15)';
            tooltip.style.padding = '16px 16px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '18px';
            tooltip.style.color = '#0D0C22';
            tooltip.style.width = 'auto';
            tooltip.style.maxWidth = '260px';
            tooltip.style.lineHeight = '1.5';
            tooltip.style.fontFamily = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

            setTimeout(() => {
                tooltip.style.opacity = '1';
                tooltip.style.visibility = 'visible';
                tooltip.style.transform = 'scale(1)';
            }, 500);

            setTimeout(() => {
                tooltip.style.opacity = '0';
                tooltip.style.visibility = 'hidden';
                tooltip.style.transform = 'scale(0.7)';
            }, 5000);
        }

        let loaded = false;
        function openIframe() {
            if (!loaded) {
                iframe.src = 'https://www.buymeacoffee.com/widget/page/' + encodeURIComponent(dataId) + '?description=' + encodeURIComponent(dataDescription) + '&color=' + encodeURIComponent(dataColor);
                loaded = true;
            }
            if (tooltip) {
                tooltip.style.opacity = '0';
                tooltip.style.visibility = 'hidden';
                tooltip.style.transform = 'scale(0.7)';
            }
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            if (isDesktop.matches) {
                iframe.style.width = '420px';
                iframe.style.height = (window.innerHeight - 120) + 'px';
            } else {
                iframe.style.width = '100vw';
                iframe.style.height = '100%';
                setTimeout(() => { closeBtn.style.visibility = 'visible'; }, 150);
            }
            iframe.style.transform = 'scale(1)';
            iframe.style.opacity = '1';
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 1L1 13M1 1L13 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }

        function closeIframe() {
            overlay.style.width = '0';
            overlay.style.height = '0';
            iframe.style.height = '0';
            iframe.style.opacity = '0';
            iframe.style.transform = 'scale(0)';
            closeBtn.style.visibility = 'hidden';
            btn.innerHTML = '<img src="https://cdn.buymeacoffee.com/widget/assets/coffee%20cup.svg" alt="Buy Me a Coffee">';
        }

        btn.onclick = function (e) {
            e.stopPropagation();
            if (overlay.style.width === '100%') {
                closeIframe();
            } else {
                openIframe();
            }
        };

        btn.onmouseover = function () {
            btn.style.transform = 'scale(1.1)';
        };

        btn.onmouseleave = function () {
            btn.style.transform = 'scale(1)';
        };

        overlay.onclick = function (e) {
            if (e.target === overlay || e.target === closeBtn || closeBtn.contains(e.target)) {
                closeIframe();
            }
        };

        overlay.appendChild(iframe);
        holder.appendChild(overlay);
        holder.appendChild(btn);
        if (tooltip) {
            holder.appendChild(tooltip);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBmcWidget);
    } else {
        initBmcWidget();
    }
    document.addEventListener('turbo:load', initBmcWidget);
})();
