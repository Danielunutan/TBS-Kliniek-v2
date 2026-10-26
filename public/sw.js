// TBS Kliniek Service Worker - Web Push Notifications & Background Worker
const SW_VERSION = 'tbs-sw-v1.0.0';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

// 1. Listen for incoming Web Push Notifications from the server
self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        payload = {
            title: 'TBS Kliniek Melding',
            body: event.data.text(),
            data: { url: '/dashboard' }
        };
    }

    const title = payload.title || 'TBS Kliniek Melding';
    const options = {
        body: payload.body || 'Je hebt een nieuwe melding ontvangen in de kliniek.',
        icon: payload.icon || '/assets/icons/icon-192x192.png',
        badge: payload.badge || '/assets/icons/icon-192x192.png',
        vibrate: [150, 80, 150],
        tag: (payload.data && payload.data.tag) ? payload.data.tag : ('tbs-push-' + Date.now()),
        renotify: true,
        data: payload.data || { url: '/dashboard' }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// 2. Handle Notification Click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url) 
        ? event.notification.data.url 
        : '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // If the game is already open in a tab, focus it and navigate
            for (const client of clientList) {
                if ('focus' in client) {
                    client.focus();
                    if ('navigate' in client && targetUrl) {
                        return client.navigate(targetUrl);
                    }
                    return client;
                }
            }
            // Otherwise open a new window
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
