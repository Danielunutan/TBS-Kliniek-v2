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
     *
     * @return array<string, array<string, mixed>>
     */
    public function getFeaturesList(): array
    {
        return [
            'heist' => [
                'key' => 'heist',
                'title' => 'De Grote Medicijnkluis Overval (Gang Heist)',
                'subtitle' => 'Coöperatieve bendeoverval op de centrale medicijnopslag',
                'category' => 'Bendes & Co-op',
                'phase' => 'Fase 1: Binnenkort',
                'status' => 'In Ontwikkeling',
                'status_badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                'icon' => 'fa-vault',
                'icon_style' => 'bg-purple-50 text-purple-600 border-purple-200',
                'description' => 'Samen met jouw bende organiseer je een gecoördineerde kraak op de zwaarbeveiligde apotheek van de kliniek. Bendeleden verdelen rollen (Hacker, Afleiding, Kraker) om sensoren te saboteren en de gigantische medicijnkluis te plunderen voor wit geld en unieke buit!',
                'highlights' => [
                    'Multiplayer gang samenwerking met specifieke rollen',
                    'Realtime minigames (sensoren hacken, zaalwacht afleiden)',
                    'Miljoenenopbrengst direct in de bende-kluis',
                ],
                'base_votes' => 184,
            ],
            'armwrestling' => [
                'key' => 'armwrestling',
                'title' => 'De Illegale Armpjedruk Arena (Kracht Showdown)',
                'subtitle' => 'Realtime 1-op-1 kracht- en reflexduels in het krachthonk',
                'category' => 'Geweld & Minigames',
                'phase' => 'Fase 1: Binnenkort',
                'status' => 'In Ontwikkeling',
                'status_badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                'icon' => 'fa-hand-back-fist',
                'icon_style' => 'bg-rose-50 text-rose-600 border-rose-200',
                'description' => 'Daag medepatiënten uit voor een brute krachtmeting aan de zware eikenhouten tafel in het krachthonk. Een interactieve reflex/timing minigame waar je getrainde spierkracht en uithoudingsvermogen de doorslag geven. Zet contanten, respect of frikandelbroodjes in!',
                'highlights' => [
                    '1-op-1 PvP weddenschappen en rechtstreekse uitdagingen',
                    'Reflex & stamina timing balk minigame',
                    'Wekelijkse Krachthonk Kampioensgordel & prestige',
                ],
                'base_votes' => 172,
            ],
            'bounty_hunt' => [
                'key' => 'bounty_hunt',
                'title' => 'De Grote Premiejacht & Opsporingsbevelen',
                'subtitle' => 'Uitgebreide opsporingsbrieven en kliniekbrede premies',
                'category' => 'Geweld & Kopgeld',
                'phase' => 'Fase 2: Gepland',
                'status' => 'Gepland',
                'status_badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon' => 'fa-crosshairs',
                'icon_style' => 'bg-amber-50 text-amber-600 border-amber-200',
                'description' => 'Plaats gerichte opsporingscontracten op rivaliserende bendes of ontsnapte gevangenen. Gezochte patiënten krijgen een waarschuwingsstatus op de afdelingen en jagers verdienen exclusieve premiejagers-titels en bonuscontanten bij uitschakeling.',
                'highlights' => [
                    'Interactief opsporingsbord in de kantine',
                    'Automatische bonusbeloningen bij knock-outs',
                    'Exclusieve Premiejager-prestaties en badges',
                ],
                'base_votes' => 158,
            ],
            'cigarettes' => [
                'key' => 'cigarettes',
                'title' => 'De Grijze Tabaksmarkt & Peuken-Economie',
                'subtitle' => 'Sigaretten als onofficiële tweede valuta van de kliniek',
                'category' => 'Zwarte Markt & Handel',
                'phase' => 'Fase 2: Gepland',
                'status' => 'Gepland',
                'status_badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon' => 'fa-smoking',
                'icon_style' => 'bg-amber-50 text-amber-600 border-amber-200',
                'description' => 'In de kliniek zijn peuken en sloffen tabak meer waard dan euro\'s. Spaar en ruil sigaretten bij de louche Peukendealer voor zeldzame privileges, afkopen van zaalwachten en exclusieve wapens die je met normaal bankgeld niet kunt kopen.',
                'highlights' => [
                    'Onofficiële in-game ruilvaluta (peuken & sloffen)',
                    'Wisselkoers schommelt op basis van smokkelroutes',
                    'Koop exclusieve smokkelwaar en guard-bribes',
                ],
                'base_votes' => 146,
            ],
            'raid' => [
                'key' => 'raid',
                'title' => 'De Celinspectie & Onverwachte Razzia',
                'subtitle' => 'Verstop je contrabande voor de Dienst Vervoer & Ondersteuning',
                'category' => 'Misdaad & Spanning',
                'phase' => 'Fase 2: Gepland',
                'status' => 'Gepland',
                'status_badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon' => 'fa-shield-halved',
                'icon_style' => 'bg-amber-50 text-amber-600 border-amber-200',
                'description' => 'Onverwacht klinkt het afdelingsalarm: de bewaking start een algehele celinspectie! Je hebt een korte timer om je illegale bezittingen (shanks, smartphones, contanten) snel te verstoppen onder het matras, achter de leidingen of door te schuiven naar een celgenoot.',
                'highlights' => [
                    'Adrenalineverhogend countdown-alarm',
                    'Dynamische risico-percentages per verstopplek',
                    'Bescherm je zeldzame buit of beland in de isoleercel',
                ],
                'base_votes' => 139,
            ],
            'kitchen' => [
                'key' => 'kitchen',
                'title' => 'De Magnetron-Chef & Kantine Keuken',
                'subtitle' => 'Crafting van legendarische snacks, buffs en gerechten',
                'category' => 'Economie & Crafting',
                'phase' => 'Fase 2: Gepland',
                'status' => 'Gepland',
                'status_badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icon' => 'fa-burger',
                'icon_style' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                'description' => 'Voedsel is heilig in de kliniek. Verzamel basisingrediënten uit pakketjes en hossels om in de gezamenlijke keuken unieke snacks te bereiden, zoals Turbo-Koffie (+energie regen), de Pittige Noodle Bom (+kracht op de luchtplaats) en Gouden Frikandellen.',
                'highlights' => [
                    'Meer dan 15 unieke recepten en snack-combinaties',
                    'Tijdelijke boosts op energie, gezondheid en vechtkracht',
                    'Verhandel zelfgemaakte gerechten vrij op de Marktplaats',
                ],
                'base_votes' => 128,
            ],
            'guards' => [
                'key' => 'guards',
                'title' => 'Het Corrupte Bewakers Netwerk (Inside Intel)',
                'subtitle' => 'Smeer zaalwachten en portiers voor geheime privileges',
                'category' => 'Spionage & Corruptie',
                'phase' => 'Fase 3: Toekomst',
                'status' => 'Concept',
                'status_badge' => 'bg-slate-100 text-slate-700 border-slate-200',
                'icon' => 'fa-user-secret',
                'icon_style' => 'bg-slate-50 text-slate-700 border-slate-200',
                'description' => 'Niet elke beveiliger is even integer. Bouw banden op met Zaalwacht Henk en Portier Jansen door ze contanten of peuken toe te schuiven. In ruil tippen ze je over rijke slachtoffers, waarschuwen ze voor celcontroles en geven ze je stiekem een lopersleutel!',
                'highlights' => [
                    'Reputatiesysteem per beveiliger en portier',
                    'Tips over patiënten met veel zakgeld op zak',
                    'Vervroegde waarschuwingen bij razzia\'s',
                ],
                'base_votes' => 121,
            ],
            'council' => [
                'key' => 'council',
                'title' => 'De Patiëntenraad & Afdelingsvoorzitter',
                'subtitle' => 'Wekelijkse verkiezingen, bende-politiek en kliniekregels',
                'category' => 'Politiek & Gangs',
                'phase' => 'Fase 3: Toekomst',
                'status' => 'Concept',
                'status_badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                'icon' => 'fa-check-to-slot',
                'icon_style' => 'bg-blue-50 text-blue-600 border-blue-200',
                'description' => 'Stel je wekelijks verkiesbaar als Voorzitter van de Patiëntenraad! Leden kunnen stemmen uitbrengen of stemmen kopen. De winnende voorzitter mag voor een week lang privileges instellen: zoals kantinekortingen, bende-uurloon boosts of het omkopen van bewakers.',
                'highlights' => [
                    'Wekelijkse verkiezingen met realtime stemmenteller',
                    'Kliniekbrede perks en wetten beïnvloeden',
                    'Intense lobby- en bende-oorlogen',
                ],
                'base_votes' => 112,
            ],
            'gang_territory' => [
                'key' => 'gang_territory',
                'title' => 'Afdelingsovernames & Territorium Oorlogen',
                'subtitle' => 'Verover afdelingen en hef belasting op hossels van rivalen',
                'category' => 'Bendes & Oorlog',
                'phase' => 'Fase 3: Toekomst',
                'status' => 'Concept',
                'status_badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                'icon' => 'fa-map-location-dot',
                'icon_style' => 'bg-purple-50 text-purple-600 border-purple-200',
                'description' => 'Strijd als bende om controle over de verschillende paviljoens en gangen (Paviljoen De Berk, Veldzicht, De Rooyse Wissel). De heersende bende int automatisch 5% belasting over alle hossels en diefstallen die in hun territorium plaatsvinden!',
                'highlights' => [
                    'Territorium-kaart van alle kliniekafdelingen',
                    'Dagelijkse afdelingsovername gevechten om 20:00 uur',
                    'Passieve inkomstenstroom rechtstreeks in de bende-kluis',
                ],
                'base_votes' => 105,
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
            'treatment' => [
                'key' => 'treatment',
                'title' => 'Het Behandelaarsgesprek & Verlof',
                'subtitle' => 'Dagelijkse psycho-evaluatie & 3 uur Begeleid Verlof',
                'category' => 'Voortgang & RPG',
                'icon' => 'fa-brain',
                'icon_style' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
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
                'icon_style' => 'bg-purple-50 text-purple-600 border-purple-200',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-purple-50 text-purple-700 border-purple-200',
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
                'icon_style' => 'bg-cyan-50 text-cyan-600 border-cyan-200',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
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
                'icon_style' => 'bg-amber-50 text-amber-600 border-amber-200',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-amber-50 text-amber-700 border-amber-200',
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
                'icon_style' => 'bg-blue-50 text-blue-600 border-blue-200',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-blue-50 text-blue-700 border-blue-200',
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
                'icon_style' => 'bg-rose-50 text-rose-600 border-rose-200',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-rose-50 text-rose-700 border-rose-200',
                'route' => 'infirmary.index',
                'action_label' => 'Naar de Ziekenboeg',
                'description' => 'Laat je gratis oplappen door Zuster Brenda en probeer stiekem kalmeringstabletten achter te houden voor de zwarte markt.',
            ],
            'newspaper' => [
                'key' => 'newspaper',
                'title' => 'Het Afdelingsblad & Incidenten',
                'subtitle' => 'Live kliniekkrant met breaking news over bendeoorlogen en invallen',
                'category' => 'Krant & Nieuws',
                'icon' => 'fa-newspaper',
                'icon_style' => 'bg-slate-50 text-slate-700 border-slate-200',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-slate-100 text-slate-700 border-slate-200',
                'route' => 'newspaper.index',
                'action_label' => 'Lees de Krant',
                'description' => 'Blijf op de hoogte van alle gevechten, overvallen, kluiskraken en gang-oorlogen die zojuist in de kliniek hebben plaatsgevonden.',
            ],
            'vault' => [
                'key' => 'vault',
                'title' => 'Medicijnkluis & 22:00 Avondronde Drops',
                'subtitle' => 'Kraak de kluis met 4-cijferige codes en claim gratis avondbuit',
                'category' => 'Kluizen & Codes',
                'icon' => 'fa-vault',
                'icon_style' => 'bg-purple-50 text-purple-600 border-purple-200',
                'badge' => 'Nu Live in de Game',
                'badge_style' => 'bg-purple-50 text-purple-700 border-purple-200',
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
    }
}
