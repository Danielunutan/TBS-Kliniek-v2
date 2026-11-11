<?php

require __DIR__ . '/atomcms/vendor/autoload.php';
$app = require_once __DIR__ . '/atomcms/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Atom\Core\Models\User;
use Illuminate\Support\Facades\Hash;

$existing = User::where('username', 'Daniel')->first();
if (!$existing) {
    $user = User::create([
        'username' => 'Daniel',
        'password' => Hash::make('Admin123!'),
        'mail' => 'admin@tbs-kliniek.nl',
        'look' => 'hr-115-42.hd-190-1.ch-215-62.lg-270-62.sh-290-62',
        'rank' => 7,
        'credits' => 50000,
        'pixels' => 10000,
        'points' => 5000,
        'motto' => 'Directeur TBS Kliniek',
        'account_created' => time(),
        'last_login' => time(),
        'last_online' => time(),
        'ip_register' => '127.0.0.1',
        'ip_current' => '127.0.0.1',
        'auth_ticket' => 'tbs_test_token',
    ]);
    echo "User Daniel created successfully with ID: " . $user->id . "\n";
} else {
    $existing->update([
        'password' => Hash::make('Admin123!'),
        'rank' => 7,
        'auth_ticket' => 'tbs_test_token',
    ]);
    echo "User Daniel updated to rank 7 with password Admin123!\n";
}
