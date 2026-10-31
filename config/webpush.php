<?php

return [
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:privacy@tbs-kliniek.nl'),
        'public_key' => env('VAPID_PUBLIC_KEY', null),
        'private_key' => env('VAPID_PRIVATE_KEY', null),
    ],
    'defaults' => [
        'icon' => '/assets/icons/icon-192x192.png',
        'badge' => '/assets/icons/icon-192x192.png',
        'ttl' => 2419200, // 4 weeks
    ],
];
