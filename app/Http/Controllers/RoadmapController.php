<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class RoadmapController extends Controller
{
    /**
     * Get the defined roadmap features list for player voting.
     * Only unreleased/in-development features are listed here.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getFeaturesList(): array
    {
        return [
            'raid' => [
                'key' => 'raid',
                'title' => 'De Celinspectie & Onverwachte Razzia',
                'subtitle' => 'Verstop je contrabande voor de Dienst Vervoer & Ondersteuning',
                'category' => 'Misdaad & Spanning',
                'phase' => 'Fase 1: Binnenkort',
                'status' => 'In Ontwikkeling',
                'status_badge' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70',
                'icon' => 'fa-shield-halved',
                'icon_style' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-200/80 dark:border-purple-800/70',
                'description' => 'Onverwacht klinkt het afdelingsalarm: de bewaking start een algehele celinspectie! Je hebt een korte timer om je illegale bezittingen (shanks, smartphones, contanten) snel te verstoppen onder het matras, achter de leidingen of door te schuiven naar een celgenoot.',
                'highlights' => [
                    'Adrenalineverhogend countdown-alarm tijdens online spelen',
                    'Dynamische risico-percentages per geheime verstopplek',
                    'Bescherm je zeldzame buit of beland in de isoleercel',
                ],
                'base_votes' => 195,
            ],
            'kitchen' => [
                'key' => 'kitchen',
                'title' => 'De Magnetron-Chef & Kantine Keuken',
                'subtitle' => 'Crafting van legendarische snacks, buffs en gerechten',
                'category' => 'Economie & Crafting',
                'phase' => 'Fase 1: Binnenkort',
                'status' => 'In Ontwikkeling',
                'status_badge' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70',
                'icon' => 'fa-burger',
                'icon_style' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-200/80 dark:border-purple-800/70',
                'description' => 'Voedsel is heilig in de kliniek. Verzamel basisingrediënten uit pakketjes en hossels om in de gezamenlijke keuken unieke snacks te bereiden, zoals Turbo-Koffie (+energie regen), de Pittige Noodle Bom (+kracht op de luchtplaats) en Gouden Frikandellen.',
                'highlights' => [
                    'Meer dan 15 unieke recepten en snack-combinaties',
                    'Tijdelijke boosts op energie, gezondheid en vechtkracht',
                    'Verhandel zelfgemaakte gerechten vrij op de Marktplaats',
                ],
                'base_votes' => 188,
            ],
            'guards' => [
                'key' => 'guards',
                'title' => 'Het Corrupte Bewakers Netwerk (Inside Intel)',
                'subtitle' => 'Smeer zaalwachten en portiers voor geheime privileges',
                'category' => 'Spionage & Corruptie',
                'phase' => 'Fase 2: Gepland',
                'status' => 'Gepland',
                'status_badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
                'icon' => 'fa-user-secret',
                'icon_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/70',
                'description' => 'Niet elke beveiliger is even integer. Bouw banden op met Zaalwacht Henk en Portier Jansen door ze contanten of peuken toe te schuiven. In ruil tippen ze je over rijke slachtoffers, waarschuwen ze voor celcontroles en geven ze je stiekem een lopersleutel!',
                'highlights' => [
                    'Reputatiesysteem per beveiliger en portier',
                    'Tips over patiënten met veel contant geld op zak',
                    'Vervroegde waarschuwingen bij razzia\'s',
                ],
                'base_votes' => 174,
            ],
            'council' => [
                'key' => 'council',
                'title' => 'De Patiëntenraad & Afdelingsvoorzitter',
                'subtitle' => 'Wekelijkse verkiezingen, bende-politiek en kliniekregels',
                'category' => 'Politiek & Bendes',
                'phase' => 'Fase 2: Gepland',
                'status' => 'Gepland',
                'status_badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
                'icon' => 'fa-check-to-slot',
                'icon_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/70',
                'description' => 'Stel je wekelijks verkiesbaar als Voorzitter van de Patiëntenraad! Leden kunnen stemmen uitbrengen of stemmen kopen. De winnende voorzitter mag voor een week lang privileges instellen: zoals kantinekortingen, bende-uurloon boosts of het omkopen van bewakers.',
                'highlights' => [
                    'Wekelijkse verkiezingen met realtime stemmenteller',
                    'Kliniekbrede perks en wetten beïnvloeden',
                    'Intense lobby- en bende-oorlogen',
                ],
                'base_votes' => 162,
            ],
            'gang_territory' => [
                'key' => 'gang_territory',
                'title' => 'Afdelingsovernames & Territorium Oorlogen',
                'subtitle' => 'Verover afdelingen en hef belasting op hossels van rivalen',
                'category' => 'Bendes & Oorlog',
                'phase' => 'Fase 2: Gepland',
                'status' => 'Gepland',
                'status_badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
                'icon' => 'fa-map-location-dot',
                'icon_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/70',
                'description' => 'Strijd als bende om controle over de verschillende paviljoens en gangen (Paviljoen De Berk, Veldzicht, De Rooyse Wissel). De heersende bende int automatisch 5% belasting over alle hossels en diefstallen die in hun territorium plaatsvinden!',
                'highlights' => [
                    'Territorium-kaart van alle kliniekafdelingen',
                    'Dagelijkse afdelingsovername gevechten om 20:00 uur',
                    'Passieve inkomstenstroom rechtstreeks in de bende-kluis',
                ],
                'base_votes' => 155,
            ],
            'poker' => [
                'key' => 'poker',
                'title' => 'TBS VIP Poker Lounge (Multiplayer Texas Hold\'em)',
                'subtitle' => 'Echte realtime pokertafels in de geheime kelder',
                'category' => 'Casino & Multiplayer',
                'phase' => 'Fase 3: Toekomst',
                'status' => 'Concept',
                'status_badge' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/70',
                'icon' => 'fa-dice',
                'icon_style' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-200/80 dark:border-blue-800/70',
                'description' => 'Schuif aan bij de illegale pokertafel achter de stookruimte. Speel live Texas Hold\'em poker tegen medespelers met fiches, contant zakgeld en exclusieve side-bets!',
                'highlights' => [
                    'Realtime live multiplayer pokertafel',
                    'Bluf tegen medespelers en sleep reusachtige pots binnen',
                    'Wekelijkse High-Roller Pokertoernooien met unieke trofeeën',
                ],
                'base_votes' => 142,
            ],
            'laundering' => [
                'key' => 'laundering',
                'title' => 'De Zwarte Markt Witwas-Operatie',
                'subtitle' => 'Zet zwart hosselgeld om in veilig legaal banktegoed via stromannen',
                'category' => 'Financiën & Witwassen',
                'phase' => 'Fase 3: Toekomst',
                'status' => 'Concept',
                'status_badge' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/70',
                'icon' => 'fa-money-bill-transfer',
                'icon_style' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-200/80 dark:border-blue-800/70',
                'description' => 'Grote sommen contant geld op zak trekken zakkenrollers en dieven aan. Via corrupte boekhouders buiten de muren kun je zwart geld veilig witwassen naar je beveiligde bankrekening.',
                'highlights' => [
                    'Dynamische witwaskosten en commissiepercentages',
                    'Beveiligd banktegoed dat beschermd is tegen overvallen',
                    'Exclusieve Witwas-Koning prestiges en titels',
                ],
                'base_votes' => 134,
            ],
        ];
    }

    /**
     * Get the list of completed and released features that are now live in the game.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getCompletedFeatures(): array
    {
        return [
            'armwrestling' => [
                'key' => 'armwrestling',
                'title' => 'De Illegale Armpjedruk Arena',
                'subtitle' => 'Realtime 1-op-1 kracht- en reflexduels aan de zware tafel',
                'category' => 'Geweld & Minigames',
                'icon' => 'fa-hand-back-fist',
                'icon_style' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-200/80 dark:border-purple-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70',
                'route' => 'armwrestling.index',
                'action_label' => 'Naar de Arena',
                'description' => 'Test je spierkracht, uithoudingsvermogen en reflexen aan de zware eikenhouten tafel in het krachthonk. Daag afdelingsbazen of medepatiënten uit voor grof prijzengeld!',
            ],
            'heist' => [
                'key' => 'heist',
                'title' => 'Grote Heists & Overvallen',
                'subtitle' => 'Kraak het Geldtransport, Casino of Juwelier voor miljoenenbuit',
                'category' => 'Misdaad & Actie',
                'icon' => 'fa-masks-theater',
                'icon_style' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border-rose-200/80 dark:border-rose-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/70',
                'route' => 'heists.index',
                'action_label' => 'Naar de Heists',
                'description' => 'Plan gewaagde overvallen, zet overval-maskers op en gebruik snelle vluchtvoertuigen om de politie en wakers af te schudden met gigantische buit.',
            ],
            'depot' => [
                'key' => 'depot',
                'title' => 'Centraal Depot & Smokkel Garage',
                'subtitle' => 'Beheer al je voertuigen, wapens, maskers en eventopslag',
                'category' => 'Inventaris & Voertuigen',
                'icon' => 'fa-warehouse',
                'icon_style' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-200/80 dark:border-blue-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/70',
                'route' => 'depot.index',
                'action_label' => 'Naar het Depot',
                'description' => 'Overzicht van je persoonlijke garage, opbergruimte en varia event-opslag, inclusief level-capaciteiten, VIP-bonussen en snelle verkoopoptie.',
            ],
            'bounty_hunt' => [
                'key' => 'bounty_hunt',
                'title' => 'De Grote Premiejacht (Bounties)',
                'subtitle' => 'Kliniekbrede opsporingsbevelen en premies op rivalen',
                'category' => 'Geweld & Kopgeld',
                'icon' => 'fa-crosshairs',
                'icon_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
                'route' => 'bounties.index',
                'action_label' => 'Naar Premiejacht',
                'description' => 'Zet kopgeld op medepatiënten of ga zelf op jacht naar gezochte doelwitten voor gigantische contanten en respectbeloningen.',
            ],
            'cigarettes' => [
                'key' => 'cigarettes',
                'title' => 'Smokkel & Productiekelder',
                'subtitle' => 'Draai peuken, druk medicatie en verhandel voor cash en respect',
                'category' => 'Zwarte Markt & Handel',
                'icon' => 'fa-smoking',
                'icon_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
                'route' => 'smuggle.index',
                'action_label' => 'Naar Smokkelen',
                'description' => 'Produceer sigaretten, ritalin en telefoons in de kelder en sluit winstgevende deals met kopers voor directe winst en +5% Rank XP.',
            ],
            'treatment' => [
                'key' => 'treatment',
                'title' => 'Het Behandelaarsgesprek & Verlof',
                'subtitle' => 'Dagelijkse psycho-evaluatie & 3 uur Begeleid Verlof',
                'category' => 'Voortgang & RPG',
                'icon' => 'fa-brain',
                'icon_style' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200/80 dark:border-emerald-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/70',
                'route' => 'treatment.index',
                'action_label' => 'Naar de Behandelaar',
                'description' => 'Ga dagelijks op gesprek bij psychiater Dokter Visser of Yvonne. Overtuig de behandelaar om tot 3 uur Begeleid Verlof te verdienen voor supermarkt- en station-buitenmissies!',
            ],
            'radio' => [
                'key' => 'radio',
                'title' => 'TBS FM Piratenradio & Shoutouts',
                'subtitle' => 'Live muziek streamen en openbare bende-shoutouts met vlammetjes',
                'category' => 'Community & Audio',
                'icon' => 'fa-radio',
                'icon_style' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-200/80 dark:border-purple-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70',
                'route' => 'radio.index',
                'action_label' => 'Naar TBS FM',
                'description' => 'Luister naar live muziekstreams terwijl je speelt en stuur openbare shoutouts naar alle ingelogde patiënten in de kliniek.',
            ],
            'droppings' => [
                'key' => 'droppings',
                'title' => 'Smokkel Droppings & Drones',
                'subtitle' => 'Nachtelijke drone-droppings en tennisballen over het prikkeldraad',
                'category' => 'Smokkel & Buit',
                'icon' => 'fa-paper-plane',
                'icon_style' => 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border-cyan-200/80 dark:border-cyan-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border-cyan-200/80 dark:border-cyan-800/70',
                'route' => 'droppings.index',
                'action_label' => 'Naar Droppings',
                'description' => 'Vang smokkelpakketten op die door handlangers via drones en geprepareerde tennisballen over de kliniekmuren worden geslingerd.',
            ],
            'tunnel' => [
                'key' => 'tunnel',
                'title' => 'De Geheime Uitbraaktunnel',
                'subtitle' => 'Graaf je een weg naar de vrijheid met lepels en werkmateriaal',
                'category' => 'Ontsnapping & Actie',
                'icon' => 'fa-trowel-bricks',
                'icon_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
                'route' => 'tunnel.index',
                'action_label' => 'Naar de Tunnel',
                'description' => 'Verzamel werkgereedschap, omkoop bewakers voor bouwmateriaal en graaf stiekem meter voor meter onder de muren door.',
            ],
            'daycare' => [
                'key' => 'daycare',
                'title' => 'Kliniek Dagbesteding & Wasserette',
                'subtitle' => 'Veilig eerlijk wit geld en XP verdienen met 0% isoleercelrisico',
                'category' => 'Werk & Voortgang',
                'icon' => 'fa-person-digging',
                'icon_style' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-200/80 dark:border-blue-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/70',
                'route' => 'daycare.index',
                'action_label' => 'Naar Dagbesteding',
                'description' => 'Meld je bij Werkmeester Henk in de werkplaats of wasserette voor een gegarandeerd eerlijk inkomen zonder risico op straf.',
            ],
            'infirmary' => [
                'key' => 'infirmary',
                'title' => 'Ziekenboeg & Pillen Palmen',
                'subtitle' => 'Gratis 100% HP herstel en stiekem medicijnen ontfutselen',
                'category' => 'Gezondheid & Smokkel',
                'icon' => 'fa-hospital',
                'icon_style' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border-rose-200/80 dark:border-rose-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/70',
                'route' => 'infirmary.index',
                'action_label' => 'Naar de Ziekenboeg',
                'description' => 'Laat je gratis oplappen door Zuster Brenda en probeer stiekem kalmeringstabletten achter te houden voor de zwarte markt.',
            ],
            'vault' => [
                'key' => 'vault',
                'title' => 'Medicijnkluis & 22:00 Avondronde Drops',
                'subtitle' => 'Kraak de kluis met 4-cijferige codes en claim gratis avondbuit',
                'category' => 'Kluizen & Codes',
                'icon' => 'fa-vault',
                'icon_style' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-200/80 dark:border-purple-800/70',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70',
                'route' => 'vault.index',
                'action_label' => 'Naar de Kluis',
                'description' => 'Vind kluiscodes, doe mee aan de dagelijkse 22:00 avondronde voor gratis kluiscodes en sleep gigantische jackpots binnen.',
            ],
        ];
    }

    /**
     * Show the roadmap page.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $features = $this->getFeaturesList();
        $completedFeatures = $this->getCompletedFeatures();

        // Haal stemmen op uit cache / persistent store
        $votes = Cache::get('tbs_roadmap_votes', []);
        $userVotes = Cache::get('tbs_user_votes_'.$character->id, []);

        foreach ($features as $k => &$f) {
            $extraVotes = (int) ($votes[$k] ?? 0);
            $f['votes'] = $f['base_votes'] + $extraVotes;
            $f['has_voted'] = in_array($k, $userVotes, true);
        }
        unset($f);

        // Sorteer features op meeste stemmen
        uasort($features, fn ($a, $b) => $b['votes'] <=> $a['votes']);

        return view('game.roadmap', compact('character', 'features', 'completedFeatures', 'userVotes'));
    }

    /**
     * Cast a vote for a roadmap feature.
     */
    public function vote(Request $request, string $featureKey): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $features = array_merge($this->getFeaturesList(), $this->getCompletedFeatures());
        if (! isset($features[$featureKey])) {
            return back()->with('error', 'Onbekende feature.');
        }

        return Cache::lock('tbs_roadmap_votes_lock', 5)->block(3, function () use ($character, $featureKey, $features): RedirectResponse {
            $userVotes = Cache::get('tbs_user_votes_'.$character->id, []);

            if (in_array($featureKey, $userVotes, true)) {
                // Unvote / stem intrekken
                $userVotes = array_values(array_diff($userVotes, [$featureKey]));
                $votes = Cache::get('tbs_roadmap_votes', []);
                $votes[$featureKey] = max(0, ((int) ($votes[$featureKey] ?? 0)) - 1);
                Cache::forever('tbs_roadmap_votes', $votes);
                Cache::forever('tbs_user_votes_'.$character->id, $userVotes);

                return back()->with('success', 'Je stem voor "'.$features[$featureKey]['title'].'" is ingetrokken.');
            }

            // Vote toevoegen
            $userVotes[] = $featureKey;
            $votes = Cache::get('tbs_roadmap_votes', []);
            $votes[$featureKey] = ((int) ($votes[$featureKey] ?? 0)) + 1;
            Cache::forever('tbs_roadmap_votes', $votes);
            Cache::forever('tbs_user_votes_'.$character->id, $userVotes);

            return back()->with('success', 'Bedankt voor je stem! Je hebt gestemd op "'.$features[$featureKey]['title'].'".');
        });
    }
}
