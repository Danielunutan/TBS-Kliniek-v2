<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('tbs:publish-update-news')]
#[Description('Publiceert het officiële update nieuwsbericht via Daniel account met credits voor Rainbow')]
class PublishUpdateNews extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Zoek Daniel zijn account
        $danielCharacter = Character::where('name', 'Daniel')
            ->orWhere('username', 'daniel')
            ->orWhere('name', 'daniel')
            ->first();

        if (! $danielCharacter) {
            $danielUser = User::where('name', 'like', '%Daniel%')
                ->orWhere('email', 'like', '%daniel%')
                ->first();

            if ($danielUser && $danielUser->character) {
                $danielCharacter = $danielUser->character;
            }
        }

        // Fallback als Daniel nog geen specifiek personage heeft: kies de hoogste staff of eerste speler
        if (! $danielCharacter) {
            $danielCharacter = Character::first();
        }

        $authorId = $danielCharacter?->id;
        $this->info("Auteur geselecteerd: ID {$authorId} ({$danielCharacter?->name})");

        $title = 'Update: Roterende Garage en Grote Bende Heists';
        $slug = Str::slug($title);

        $content = 'Beste patiënten en bendeleden,

Vandaag introduceren we een grote update voor de Smokkel Garage en het Heist Planningsbord. Met deze vernieuwingen krijgt de onderwereld van de kliniek meer diepgang, dynamiek en samenwerking.

[credit=Rainbow]Dank aan speler Rainbow voor het waardevolle idee om de Smokkel Garage te voorzien van een roterend assortiment met wisselende condities en snelheden.[/credit]

## 1. De Roterende Smokkelmarkt (Elk Half Uur Nieuwe Lading)
De Smokkel Garage beschikt vanaf nu over een dynamisch roulatiesysteem. Elk half uur om het hele en halve uur arriveert er een nieuwe vracht van 8 unieke voertuigen.

Belangrijke eigenschappen van de roulatie:
- Wisselende Conditie: Voertuigen worden aangeboden in verschillende staten van onderhoud, variërend van afgetrapte projectwagens tot topstaat bolides.
- Realistische Prijzen: Een voertuig met lagere conditie kent een flinke korting. Dit biedt spelers de kans om voor een scherpe prijs een projectwagen op de kop te tikken en deze zelf in de garage te reviseren.
- Getunede Snelheden: Sommige leveringen bevatten getunede motoren met extra vermogen en hogere topsnelheden. Deze getunede wagens behouden hun snelheidsvoordeel permanent in je eigen wagenpark.
- Uitgebreid Aanbod: Het wagenpark is uitgebreid naar 28 verschillende modellen, van snelle scooters en quads tot kogelwerende sedans, speedboten en vluchthelikopters.

## 2. Grote Bende Heists
Naast solo overvallen is het heists planningsbord nu uitgebreid met officiële Bende Heists. Deze grootschalige operaties vereisen een actieve bende en meerdere gangleden.

Kenmerken van Bende Heists:
- Buitverdeling 50/50: Bij een geslaagde overval ontvang je 50% van de buit direct als contant geld. De overige 50% wordt automatisch gestort in de kluis van jouw bende.
- Bende Respect: Jouw gang verdient aanzienlijk extra Bende Respect bij elke voltooide kraak.
- Co-Op Bonus: Hoe meer leden jouw bende telt, hoe hoger de gezamenlijke slagingskans tijdens de overval.

Bezoek de Smokkel Garage en het Heist Planningsbord om het actuele aanbod en de nieuwe bende-doelwitten te bekijken.';

        $article = NewsArticle::updateOrCreate(
            ['slug' => $slug],
            [
                'character_id' => $authorId,
                'title' => $title,
                'emoji' => '🏎️',
                'image_url' => null,
                'content' => $content,
                'is_pinned' => true,
                'comments_enabled' => true,
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        $this->info("Nieuwsbericht succesvol geplaatst! ID: {$article->id}, Slug: {$article->slug}");

        return self::SUCCESS;
    }
}
