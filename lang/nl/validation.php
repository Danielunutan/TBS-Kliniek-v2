<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validatie Berichten (Nederlands)
    |--------------------------------------------------------------------------
    |
    | De onderstaande regels bevatten de standaard foutmeldingen van Laravel
    | voor formulier- en bestandvalidatie.
    |
    */

    'accepted' => ':Attribute moet worden geaccepteerd.',
    'accepted_if' => ':Attribute moet worden geaccepteerd wanneer :other :value is.',
    'active_url' => ':Attribute is geen geldige URL.',
    'after' => ':Attribute moet een datum na :date zijn.',
    'after_or_equal' => ':Attribute moet een datum na of gelijk aan :date zijn.',
    'alpha' => ':Attribute mag alleen letters bevatten.',
    'alpha_dash' => ':Attribute mag alleen letters, cijfers, koppeltekens en underscores bevatten.',
    'alpha_num' => ':Attribute mag alleen letters en cijfers bevatten.',
    'array' => ':Attribute moet een array zijn.',
    'ascii' => ':Attribute mag alleen alfanumerieke tekens en symbolen van één byte bevatten.',
    'before' => ':Attribute moet een datum voor :date zijn.',
    'before_or_equal' => ':Attribute moet een datum voor of gelijk aan :date zijn.',
    'between' => [
        'array' => ':Attribute moet tussen :min en :max items bevatten.',
        'file' => ':Attribute moet tussen :min en :max kilobytes zijn.',
        'numeric' => ':Attribute moet tussen :min en :max zijn.',
        'string' => ':Attribute moet tussen :min en :max tekens zijn.',
    ],
    'boolean' => ':Attribute moet waar of onwaar zijn.',
    'confirmed' => 'De :attribute bevestiging komt niet overeen.',
    'current_password' => 'Het wachtwoord is onjuist.',
    'date' => ':Attribute is geen geldige datum.',
    'date_equals' => ':Attribute moet een datum gelijk aan :date zijn.',
    'date_format' => ':Attribute komt niet overeen met het formaat :format.',
    'decimal' => ':Attribute moet :decimal decimalen bevatten.',
    'declined' => ':Attribute moet worden afgewezen.',
    'different' => ':Attribute en :other moeten verschillend zijn.',
    'digits' => ':Attribute moet :digits cijfers bevatten.',
    'digits_between' => ':Attribute moet tussen :min en :max cijfers bevatten.',
    'dimensions' => ':Attribute heeft ongeldige afmetingen.',
    'distinct' => ':Attribute heeft een dubbele waarde.',
    'email' => ':Attribute moet een geldig e-mailadres zijn.',
    'ends_with' => ':Attribute moet eindigen op één van de volgende waarden: :values.',
    'enum' => 'De geselecteerde :attribute is ongeldig.',
    'exists' => 'De geselecteerde :attribute is ongeldig.',
    'extensions' => ':Attribute moet één van de volgende extensies hebben: :values.',
    'file' => ':Attribute moet een geldig bestand zijn.',
    'filled' => ':Attribute moet een waarde hebben.',
    'gt' => [
        'array' => ':Attribute moet meer dan :value items bevatten.',
        'file' => ':Attribute moet groter zijn dan :value kilobytes.',
        'numeric' => ':Attribute moet groter zijn dan :value.',
        'string' => ':Attribute moet meer dan :value tekens bevatten.',
    ],
    'gte' => [
        'array' => ':Attribute moet :value items of meer bevatten.',
        'file' => ':Attribute moet groter of gelijk zijn aan :value kilobytes.',
        'numeric' => ':Attribute moet groter of gelijk zijn aan :value.',
        'string' => ':Attribute moet minimaal :value tekens bevatten.',
    ],
    'hex_color' => ':Attribute moet een geldige hexadecimale kleurcode zijn.',
    'image' => ':Attribute moet een geldige afbeelding zijn (JPG, PNG, WEBP of GIF).',
    'in' => 'De geselecteerde :attribute is ongeldig.',
    'in_array' => ':Attribute moet bestaan in :other.',
    'integer' => ':Attribute moet een geheel getal zijn.',
    'ip' => ':Attribute moet een geldig IP-adres zijn.',
    'ipv4' => ':Attribute moet een geldig IPv4-adres zijn.',
    'ipv6' => ':Attribute moet een geldig IPv6-adres zijn.',
    'json' => ':Attribute moet een geldige JSON-string zijn.',
    'lowercase' => ':Attribute mag alleen kleine letters bevatten.',
    'lt' => [
        'array' => ':Attribute moet minder dan :value items bevatten.',
        'file' => ':Attribute moet kleiner zijn dan :value kilobytes.',
        'numeric' => ':Attribute moet kleiner zijn dan :value.',
        'string' => ':Attribute moet minder dan :value tekens bevatten.',
    ],
    'lte' => [
        'array' => ':Attribute mag niet meer dan :value items bevatten.',
        'file' => ':Attribute mag maximaal :value kilobytes zijn.',
        'numeric' => ':Attribute moet kleiner of gelijk zijn aan :value.',
        'string' => ':Attribute mag maximaal :value tekens bevatten.',
    ],
    'max' => [
        'array' => ':Attribute mag niet meer dan :max items bevatten.',
        'file' => ':Attribute mag niet groter zijn dan :max kilobytes.',
        'numeric' => ':Attribute mag niet groter zijn dan :max.',
        'string' => ':Attribute mag niet langer zijn dan :max tekens.',
    ],
    'max_digits' => ':Attribute mag niet meer dan :max cijfers bevatten.',
    'mimes' => ':Attribute moet een bestand zijn van het type: :values.',
    'mimetypes' => ':Attribute moet een bestand zijn van het type: :values.',
    'min' => [
        'array' => ':Attribute moet minimaal :min items bevatten.',
        'file' => ':Attribute moet minimaal :min kilobytes zijn.',
        'numeric' => ':Attribute moet minimaal :min zijn.',
        'string' => ':Attribute moet minimaal :min tekens bevatten.',
    ],
    'min_digits' => ':Attribute moet minimaal :min cijfers bevatten.',
    'not_in' => 'De geselecteerde :attribute is ongeldig.',
    'numeric' => ':Attribute moet een getal zijn.',
    'present' => ':Attribute moet aanwezig zijn.',
    'prohibited' => ':Attribute is niet toegestaan.',
    'regex' => 'Het formaat van :attribute is ongeldig.',
    'required' => ':Attribute is verplicht.',
    'same' => ':Attribute en :other moeten overeenkomen.',
    'size' => [
        'array' => ':Attribute moet :size items bevatten.',
        'file' => ':Attribute moet :size kilobytes zijn.',
        'numeric' => ':Attribute moet :size zijn.',
        'string' => ':Attribute moet :size tekens bevatten.',
    ],
    'string' => ':Attribute moet een tekst zijn.',
    'timezone' => ':Attribute moet een geldige tijdzone zijn.',
    'unique' => ':Attribute is al in gebruik.',
    'uploaded' => 'Het uploaden van :attribute is mislukt. Controleer of het bestand niet te groot is en probeer het opnieuw.',
    'url' => ':Attribute moet een geldige URL zijn.',

    /*
    |--------------------------------------------------------------------------
    | Aangepaste Attributen
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'avatar' => 'de bende avatar',
        'banner' => 'de bende banner',
        'name' => 'naam',
        'email' => 'e-mailadres',
        'password' => 'wachtwoord',
    ],

];
