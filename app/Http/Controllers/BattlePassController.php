<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BattlePassController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    /**
     * Get the full configuration of the Season 1 Battle Pass (20 Levels, 4 Tracks).
     *
     * @return array<int, array{level: int, req_level: int, req_respect: int, tracks: array<string, array{title: string, icon: string, rewards: array<string, int>, summary: string}>}>
     */
    public static function getPassConfig(): array
    {
        return [
            1 => [
                'level' => 1,
                'req_level' => 1,
                'req_respect' => 0,
                'tracks' => [
                    'free' => ['title' => 'Startbonus Cash & Energie', 'icon' => 'fa-bolt', 'rewards' => ['cash' => 2500, 'energy' => 25], 'summary' => '€2.500 Cash + 25 Energie'],
                    'vip1' => ['title' => 'V.I.P. Welkomstpakket', 'icon' => 'fa-star', 'rewards' => ['cash' => 15000, 'stars' => 10, 'vip_points' => 10], 'summary' => '€15.000 Cash + 10 Sterren + 10 VIP Punten'],
                    'vip2' => ['title' => 'Elite Voorraad Koffer', 'icon' => 'fa-briefcase', 'rewards' => ['cash' => 50000, 'stars' => 25, 'unopened_bags' => 1], 'summary' => '€50.000 Cash + 25 Sterren + 1x Koffer'],
                    'vip3' => ['title' => 'Syndicaat XXL Kist', 'icon' => 'fa-box-open', 'rewards' => ['cash' => 150000, 'stars' => 75, 'xxl_cases' => 1, 'masks' => 2], 'summary' => '€150.000 Cash + 75 Sterren + 1x XXL Kist + 2 Maskers'],
                ],
            ],
            2 => [
                'level' => 2,
                'req_level' => 2,
                'req_respect' => 25,
                'tracks' => [
                    'free' => ['title' => 'Munitie Pakket', 'icon' => 'fa-crosshairs', 'rewards' => ['cash' => 5000, 'bullets' => 25], 'summary' => '€5.000 Cash + 25 Kogels'],
                    'vip1' => ['title' => 'Magazijn Herlading', 'icon' => 'fa-shield-halved', 'rewards' => ['cash' => 20000, 'bullets' => 75, 'stars' => 15], 'summary' => '€20.000 Cash + 75 Kogels + 15 Sterren'],
                    'vip2' => ['title' => 'Elite Wapenarsenaal', 'icon' => 'fa-gun', 'rewards' => ['cash' => 75000, 'bullets' => 200, 'stars' => 35], 'summary' => '€75.000 Cash + 200 Kogels + 35 Sterren'],
                    'vip3' => ['title' => 'Kliniek Zware Munitie', 'icon' => 'fa-explosion', 'rewards' => ['cash' => 250000, 'bullets' => 600, 'stars' => 100, 'masks' => 3], 'summary' => '€250.000 Cash + 600 Kogels + 100 Sterren + 3 Maskers'],
                ],
            ],
            3 => [
                'level' => 3,
                'req_level' => 3,
                'req_respect' => 75,
                'tracks' => [
                    'free' => ['title' => 'Medische Booster', 'icon' => 'fa-heart-pulse', 'rewards' => ['cash' => 7500, 'health' => 50, 'energy' => 50], 'summary' => '€7.500 Cash + 50 HP + 50 Energie'],
                    'vip1' => ['title' => 'VIP Vitamines', 'icon' => 'fa-pills', 'rewards' => ['cash' => 25000, 'energy' => 100, 'vip_points' => 20], 'summary' => '€25.000 Cash + 100 Energie + 20 VIP Punten'],
                    'vip2' => ['title' => 'Adrenaline Injectie', 'icon' => 'fa-syringe', 'rewards' => ['cash' => 100000, 'energy' => 150, 'stars' => 45, 'unopened_bags' => 1], 'summary' => '€100.000 Cash + 150 Energie + 45 Sterren + 1 Koffer'],
                    'vip3' => ['title' => 'Syndicaat Geheime Serum', 'icon' => 'fa-flask-vial', 'rewards' => ['cash' => 350000, 'energy' => 250, 'stars' => 125, 'xxl_cases' => 1], 'summary' => '€350.000 Cash + 250 Energie + 125 Sterren + 1 XXL Kist'],
                ],
            ],
            4 => [
                'level' => 4,
                'req_level' => 4,
                'req_respect' => 150,
                'tracks' => [
                    'free' => ['title' => 'Kluis Code Token', 'icon' => 'fa-key', 'rewards' => ['cash' => 10000, 'vault_tokens' => 1], 'summary' => '€10.000 Cash + 1 Kluis Token'],
                    'vip1' => ['title' => 'Kluis Master Sleutels', 'icon' => 'fa-key', 'rewards' => ['cash' => 30000, 'vault_tokens' => 3, 'stars' => 20], 'summary' => '€30.000 Cash + 3 Kluis Tokens + 20 Sterren'],
                    'vip2' => ['title' => 'Kluis Kraak Gereedschap', 'icon' => 'fa-unlock-keyhole', 'rewards' => ['cash' => 125000, 'vault_tokens' => 6, 'stars' => 55], 'summary' => '€125.000 Cash + 6 Kluis Tokens + 55 Sterren'],
                    'vip3' => ['title' => 'Kliniek Directeur Loper', 'icon' => 'fa-vault', 'rewards' => ['cash' => 450000, 'vault_tokens' => 12, 'stars' => 150, 'masks' => 4], 'summary' => '€450.000 Cash + 12 Kluis Tokens + 150 Sterren + 4 Maskers'],
                ],
            ],
            5 => [
                'level' => 5,
                'req_level' => 5,
                'req_respect' => 250,
                'tracks' => [
                    'free' => ['title' => 'Mijlpaal Koffer', 'icon' => 'fa-briefcase', 'rewards' => ['cash' => 15000, 'respect' => 15, 'unopened_bags' => 1], 'summary' => '€15.000 Cash + 15 Respect + 1 Koffer'],
                    'vip1' => ['title' => 'VIP Dubbele Koffers', 'icon' => 'fa-briefcase', 'rewards' => ['cash' => 50000, 'respect' => 35, 'stars' => 35, 'unopened_bags' => 2], 'summary' => '€50.000 Cash + 35 Respect + 35 Sterren + 2 Koffers'],
                    'vip2' => ['title' => 'Elite Zeldzame XXL Kist', 'icon' => 'fa-box-open', 'rewards' => ['cash' => 200000, 'respect' => 75, 'stars' => 75, 'xxl_cases' => 1], 'summary' => '€200.000 Cash + 75 Respect + 75 Sterren + 1 XXL Kist'],
                    'vip3' => ['title' => 'Syndicaat Dubbele Kist', 'icon' => 'fa-gem', 'rewards' => ['cash' => 750000, 'respect' => 150, 'stars' => 200, 'xxl_cases' => 2, 'masks' => 5], 'summary' => '€750.000 Cash + 150 Respect + 200 Sterren + 2 XXL Kisten + 5 Maskers'],
                ],
            ],
            6 => [
                'level' => 6,
                'req_level' => 6,
                'req_respect' => 400,
                'tracks' => [
                    'free' => ['title' => 'Geldinjectie Paviljoen', 'icon' => 'fa-money-bill-wave', 'rewards' => ['cash' => 20000, 'respect' => 20], 'summary' => '€20.000 Cash + 20 Respect'],
                    'vip1' => ['title' => 'VIP Bankdeposito', 'icon' => 'fa-building-columns', 'rewards' => ['bank' => 65000, 'stars' => 40, 'vip_points' => 25], 'summary' => '€65.000 Bank + 40 Sterren + 25 VIP Punten'],
                    'vip2' => ['title' => 'Elite Off-Shore Saldo', 'icon' => 'fa-wallet', 'rewards' => ['bank' => 250000, 'stars' => 90, 'unopened_bags' => 1], 'summary' => '€250.000 Bank + 90 Sterren + 1 Koffer'],
                    'vip3' => ['title' => 'Zwart Geld Witwasdeal', 'icon' => 'fa-sack-dollar', 'rewards' => ['bank' => 900000, 'stars' => 250, 'masks' => 6], 'summary' => '€900.000 Bank + 250 Sterren + 6 Maskers'],
                ],
            ],
            7 => [
                'level' => 7,
                'req_level' => 7,
                'req_respect' => 600,
                'tracks' => [
                    'free' => ['title' => 'Kogelband Bevoorrading', 'icon' => 'fa-crosshairs', 'rewards' => ['cash' => 25000, 'bullets' => 50], 'summary' => '€25.000 Cash + 50 Kogels'],
                    'vip1' => ['title' => 'VIP Zwaar Magazijn', 'icon' => 'fa-shield-halved', 'rewards' => ['cash' => 80000, 'bullets' => 150, 'stars' => 45], 'summary' => '€80.000 Cash + 150 Kogels + 45 Sterren'],
                    'vip2' => ['title' => 'Elite Kogelkratten', 'icon' => 'fa-gun', 'rewards' => ['cash' => 300000, 'bullets' => 400, 'stars' => 100], 'summary' => '€300.000 Cash + 400 Kogels + 100 Sterren'],
                    'vip3' => ['title' => 'Syndicaat Munitietransport', 'icon' => 'fa-truck-fast', 'rewards' => ['cash' => 1100000, 'bullets' => 1200, 'stars' => 300, 'masks' => 7], 'summary' => '€1.100.000 Cash + 1.200 Kogels + 300 Sterren + 7 Maskers'],
                ],
            ],
            8 => [
                'level' => 8,
                'req_level' => 8,
                'req_respect' => 850,
                'tracks' => [
                    'free' => ['title' => 'Kluis Specialisten Loper', 'icon' => 'fa-key', 'rewards' => ['cash' => 30000, 'vault_tokens' => 2], 'summary' => '€30.000 Cash + 2 Kluis Tokens'],
                    'vip1' => ['title' => 'VIP Kluizenkraker Set', 'icon' => 'fa-lock-open', 'rewards' => ['cash' => 95000, 'vault_tokens' => 5, 'stars' => 50], 'summary' => '€95.000 Cash + 5 Kluis Tokens + 50 Sterren'],
                    'vip2' => ['title' => 'Elite Stethoscoop & Decoder', 'icon' => 'fa-microchip', 'rewards' => ['cash' => 350000, 'vault_tokens' => 10, 'stars' => 120], 'summary' => '€350.000 Cash + 10 Kluis Tokens + 120 Sterren'],
                    'vip3' => ['title' => 'Kliniek Directeur Hoofdcode', 'icon' => 'fa-fingerprint', 'rewards' => ['cash' => 1300000, 'vault_tokens' => 20, 'stars' => 350, 'masks' => 8], 'summary' => '€1.300.000 Cash + 20 Kluis Tokens + 350 Sterren + 8 Maskers'],
                ],
            ],
            9 => [
                'level' => 9,
                'req_level' => 9,
                'req_respect' => 1150,
                'tracks' => [
                    'free' => ['title' => 'Doping & Stamina Boost', 'icon' => 'fa-bolt-lightning', 'rewards' => ['cash' => 35000, 'energy' => 75], 'summary' => '€35.000 Cash + 75 Energie'],
                    'vip1' => ['title' => 'VIP Power Shots', 'icon' => 'fa-fire-flame-curved', 'rewards' => ['cash' => 110000, 'energy' => 150, 'stars' => 60], 'summary' => '€110.000 Cash + 150 Energie + 60 Sterren'],
                    'vip2' => ['title' => 'Elite Geheime Stamina', 'icon' => 'fa-dumbbell', 'rewards' => ['cash' => 400000, 'energy' => 250, 'stars' => 140, 'unopened_bags' => 1], 'summary' => '€400.000 Cash + 250 Energie + 140 Sterren + 1 Koffer'],
                    'vip3' => ['title' => 'Syndicaat Onbeperkte Drive', 'icon' => 'fa-crown', 'rewards' => ['cash' => 1600000, 'energy' => 350, 'stars' => 400, 'xxl_cases' => 1, 'masks' => 9], 'summary' => '€1.600.000 Cash + 350 Energie + 400 Sterren + 1 XXL Kist + 9 Maskers'],
                ],
            ],
            10 => [
                'level' => 10,
                'req_level' => 10,
                'req_respect' => 1500,
                'tracks' => [
                    'free' => ['title' => 'Halverwege Seizoen Koffer', 'icon' => 'fa-trophy', 'rewards' => ['cash' => 50000, 'respect' => 50, 'stars' => 15, 'unopened_bags' => 1], 'summary' => '€50.000 Cash + 50 Respect + 15 Sterren + 1 Koffer'],
                    'vip1' => ['title' => 'VIP Zilveren Kist', 'icon' => 'fa-briefcase', 'rewards' => ['cash' => 150000, 'respect' => 100, 'stars' => 80, 'unopened_bags' => 2], 'summary' => '€150.000 Cash + 100 Respect + 80 Sterren + 2 Koffers'],
                    'vip2' => ['title' => 'Elite Gouden XXL Kist', 'icon' => 'fa-box-open', 'rewards' => ['cash' => 500000, 'respect' => 200, 'stars' => 200, 'xxl_cases' => 2], 'summary' => '€500.000 Cash + 200 Respect + 200 Sterren + 2 XXL Kisten'],
                    'vip3' => ['title' => 'Syndicaat Meesterkist', 'icon' => 'fa-dragon', 'rewards' => ['cash' => 2000000, 'respect' => 400, 'stars' => 500, 'xxl_cases' => 3, 'masks' => 12], 'summary' => '€2.000.000 Cash + 400 Respect + 500 Sterren + 3 XXL Kisten + 12 Maskers'],
                ],
            ],
            11 => [
                'level' => 11,
                'req_level' => 11,
                'req_respect' => 1900,
                'tracks' => [
                    'free' => ['title' => 'Kogelreserve Pakket', 'icon' => 'fa-crosshairs', 'rewards' => ['cash' => 45000, 'bullets' => 75], 'summary' => '€45.000 Cash + 75 Kogels'],
                    'vip1' => ['title' => 'VIP Heavy Rounds', 'icon' => 'fa-shield-halved', 'rewards' => ['cash' => 130000, 'bullets' => 250, 'stars' => 70], 'summary' => '€130.000 Cash + 250 Kogels + 70 Sterren'],
                    'vip2' => ['title' => 'Elite Wapenarsenaal II', 'icon' => 'fa-gun', 'rewards' => ['cash' => 450000, 'bullets' => 600, 'stars' => 160], 'summary' => '€450.000 Cash + 600 Kogels + 160 Sterren'],
                    'vip3' => ['title' => 'Syndicaat Munitieloods', 'icon' => 'fa-explosion', 'rewards' => ['cash' => 2200000, 'bullets' => 1800, 'stars' => 450, 'masks' => 10], 'summary' => '€2.200.000 Cash + 1.800 Kogels + 450 Sterren + 10 Maskers'],
                ],
            ],
            12 => [
                'level' => 12,
                'req_level' => 12,
                'req_respect' => 2350,
                'tracks' => [
                    'free' => ['title' => 'Zorgverzekering Uitkering', 'icon' => 'fa-building-columns', 'rewards' => ['bank' => 50000, 'respect' => 30], 'summary' => '€50.000 Bank + 30 Respect'],
                    'vip1' => ['title' => 'VIP Bank Rendement', 'icon' => 'fa-chart-line', 'rewards' => ['bank' => 150000, 'stars' => 80, 'vip_points' => 35], 'summary' => '€150.000 Bank + 80 Sterren + 35 VIP Punten'],
                    'vip2' => ['title' => 'Elite Zwitserse Kluis', 'icon' => 'fa-vault', 'rewards' => ['bank' => 550000, 'stars' => 180, 'unopened_bags' => 1], 'summary' => '€550.000 Bank + 180 Sterren + 1 Koffer'],
                    'vip3' => ['title' => 'Syndicaat Miljoenen Transfer', 'icon' => 'fa-money-bill-transfer', 'rewards' => ['bank' => 2500000, 'stars' => 500, 'masks' => 12], 'summary' => '€2.500.000 Bank + 500 Sterren + 12 Maskers'],
                ],
            ],
            13 => [
                'level' => 13,
                'req_level' => 13,
                'req_respect' => 2850,
                'tracks' => [
                    'free' => ['title' => 'Kluis Code Breker', 'icon' => 'fa-key', 'rewards' => ['cash' => 55000, 'vault_tokens' => 3], 'summary' => '€55.000 Cash + 3 Kluis Tokens'],
                    'vip1' => ['title' => 'VIP Kluis Decryptor', 'icon' => 'fa-lock-open', 'rewards' => ['cash' => 175000, 'vault_tokens' => 7, 'stars' => 90], 'summary' => '€175.000 Cash + 7 Kluis Tokens + 90 Sterren'],
                    'vip2' => ['title' => 'Elite Kluis Sleutelbos', 'icon' => 'fa-microchip', 'rewards' => ['cash' => 650000, 'vault_tokens' => 15, 'stars' => 200], 'summary' => '€650.000 Cash + 15 Kluis Tokens + 200 Sterren'],
                    'vip3' => ['title' => 'Kliniek Hoofdsleutel Deal', 'icon' => 'fa-crown', 'rewards' => ['cash' => 3000000, 'vault_tokens' => 30, 'stars' => 600, 'masks' => 15], 'summary' => '€3.000.000 Cash + 30 Kluis Tokens + 600 Sterren + 15 Maskers'],
                ],
            ],
            14 => [
                'level' => 14,
                'req_level' => 14,
                'req_respect' => 3400,
                'tracks' => [
                    'free' => ['title' => 'Energie Herlaad Beurt', 'icon' => 'fa-bolt', 'rewards' => ['cash' => 60000, 'energy' => 100], 'summary' => '€60.000 Cash + 100 Energie'],
                    'vip1' => ['title' => 'VIP Turbo Injectie', 'icon' => 'fa-syringe', 'rewards' => ['cash' => 200000, 'energy' => 200, 'stars' => 100], 'summary' => '€200.000 Cash + 200 Energie + 100 Sterren'],
                    'vip2' => ['title' => 'Elite Stamina Formule', 'icon' => 'fa-dumbbell', 'rewards' => ['cash' => 750000, 'energy' => 300, 'stars' => 220, 'unopened_bags' => 1], 'summary' => '€750.000 Cash + 300 Energie + 220 Sterren + 1 Koffer'],
                    'vip3' => ['title' => 'Syndicaat Monster Energy', 'icon' => 'fa-fire', 'rewards' => ['cash' => 3500000, 'energy' => 500, 'stars' => 700, 'xxl_cases' => 1, 'masks' => 15], 'summary' => '€3.500.000 Cash + 500 Energie + 700 Sterren + 1 XXL Kist + 15 Maskers'],
                ],
            ],
            15 => [
                'level' => 15,
                'req_level' => 15,
                'req_respect' => 4000,
                'tracks' => [
                    'free' => ['title' => 'Grote Inmate Koffer', 'icon' => 'fa-briefcase', 'rewards' => ['cash' => 70000, 'respect' => 75, 'stars' => 25, 'unopened_bags' => 1], 'summary' => '€70.000 Cash + 75 Respect + 25 Sterren + 1 Koffer'],
                    'vip1' => ['title' => 'VIP Drie Koffers Pack', 'icon' => 'fa-briefcase', 'rewards' => ['cash' => 225000, 'respect' => 150, 'stars' => 120, 'unopened_bags' => 3], 'summary' => '€225.000 Cash + 150 Respect + 120 Sterren + 3 Koffers'],
                    'vip2' => ['title' => 'Elite Dubbele XXL Kisten', 'icon' => 'fa-box-open', 'rewards' => ['cash' => 850000, 'respect' => 300, 'stars' => 250, 'xxl_cases' => 2], 'summary' => '€850.000 Cash + 300 Respect + 250 Sterren + 2 XXL Kisten'],
                    'vip3' => ['title' => 'Syndicaat Baas Schatkamer', 'icon' => 'fa-gem', 'rewards' => ['cash' => 4000000, 'respect' => 600, 'stars' => 850, 'xxl_cases' => 4, 'masks' => 20], 'summary' => '€4.000.000 Cash + 600 Respect + 850 Sterren + 4 XXL Kisten + 20 Maskers'],
                ],
            ],
            16 => [
                'level' => 16,
                'req_level' => 16,
                'req_respect' => 4700,
                'tracks' => [
                    'free' => ['title' => 'Tactische Kogelkist', 'icon' => 'fa-crosshairs', 'rewards' => ['cash' => 75000, 'bullets' => 100], 'summary' => '€75.000 Cash + 100 Kogels'],
                    'vip1' => ['title' => 'VIP Zwaar Kaliber', 'icon' => 'fa-shield-halved', 'rewards' => ['cash' => 250000, 'bullets' => 350, 'stars' => 130], 'summary' => '€250.000 Cash + 350 Kogels + 130 Sterren'],
                    'vip2' => ['title' => 'Elite Wapenarsenaal III', 'icon' => 'fa-gun', 'rewards' => ['cash' => 950000, 'bullets' => 800, 'stars' => 300], 'summary' => '€950.000 Cash + 800 Kogels + 300 Sterren'],
                    'vip3' => ['title' => 'Syndicaat Artillerie Transport', 'icon' => 'fa-truck-front', 'rewards' => ['cash' => 4500000, 'bullets' => 2500, 'stars' => 1000, 'masks' => 25], 'summary' => '€4.500.000 Cash + 2.500 Kogels + 1.000 Sterren + 25 Maskers'],
                ],
            ],
            17 => [
                'level' => 17,
                'req_level' => 17,
                'req_respect' => 5500,
                'tracks' => [
                    'free' => ['title' => 'Kliniek Premie Uitkering', 'icon' => 'fa-building-columns', 'rewards' => ['bank' => 80000, 'respect' => 50], 'summary' => '€80.000 Bank + 50 Respect'],
                    'vip1' => ['title' => 'VIP Elite Rente', 'icon' => 'fa-chart-line', 'rewards' => ['bank' => 300000, 'stars' => 150, 'vip_points' => 50], 'summary' => '€300.000 Bank + 150 Sterren + 50 VIP Punten'],
                    'vip2' => ['title' => 'Elite Trust Fund', 'icon' => 'fa-vault', 'rewards' => ['bank' => 1100000, 'stars' => 350, 'unopened_bags' => 2], 'summary' => '€1.100.000 Bank + 350 Sterren + 2 Koffers'],
                    'vip3' => ['title' => 'Syndicaat Zwart Geld Imperium', 'icon' => 'fa-sack-dollar', 'rewards' => ['bank' => 5500000, 'stars' => 1200, 'masks' => 30], 'summary' => '€5.500.000 Bank + 1.200 Sterren + 30 Maskers'],
                ],
            ],
            18 => [
                'level' => 18,
                'req_level' => 18,
                'req_respect' => 6400,
                'tracks' => [
                    'free' => ['title' => 'Kluis Code Brekers Bundel', 'icon' => 'fa-key', 'rewards' => ['cash' => 85000, 'vault_tokens' => 5], 'summary' => '€85.000 Cash + 5 Kluis Tokens'],
                    'vip1' => ['title' => 'VIP Master Decoder', 'icon' => 'fa-microchip', 'rewards' => ['cash' => 350000, 'vault_tokens' => 12, 'stars' => 175], 'summary' => '€350.000 Cash + 12 Kluis Tokens + 175 Sterren'],
                    'vip2' => ['title' => 'Elite Hoofdkluis Sleutelbos', 'icon' => 'fa-lock-open', 'rewards' => ['cash' => 1300000, 'vault_tokens' => 25, 'stars' => 400], 'summary' => '€1.300.000 Cash + 25 Kluis Tokens + 400 Sterren'],
                    'vip3' => ['title' => 'Kliniek Baas Kluisautoriteit', 'icon' => 'fa-crown', 'rewards' => ['cash' => 6500000, 'vault_tokens' => 50, 'stars' => 1500, 'masks' => 35], 'summary' => '€6.500.000 Cash + 50 Kluis Tokens + 1.500 Sterren + 35 Maskers'],
                ],
            ],
            19 => [
                'level' => 19,
                'req_level' => 19,
                'req_respect' => 7400,
                'tracks' => [
                    'free' => ['title' => 'Volle Gezondheid & Kracht', 'icon' => 'fa-heart-pulse', 'rewards' => ['cash' => 90000, 'energy' => 150, 'health' => 100], 'summary' => '€90.000 Cash + 150 Energie + 100 HP'],
                    'vip1' => ['title' => 'VIP Maximale Vitaliteit', 'icon' => 'fa-fire-flame-curved', 'rewards' => ['cash' => 400000, 'energy' => 300, 'stars' => 200], 'summary' => '€400.000 Cash + 300 Energie + 200 Sterren'],
                    'vip2' => ['title' => 'Elite Goddelijke Serum', 'icon' => 'fa-dumbbell', 'rewards' => ['cash' => 1500000, 'energy' => 450, 'stars' => 500, 'unopened_bags' => 2], 'summary' => '€1.500.000 Cash + 450 Energie + 500 Sterren + 2 Koffers'],
                    'vip3' => ['title' => 'Syndicaat Onsterfelijkheid Serum', 'icon' => 'fa-gem', 'rewards' => ['cash' => 8000000, 'energy' => 750, 'stars' => 2000, 'xxl_cases' => 2, 'masks' => 40], 'summary' => '€8.000.000 Cash + 750 Energie + 2.000 Sterren + 2 XXL Kisten + 40 Maskers'],
                ],
            ],
            20 => [
                'level' => 20,
                'req_level' => 20,
                'req_respect' => 8500,
                'tracks' => [
                    'free' => ['title' => 'Seizoen 1 Finale Beloning', 'icon' => 'fa-trophy', 'rewards' => ['cash' => 150000, 'stars' => 50, 'unopened_bags' => 2, 'respect' => 100], 'summary' => '€150.000 Cash + 50 Sterren + 2 Koffers + 100 Respect'],
                    'vip1' => ['title' => 'Tier 1 VIP Kampioen', 'icon' => 'fa-crown', 'rewards' => ['cash' => 600000, 'stars' => 300, 'unopened_bags' => 4, 'respect' => 250, 'bullets' => 600], 'summary' => '€600.000 Cash + 300 Sterren + 4 Koffers + 250 Respect + 600 Kogels'],
                    'vip2' => ['title' => 'Tier 2 Gangster Elite Keizer', 'icon' => 'fa-gem', 'rewards' => ['cash' => 2500000, 'stars' => 1000, 'xxl_cases' => 4, 'respect' => 500, 'bullets' => 2000, 'masks' => 25], 'summary' => '€2.500.000 Cash + 1.000 Sterren + 4 XXL Kisten + 500 Respect + 2.000 Kogels + 25 Maskers'],
                    'vip3' => ['title' => 'Tier 3 Syndicaat Kliniek Baas Trofee', 'icon' => 'fa-dragon', 'rewards' => ['cash' => 12500000, 'stars' => 3500, 'xxl_cases' => 10, 'respect' => 1500, 'bullets' => 6000, 'masks' => 75], 'summary' => '€12.500.000 Cash + 3.500 Sterren + 10 XXL Kisten + 1.500 Respect + 6.000 Kogels + 75 Maskers'],
                ],
            ],
        ];
    }

    /**
     * Display the Battle Pass page with all 4 tracks.
     */
    public function index(): View
    {
        /** @var Character $character */
        $character = Auth::user()->character;
        $this->energyService->refresh($character);

        $config = self::getPassConfig();
        $claims = $character->perks['battle_pass_claims'] ?? [];
        $supporterTier = $character->getSupporterTier();
        $isVip = $character->isVip();

        // Calculate how many claimable rewards are waiting
        $claimableCount = 0;
        foreach ($config as $lvlNum => $data) {
            $isUnlocked = $character->level >= $data['req_level'];
            if (! $isUnlocked) {
                continue;
            }

            // Free track
            if (! in_array("lvl_{$lvlNum}_free", $claims, true)) {
                $claimableCount++;
            }
            // VIP 1
            if ($isVip && $supporterTier >= 1 && ! in_array("lvl_{$lvlNum}_vip1", $claims, true)) {
                $claimableCount++;
            }
            // VIP 2
            if ($isVip && $supporterTier >= 2 && ! in_array("lvl_{$lvlNum}_vip2", $claims, true)) {
                $claimableCount++;
            }
            // VIP 3
            if ($isVip && $supporterTier >= 3 && ! in_array("lvl_{$lvlNum}_vip3", $claims, true)) {
                $claimableCount++;
            }
        }

        return view('game.battlepass.index', compact('character', 'config', 'claims', 'supporterTier', 'isVip', 'claimableCount'));
    }

    /**
     * Claim an individual Battle Pass reward.
     */
    public function claim(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'level' => 'required|integer|between:1,20',
            'track' => 'required|string|in:free,vip1,vip2,vip3',
        ]);

        /** @var Character $character */
        $character = Auth::user()->character;
        $lvl = (int) $validated['level'];
        $track = (string) $validated['track'];

        $config = self::getPassConfig();
        if (! isset($config[$lvl]['tracks'][$track])) {
            return back()->with('error', 'Ongeldig Battle Pass niveau of spoor.');
        }

        $levelData = $config[$lvl];
        $trackData = $levelData['tracks'][$track];

        // Check level requirement
        if ($character->level < $levelData['req_level']) {
            return back()->with('error', "Je moet minimaal Level {$levelData['req_level']} hebben bereikt om dit niveau te ontgrendelen.");
        }

        // Check VIP tier requirement
        $supporterTier = $character->getSupporterTier();
        $isVip = $character->isVip();

        if ($track === 'vip1' && (! $isVip || $supporterTier < 1)) {
            return back()->with('error', 'Voor dit spoor heb je minimaal VIP Tier 1 (TBS V.I.P. Lid) status nodig.');
        }

        if ($track === 'vip2' && (! $isVip || $supporterTier < 2)) {
            return back()->with('error', 'Voor dit spoor heb je minimaal VIP Tier 2 (Gangster Elite) status nodig.');
        }

        if ($track === 'vip3' && (! $isVip || $supporterTier < 3)) {
            return back()->with('error', 'Voor dit spoor heb je minimaal VIP Tier 3 (Kliniek Baas) status nodig.');
        }

        $perks = $character->perks ?? [];
        $claims = $perks['battle_pass_claims'] ?? [];
        $claimKey = "lvl_{$lvl}_{$track}";

        if (in_array($claimKey, $claims, true)) {
            return back()->with('info', 'Je hebt deze beloning al geclaimd.');
        }

        // Grant rewards
        $this->applyRewards($character, $trackData['rewards']);

        $claims[] = $claimKey;
        $perks['battle_pass_claims'] = $claims;
        $character->perks = $perks;
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'battle_pass_claimed',
            'icon' => $trackData['icon'],
            'title' => "Battle Pass Level {$lvl} Geclaimd!",
            'message' => "Je hebt {$trackData['summary']} ontvangen op je account!",
            'is_read' => false,
        ]);

        return back()->with('success', "🎉 Battle Pass Level {$lvl} beloning ({$trackData['title']}) succesvol geclaimd: {$trackData['summary']}!");
    }

    /**
     * Claim all available unlocked rewards in one click.
     */
    public function claimAll(Request $request): RedirectResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;
        $config = self::getPassConfig();

        $perks = $character->perks ?? [];
        $claims = $perks['battle_pass_claims'] ?? [];
        $supporterTier = $character->getSupporterTier();
        $isVip = $character->isVip();

        $claimedCount = 0;
        $totalRewards = [];

        foreach ($config as $lvlNum => $data) {
            if ($character->level < $data['req_level']) {
                continue;
            }

            $tracksToCheck = ['free'];
            if ($isVip && $supporterTier >= 1) {
                $tracksToCheck[] = 'vip1';
            }
            if ($isVip && $supporterTier >= 2) {
                $tracksToCheck[] = 'vip2';
            }
            if ($isVip && $supporterTier >= 3) {
                $tracksToCheck[] = 'vip3';
            }

            foreach ($tracksToCheck as $track) {
                $claimKey = "lvl_{$lvlNum}_{$track}";
                if (! in_array($claimKey, $claims, true)) {
                    $rewards = $data['tracks'][$track]['rewards'];
                    $this->applyRewards($character, $rewards);

                    foreach ($rewards as $key => $amount) {
                        $totalRewards[$key] = ($totalRewards[$key] ?? 0) + $amount;
                    }

                    $claims[] = $claimKey;
                    $claimedCount++;
                }
            }
        }

        if ($claimedCount === 0) {
            return back()->with('info', 'Er zijn op dit moment geen ongeclaimde beloningen die voor jou ontgrendeld zijn.');
        }

        $perks['battle_pass_claims'] = $claims;
        $character->perks = $perks;
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'battle_pass_claimed',
            'icon' => 'fa-trophy',
            'title' => "{$claimedCount} Battle Pass Beloningen Geclaimd!",
            'message' => 'Alle ontgrendelde Battle Pass niveaus zijn direct bijgeschreven op je profiel!',
            'is_read' => false,
        ]);

        return back()->with('success', "🎉 Gefeliciteerd! Je hebt in één klik {$claimedCount} Battle Pass beloningen geclaimd en ontvangen!");
    }

    /**
     * Helper to apply rewards array to character.
     *
     * @param  array<string, int>  $rewards
     */
    protected function applyRewards(Character $character, array $rewards): void
    {
        if (isset($rewards['cash'])) {
            $character->cash = ($character->cash ?? 0) + $rewards['cash'];
        }
        if (isset($rewards['bank'])) {
            $character->bank = ($character->bank ?? 0) + $rewards['bank'];
        }
        if (isset($rewards['stars'])) {
            $character->stars = ($character->stars ?? 0) + $rewards['stars'];
        }
        if (isset($rewards['vip_points'])) {
            $character->vip_points = ($character->vip_points ?? 0) + $rewards['vip_points'];
        }
        if (isset($rewards['respect'])) {
            $character->respect = ($character->respect ?? 0) + $rewards['respect'];
        }
        if (isset($rewards['bullets'])) {
            $character->bullets = ($character->bullets ?? 0) + $rewards['bullets'];
        }
        if (isset($rewards['unopened_bags'])) {
            $character->unopened_bags = ($character->unopened_bags ?? 0) + $rewards['unopened_bags'];
        }
        if (isset($rewards['xxl_cases'])) {
            $character->xxl_cases = ($character->xxl_cases ?? 0) + $rewards['xxl_cases'];
        }
        if (isset($rewards['masks'])) {
            $character->masks = ($character->masks ?? 0) + $rewards['masks'];
        }
        if (isset($rewards['vault_tokens'])) {
            $character->vault_tokens = ($character->vault_tokens ?? 0) + $rewards['vault_tokens'];
        }
        if (isset($rewards['energy'])) {
            $character->energy = min($character->max_energy, ($character->energy ?? 0) + $rewards['energy']);
        }
        if (isset($rewards['health'])) {
            $character->health = min($character->max_health, ($character->health ?? 0) + $rewards['health']);
        }
    }
}
