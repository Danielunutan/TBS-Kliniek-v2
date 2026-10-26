// TBS Kliniek Web Push Client Helper
(function(window) {
    'use strict';

    window.tbsPush = {
        async isSupported() {
            return ('serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window);
        },

        getPermission() {
            if (!('Notification' in window)) return 'unsupported';
            return Notification.permission;
        },

        async getSubscription() {
            if (!await this.isSupported()) return null;
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
            if (!await this.isSupported()) {
                throw new Error('Pushmeldingen worden niet ondersteund door deze browser.');
            }

            const perm = await Notification.requestPermission();
            if (perm !== 'granted') {
                throw new Error('Toestemming voor meldingen werd geweigerd in de browserinstellingen.');
            }

            const reg = await navigator.serviceWorker.ready;

            // Fetch VAPID public key
            const keyRes = await fetch('/push/key', { 
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } 
            });
            const keyData = await keyRes.json();
            if (!keyData.public_key) {
                throw new Error('Kon VAPID sleutel niet ophalen van server.');
            }

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
