<?php

return [
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:privacy@tbs-kliniek.nl'),
        'public_key' => env('VAPID_PUBLIC_KEY', 'BOjZcNe1NH_c8fhzBfzkDWDY44uRT_QOnEyU6ZCseetA2wl2RMRzyN6AcO44XvjbsEPJDKHmWIuN6xdIAR7D188'),
        'private_key' => env('VAPID_PRIVATE_KEY', 'XvG4WI43tBp-3HIKTZYbC3hTZdlO2_0t0lN7fEhxSDY'),
    ],
    'defaults' => [
        'icon' => '/assets/icons/icon-192x192.png',
        'badge' => '/assets/icons/icon-192x192.png',
        'ttl' => 2419200, // 4 weeks
    ],
];
