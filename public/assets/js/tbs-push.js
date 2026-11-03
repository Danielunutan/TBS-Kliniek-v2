// TBS Kliniek Web Push Client Helper
(function(window) {
    'use strict';

    window.tbsPush = {
        isSupported() {
            return ('serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window);
        },

        getPermission() {
            if (!('Notification' in window)) return 'unsupported';
            return Notification.permission;
        },

        async getSubscription() {
            if (!this.isSupported()) return null;
            try {
                const reg = await navigator.serviceWorker.ready;
                return await reg.pushManager.getSubscription();
            } catch (e) {
                return null;
            }
        },

        async isSubscribed() {
            const sub = await this.getSubscription();
            return !!sub;
        },

        async subscribe() {
            if (!this.isSupported()) {
                throw new Error('Pushmeldingen worden niet ondersteund door deze browser.');
            }

            // iOS WebKit requires requestPermission to be called directly in the user click gesture without prior await
            let perm = Notification.permission;
            if (perm !== 'granted') {
                perm = await Notification.requestPermission();
            }
            if (perm !== 'granted') {
                throw new Error('Toestemming voor meldingen werd geweigerd in de browserinstellingen.');
            }

            const isStandalone = Boolean(('standalone' in navigator && navigator.standalone) || window.matchMedia('(display-mode: standalone)').matches);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Fetch VAPID public key
            let keyData = null;
            try {
                const keyRes = await fetch('/push/key', { 
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } 
                });
                keyData = await keyRes.json();
            } catch (e) {
                keyData = null;
            }

            if (!keyData || !keyData.public_key) {
                // If VAPID is not set on the server, claim the WebApp/Push bonus directly with permission/standalone
                const fallbackRes = await fetch('/community/claim/push', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-TBS-Standalone': isStandalone ? '1' : '0'
                    },
                    body: JSON.stringify({
                        is_standalone: isStandalone,
                        notification_granted: true
                    })
                });
                return await fallbackRes.json();
            }

            const reg = await navigator.serviceWorker.ready;
            const convertedVapidKey = this.urlBase64ToUint8Array(keyData.public_key);

            let sub = await reg.pushManager.getSubscription();
            if (!sub) {
                sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });
            }

            const rawKey = sub.getKey ? sub.getKey('p256dh') : null;
            const rawAuth = sub.getKey ? sub.getKey('auth') : null;

            const subData = {
                endpoint: sub.endpoint,
                public_key: rawKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawKey))) : '',
                auth_token: rawAuth ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawAuth))) : '',
                content_encoding: (window.PushManager && PushManager.supportedContentEncodings) ? PushManager.supportedContentEncodings[0] : 'aesgcm'
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const saveRes = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(subData)
            });

            return await saveRes.json();
        },

        async unsubscribe() {
            let endpoint = null;
            try {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                if (sub) {
                    endpoint = sub.endpoint;
                    await sub.unsubscribe();
                }
            } catch (e) {
                console.warn('Local unsubscribe error:', e);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ endpoint: endpoint })
            });

            return await res.json();
        },

        async sendTest() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/push/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            return await res.json();
        },

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    };
})(window);
