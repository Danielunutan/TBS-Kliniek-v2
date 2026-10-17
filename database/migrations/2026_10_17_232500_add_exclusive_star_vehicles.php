<?php

use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $starVehicles = [
            [
                'name' => 'Gepantserde Brabus G-Klasse (V12)',
                'slug' => 'brabus-g-klasse-v12-armored',
                'type' => 'auto',
                'brand' => 'Brabus',
                'price_cash' => 0,
                'price_stars' => 35,
                'speed_pct' => 88,
                'reliability_pct' => 98,
                'min_level' => 1,
                'description' => 'Kogelwerend en versterkt met titanium beplating. Onverwoestbaar tijdens zware ramkraken en heists.',
                'icon' => 'fa-shield-halved',
                'badge_color' => 'amber',
                'is_available' => true,
            ],
            [
                'name' => "Audi RS6 Avant 'Blacked Out'",
                'slug' => 'audi-rs6-avant-blacked-out',
                'type' => 'auto',
                'brand' => 'Audi',
                'price_cash' => 0,
                'price_stars' => 50,
                'speed_pct' => 95,
                'reliability_pct' => 92,
                'min_level' => 1,
                'description' => 'Twin-turbo V8 monster in matzwart. Schudt moeiteloos elke politie-achtervolging af met 85% heist bonus.',
                'icon' => 'fa-car-side',
                'badge_color' => 'amber',
                'is_available' => true,
            ],
            [
                'name' => 'Yamaha R1 Supersport 1000cc',
                'slug' => 'yamaha-r1-supersport-1000cc',
                'type' => 'motor',
                'brand' => 'Yamaha',
                'price_cash' => 0,
                'price_stars' => 40,
                'speed_pct' => 98,
                'reliability_pct' => 85,
                'min_level' => 1,
                'description' => 'Maximale acceleratie en topsnelheid. Flitst tussen het verkeer door voor vliegensvlugge solo operaties.',
                'icon' => 'fa-motorcycle',
                'badge_color' => 'amber',
                'is_available' => true,
            ],
            [
                'name' => 'Offshore Stealth Speedboot',
                'slug' => 'offshore-stealth-speedboot',
                'type' => 'boot',
                'brand' => 'Cigarette Racing',
                'price_cash' => 0,
                'price_stars' => 65,
                'speed_pct' => 92,
                'reliability_pct' => 90,
                'min_level' => 1,
                'description' => 'Radar-absorberende romp met dubbele Mercury racing motoren. Exclusief en dominant voor haven- en water-heists.',
                'icon' => 'fa-ship',
                'badge_color' => 'amber',
                'is_available' => true,
            ],
        ];

        foreach ($starVehicles as $v) {
            Vehicle::updateOrCreate(['slug' => $v['slug']], $v);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Vehicle::whereIn('slug', [
            'brabus-g-klasse-v12-armored',
            'audi-rs6-avant-blacked-out',
            'yamaha-r1-supersport-1000cc',
            'offshore-stealth-speedboot',
        ])->delete();
    }
};
