<?php

require __DIR__ . '/atomcms/vendor/autoload.php';
$app = require_once __DIR__ . '/atomcms/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Atom\Installation\Models\WebsiteInstallation;
use Atom\Installation\Models\WebsiteSetting;

$settings = [
    'theme' => 'atom',
    'hotel_name' => 'TBS Kliniek',
    'cms_color_mode' => 'dark',
    'disable_registration' => 0,
    'hotel_home_room' => 0,
    'start_motto' => 'TBS Kliniek Blok A - Gedetineerde',
    'start_credits' => 5000,
    'start_duckets' => 2500,
    'start_diamonds' => 250,
    'start_points' => 0,
    'start_look' => 'hr-115-42.hd-190-1.ch-215-62.lg-270-62.sh-290-62',
    'give_hc_on_register' => 1,
    'hc_on_register_duration' => 315569260,
    'referrals_needed' => 5,
    'referral_reward_amount' => 30,
    'referral_reward_currency_type' => 'diamonds',
    'min_staff_rank' => 4,
    'min_rank_to_see_hidden_staff' => 6,
    'min_housekeeping_rank' => 6,
    'max_accounts_per_ip' => 10,
    'site_name' => 'TBS Kliniek',
    'site_logo' => 'images/logo.png',
    'hotel_url' => 'http://127.0.0.1:3000',
    'nitro_path' => 'http://127.0.0.1:3000',
];

foreach ($settings as $k => $v) {
    WebsiteSetting::updateOrCreate(
        ['key' => $k],
        ['value' => (string)$v, 'comment' => 'TBS Kliniek Setting']
    );
}

WebsiteInstallation::first()->update([
    'step' => 4,
    'completed' => 1,
]);

echo "AtomCMS initialized with TBS Kliniek settings successfully!\n";
