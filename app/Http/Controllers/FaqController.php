<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Complete FAQ knowledge base categories and questions.
     */
    public static function getFaqData(): array
    {
        return [
            'basis' => [
                'title' => 'Basis & Spelregels',
                'icon' => 'fa-book-open',
                'questions' => [
                    [
                        'q' => 'Wat is het doel van TBS Kliniek?',
                        'a' => 'In TBS Kliniek begin je als nieuw binnengebrachte patiënt. Je bouwt respect op door te trainen, misdrijven te plegen, andere patiënten uit te dagen op de luchtplaats en een machtige kliniekgang op te richten. Degene met het hoogste respect domineert de ranglijst.',
                    ],
                    [
                        'q' => 'Hoe werkt Energie en Gezondheid (HP)?',
                        'a' => 'Voor bijna alle acties (vechten, diefstal, hosselen) heb je Energie nodig. Energie herstelt automatisch elke paar minuten. Gezondheid daalt wanneer je een gevecht verliest of betrapt wordt. Je herstelt HP automatisch over tijd of direct via EHBO-artikelen in de KliniekWinkel.',
                    ],
                    [
                        'q' => 'Wat gebeurt er als ik in de Isoleercel beland?',
                        'a' => 'Als de zaalwacht je betrapt bij diefstal of een mislukte kluiskraak, word je opgesloten in de isoleercel voor een straftijd (in seconden). Tijdens je straftijd kun je niet vechten of misdrijven plegen. Je kunt wachten tot de timer afloopt, een andere patiënt vragen je celdeur te forceren, of je direct vrijkopen met Sterren.',
                    ],
                ],
            ],
            'vechten' => [
                'title' => 'Vechten & Luchtplaats',
                'icon' => 'fa-hand-fist',
                'questions' => [
                    [
                        'q' => 'Hoeveel aanvallen mag ik per dag uitvoeren?',
                        'a' => 'Je kunt maximaal 100 gevechten per dag uitvoeren op de luchtplaats. Daarnaast geldt een maximum van 100 aanvallen per dag op hetzelfde specifieke doelwit, om pesterijen en farming tegen te gaan. De teller reset elke nacht om 00:00 uur.',
                    ],
                    [
                        'q' => 'Waarom is er een 5-seconden arena scherm?',
                        'a' => 'Elk gevecht brengt je naar de interactieve Arena Showdown. Gedurende 5 seconden bouwt de spanning zich op terwijl de klappen vallen tussen aanvaller en verdediger, waarna de buit, opgelopen schade en respect direct worden onthuld.',
                    ],
                    [
                        'q' => 'Waarom krijg ik soms een Cloudflare Turnstile controle?',
                        'a' => 'Om macro\'s, autoclickers en bots tegen te gaan, controleert het TBS beveiligingssysteem spelers met Cloudflare Turnstile tijdens actieve gevechten en cooldowns. Bevestig eenvoudig de verificatie om door te spelen.',
                    ],
                ],
            ],
            'vip' => [
                'title' => 'VIP Lidmaatschap & Punten',
                'icon' => 'fa-crown',
                'questions' => [
                    [
                        'q' => 'Hoe word ik VIP en wat kost het?',
                        'a' => 'VIP kost €7,50 per maand. Word lid van onze officiële Discord via discord.gg/tbs en maak een aankoopticket aan. Een medewerker activeert na betaling direct jouw VIP-status in het Medewerkers Paneel.',
                    ],
                    [
                        'q' => 'Hoe verdien ik VIP punten?',
                        'a' => 'Elke VIP-speler ontvangt automatisch 5 VIP Punten per uur dat ze actief meespelen in de kliniek! Deze punten kun je in de VIP Shop inwisselen voor exclusieve titels, dubbele energie boosters, gouden koffers en respectpakketten.',
                    ],
                ],
            ],
            'kluis' => [
                'title' => 'Kluis-Kraak & Codes',
                'icon' => 'fa-vault',
                'questions' => [
                    [
                        'q' => 'Hoe kraak ik de centrale kliniekkluis?',
                        'a' => 'Ga naar de pagina Kluis-Kraak. Je moet de geheime 4-cijferige code raden. Je krijgt bij elke poging aanwijzingen (te hoog of te laag). Als je alle pogingen verbruikt, kan het alarm afgaan tenzij je een Alarm Jammer hebt aangeschaft.',
                    ],
                    [
                        'q' => 'Wat zijn geheime Kluiscodes en vouchers?',
                        'a' => 'Tijdens het openen van Smokkelkoffers of via level-mijlpalen kun je geheime codes vinden. Deze kun je direct invoeren op de Kluis-Kraak pagina voor contant geld, sterren en zeldzame kluismunten.',
                    ],
                ],
            ],
            'gangs' => [
                'title' => 'Gangs & Bendevorming',
                'icon' => 'fa-users',
                'questions' => [
                    [
                        'q' => 'Hoe richt ik een gang op en wat kost het?',
                        'a' => 'Vanaf niveau 3 kun je op de Gangs pagina een eigen bende oprichten. Als baas beheer je de bendeleden, de gezamenlijke kluis, upgrades en bondgenootschappen.',
                    ],
                    [
                        'q' => 'Hoe werkt het gang uurloon?',
                        'a' => 'Leden van een gang kunnen elk uur hun gang-uurloon claimen vanuit de gezamenlijke kluis, afhankelijk van het wage-percentage dat de bendeleider heeft ingesteld in het bendebeheer.',
                    ],
                ],
            ],
            'account' => [
                'title' => 'Account, Beveiliging & Brevo Mail',
                'icon' => 'fa-shield-halved',
                'questions' => [
                    [
                        'q' => 'Waarom moet ik mijn e-mailadres bevestigen?',
                        'a' => 'E-mailverificatie is verplicht voor alle accounts om veilige communicatie, accountterugwinning en eerlijk spel te garanderen. Bevestigingsmails worden veilig verstuurd via onze Brevo SMTP server.',
                    ],
                    [
                        'q' => 'Hoe werkt Wachtwoord Vergeten?',
                        'a' => 'Klik op het inlogscherm of ga naar /wachtwoord-vergeten. Vul je e-mailadres in en je ontvangt direct een veilige reset-link in je inbox om een nieuw wachtwoord in te stellen.',
                    ],
                    [
                        'q' => 'Hoe vaak mag ik mijn gebruikersnaam aanpassen?',
                        'a' => 'Je mag je gebruikersnaam maximaal 1 keer per 6 maanden aanpassen via Account Instellingen (/instellingen/account). Dit voorkomt verwarring en oplichting tussen spelers.',
                    ],
                    [
                        'q' => 'Wat doet "Overal Uitloggen" (Remote Sessions)?',
                        'a' => 'Onder Account Instellingen zie je alle apparaten waar je momenteel bent ingelogd. Vul je wachtwoord in om alle andere apparaten op afstand met 1 klik uit te loggen.',
                    ],
                ],
            ],
        ];
    }

    /**
     * Show FAQ page.
     */
    public function index(Request $request): View
    {
        $categories = self::getFaqData();
        $search = strtolower(trim($request->input('search', '')));

        if (! empty($search)) {
            $filtered = [];
            foreach ($categories as $catKey => $cat) {
                $matchingQuestions = array_filter($cat['questions'], function ($item) use ($search) {
                    return str_contains(strtolower($item['q']), $search) || str_contains(strtolower($item['a']), $search);
                });

                if (! empty($matchingQuestions)) {
                    $catCopy = $cat;
                    $catCopy['questions'] = array_values($matchingQuestions);
                    $filtered[$catKey] = $catCopy;
                }
            }
            $categories = $filtered;
        }

        return view('game.faq', compact('categories', 'search'));
    }
}
