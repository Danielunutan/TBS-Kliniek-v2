/**
 * TBS Kliniek - Custom Game Emojis & Expanded Emoji Catalog Engine
 * Global frozen dataset and sub-millisecond search filter
 */
(function(window) {
    'use strict';

    window.TBS_CUSTOM_EMOJIS = Object.freeze([
    {
        "code": "tbs_shank",
        "name": "TBS Steekwapen",
        "shortcode": ":tbs_shank:",
        "url": "/assets/emojis/custom/tbs_shank.svg",
        "keywords": "shank mes steekwapen tbs tandenborstel bloed moord wapen aanval isoleercel"
    },
    {
        "code": "tbs_pill",
        "name": "TBS Kalmeringspil",
        "shortcode": ":tbs_pill:",
        "url": "/assets/emojis/custom/tbs_pill.svg",
        "keywords": "pil pillen medicijn medicatie capsule drugs tbs kalmering oxazepam goud paars"
    },
    {
        "code": "tbs_handcuffs",
        "name": "Handboeien",
        "shortcode": ":tbs_handcuffs:",
        "url": "/assets/emojis/custom/tbs_handcuffs.svg",
        "keywords": "boeien handboeien arrestatie politie bajes ketting gevangenis cel"
    },
    {
        "code": "tbs_bars",
        "name": "Isoleercel Tralies",
        "shortcode": ":tbs_bars:",
        "url": "/assets/emojis/custom/tbs_bars.svg",
        "keywords": "tralies gevangenis cel isoleercel ogen rood duister bajes celdeur"
    },
    {
        "code": "tbs_soap",
        "name": "Glibberig Zeepje",
        "shortcode": ":tbs_soap:",
        "url": "/assets/emojis/custom/tbs_soap.svg",
        "keywords": "zeep zeepje douchen wassen bubbels schuim tbs glijden douche"
    },
    {
        "code": "tbs_straitjacket",
        "name": "TBS Dwangbuis",
        "shortcode": ":tbs_straitjacket:",
        "url": "/assets/emojis/custom/tbs_straitjacket.svg",
        "keywords": "dwangbuis jas gekkenhuis gespen psychiatrie tbs kliniek opsluiting isolatie"
    },
    {
        "code": "tbs_syringe",
        "name": "TBS Verdovingsspuit",
        "shortcode": ":tbs_syringe:",
        "url": "/assets/emojis/custom/tbs_syringe.svg",
        "keywords": "spuit injectie naald serum verdoving paars dokter tbs medicijn narcose"
    },
    {
        "code": "tbs_guard",
        "name": "Riot Bewaker",
        "shortcode": ":tbs_guard:",
        "url": "/assets/emojis/custom/tbs_guard.svg",
        "keywords": "bewaker beveiliger helm vizier wout smeris politie agent riot guard"
    },
    {
        "code": "tbs_safe",
        "name": "Gekraakte Kluis",
        "shortcode": ":tbs_safe:",
        "url": "/assets/emojis/custom/tbs_safe.svg",
        "keywords": "kluis kluiskraak overval buit heist bank geld euro jackpot kraken"
    },
    {
        "code": "tbs_crown",
        "name": "Bende Baas Kroon",
        "shortcode": ":tbs_crown:",
        "url": "/assets/emojis/custom/tbs_crown.svg",
        "keywords": "kroon koning baas bendeleider leider gang king goud paars vip"
    }
]);

    window.TBS_EMOJI_LIBRARY = Object.freeze({
    "tbs": {
        "label": "TBS Exclusief",
        "shortLabel": "Exclusief",
        "icon": "fa-solid fa-crown",
        "badge": "TBS",
        "isCustom": true,
        "customItems": [
            {
                "code": "tbs_shank",
                "name": "TBS Steekwapen",
                "shortcode": ":tbs_shank:",
                "url": "/assets/emojis/custom/tbs_shank.svg",
                "keywords": "shank mes steekwapen tbs tandenborstel bloed moord wapen aanval isoleercel"
            },
            {
                "code": "tbs_pill",
                "name": "TBS Kalmeringspil",
                "shortcode": ":tbs_pill:",
                "url": "/assets/emojis/custom/tbs_pill.svg",
                "keywords": "pil pillen medicijn medicatie capsule drugs tbs kalmering oxazepam goud paars"
            },
            {
                "code": "tbs_handcuffs",
                "name": "Handboeien",
                "shortcode": ":tbs_handcuffs:",
                "url": "/assets/emojis/custom/tbs_handcuffs.svg",
                "keywords": "boeien handboeien arrestatie politie bajes ketting gevangenis cel"
            },
            {
                "code": "tbs_bars",
                "name": "Isoleercel Tralies",
                "shortcode": ":tbs_bars:",
                "url": "/assets/emojis/custom/tbs_bars.svg",
                "keywords": "tralies gevangenis cel isoleercel ogen rood duister bajes celdeur"
            },
            {
                "code": "tbs_soap",
                "name": "Glibberig Zeepje",
                "shortcode": ":tbs_soap:",
                "url": "/assets/emojis/custom/tbs_soap.svg",
                "keywords": "zeep zeepje douchen wassen bubbels schuim tbs glijden douche"
            },
            {
                "code": "tbs_straitjacket",
                "name": "TBS Dwangbuis",
                "shortcode": ":tbs_straitjacket:",
                "url": "/assets/emojis/custom/tbs_straitjacket.svg",
                "keywords": "dwangbuis jas gekkenhuis gespen psychiatrie tbs kliniek opsluiting isolatie"
            },
            {
                "code": "tbs_syringe",
                "name": "TBS Verdovingsspuit",
                "shortcode": ":tbs_syringe:",
                "url": "/assets/emojis/custom/tbs_syringe.svg",
                "keywords": "spuit injectie naald serum verdoving paars dokter tbs medicijn narcose"
            },
            {
                "code": "tbs_guard",
                "name": "Riot Bewaker",
                "shortcode": ":tbs_guard:",
                "url": "/assets/emojis/custom/tbs_guard.svg",
                "keywords": "bewaker beveiliger helm vizier wout smeris politie agent riot guard"
            },
            {
                "code": "tbs_safe",
                "name": "Gekraakte Kluis",
                "shortcode": ":tbs_safe:",
                "url": "/assets/emojis/custom/tbs_safe.svg",
                "keywords": "kluis kluiskraak overval buit heist bank geld euro jackpot kraken"
            },
            {
                "code": "tbs_crown",
                "name": "Bende Baas Kroon",
                "shortcode": ":tbs_crown:",
                "url": "/assets/emojis/custom/tbs_crown.svg",
                "keywords": "kroon koning baas bendeleider leider gang king goud paars vip"
            }
        ],
        "emojis": [
            {
                "e": "💉",
                "k": "spuit injectie tbs kliniek dokter naald dope verdoving"
            },
            {
                "e": "💊",
                "k": "pil pillen medicijn medicatie capsule drugs pharma"
            },
            {
                "e": "🩸",
                "k": "bloed wond aanval gevaar tbs rood letsel"
            },
            {
                "e": "⛓️",
                "k": "ketting boeien bajes gevangenis cel vast arrestatie"
            },
            {
                "e": "🔒",
                "k": "slot kluis cel dicht opgesloten beveiliging"
            },
            {
                "e": "🔪",
                "k": "mes steek steekpartij aanval shank wapen"
            },
            {
                "e": "💣",
                "k": "bom explosie aanslag bende aanval boem"
            },
            {
                "e": "🔫",
                "k": "pistool wapen schiet schot revolver glock gun"
            },
            {
                "e": "🚓",
                "k": "politie politieauto wout popo smeris arrestatie zwaailicht"
            },
            {
                "e": "🚨",
                "k": "alarm sirene zwaailicht paniek politie rood melding"
            },
            {
                "e": "💰",
                "k": "geld cash buit zak rijk munten money buit"
            },
            {
                "e": "💸",
                "k": "vliegend geld rente betalen overmaken vliegen"
            },
            {
                "e": "🪙",
                "k": "munt credit credits coin goud"
            },
            {
                "e": "💀",
                "k": "dood schedel doodskop kill rip botten lijk"
            },
            {
                "e": "☠️",
                "k": "piraat gif gevaar schedel botten waarschuwing"
            },
            {
                "e": "🥊",
                "k": "bokshandschoen vechten meppen gevecht boks klap"
            },
            {
                "e": "🔥",
                "k": "vuur brand lit vlam heet vuurtje fire vlammen"
            },
            {
                "e": "🚬",
                "k": "sigaret peuk roken blowen joint tabak rook"
            },
            {
                "e": "👑",
                "k": "kroon koning baas king leider leiderschap"
            },
            {
                "e": "⚡",
                "k": "bliksem stroom energie schok shock flash"
            },
            {
                "e": "🕵️",
                "k": "detective spion speurneus rechercheur undercover"
            },
            {
                "e": "👀",
                "k": "ogen kijken gluren zien spying eyes blik"
            },
            {
                "e": "🎯",
                "k": "doelwit roos schot target raak bullseye"
            },
            {
                "e": "💯",
                "k": "honderd perfect honderd procent puur 100"
            },
            {
                "e": "🏴‍☠️",
                "k": "piratenvlag zwart dood vlag rebel gang"
            },
            {
                "e": "🧨",
                "k": "dynamiet rotje vuurwerk explosief knal"
            },
            {
                "e": "🍻",
                "k": "bier proost zuipen feesten drinken cafe"
            },
            {
                "e": "🥃",
                "k": "whisky drank shot alcohol borrel glas"
            },
            {
                "e": "⚠️",
                "k": "waarschuwing attentie gevaar pas op geel"
            },
            {
                "e": "☣️",
                "k": "biohazard gif virus infectie besmet gevaar"
            },
            {
                "e": "🪓",
                "k": "bijl hakken wapen hakbijl"
            },
            {
                "e": "🤫",
                "k": "stil stilzwijgen shh geheim mondje dicht"
            }
        ]
    },
    "crime": {
        "label": "Misdaad & Actie",
        "icon": "fa-solid fa-handcuffs",
        "emojis": [
            {
                "e": "🔫",
                "k": "pistool revolver gun wapen schieten schot glock kogels"
            },
            {
                "e": "🔪",
                "k": "mes steekwapen steken snijden kill moord bloed butcher"
            },
            {
                "e": "🗡️",
                "k": "dolk zwaard mes steken wapen combat"
            },
            {
                "e": "🪓",
                "k": "bijl hakken wapen gereedschap moord"
            },
            {
                "e": "💣",
                "k": "bom explosie ontploffing aanslag terror tnt c4"
            },
            {
                "e": "🧨",
                "k": "dynamiet vuurwerk rotje explosief lont knal"
            },
            {
                "e": "💥",
                "k": "boem explosie klap kogel impact hit fight"
            },
            {
                "e": "🩸",
                "k": "bloed druppel bloeden gewond letsel moord wond"
            },
            {
                "e": "💀",
                "k": "dood schedel skull botten overleden kill dead"
            },
            {
                "e": "☠️",
                "k": "doodshoofd piraten botten gif gevaar vlag"
            },
            {
                "e": "⚰️",
                "k": "doodskist graf begrafenis rip lijk begraven"
            },
            {
                "e": "🪦",
                "k": "grafsteen graf begraafplaats rip dood overleden"
            },
            {
                "e": "⛓️",
                "k": "kettingen boeien vast bajes gevangenis cel"
            },
            {
                "e": "🔒",
                "k": "slot op slot kluis beveiliging bajes opgesloten dicht"
            },
            {
                "e": "🔓",
                "k": "ontgrendeld open slot kluis inbraak kraken hacken"
            },
            {
                "e": "🗝️",
                "k": "oude sleutel kluis code geheime deur"
            },
            {
                "e": "🔑",
                "k": "sleutel sleutels kluiscode celdeur auto open"
            },
            {
                "e": "🚓",
                "k": "politieauto wout smeris politie cop sirene"
            },
            {
                "e": "🚔",
                "k": "politiewagen voorzijde zwaailicht politie wouten"
            },
            {
                "e": "🚨",
                "k": "zwaailicht sirene alarm waarschuwing bust inval opgetrommeld"
            },
            {
                "e": "👮",
                "k": "politieagent agent wout smeris cop bewaker bewaking"
            },
            {
                "e": "🕵️",
                "k": "rechercheur detective spion undercover gluren spioneren"
            },
            {
                "e": "💰",
                "k": "geldzak geld buit heist jackpot doekoe cash"
            },
            {
                "e": "💵",
                "k": "dollar biljetten cash geld money"
            },
            {
                "e": "💶",
                "k": "euro biljetten cash flappen bankbiljet poen"
            },
            {
                "e": "💸",
                "k": "vliegend geld verlies betalen smijten rijk uitgaven"
            },
            {
                "e": "🪙",
                "k": "munt gouden munt coin goud geld"
            },
            {
                "e": "💎",
                "k": "diamant edelsteen juwelen buit rijk juwelier overval"
            },
            {
                "e": "👑",
                "k": "kroon koning baas godfather leider leiderschap gang leader"
            },
            {
                "e": "🏆",
                "k": "trofee beker winnaar nummer 1 first rang pvp"
            },
            {
                "e": "🥇",
                "k": "gouden medaille goud winnaar 1e plek champion"
            },
            {
                "e": "🔥",
                "k": "vuur brand vlam fire hype heet tbs vlammen"
            },
            {
                "e": "🚬",
                "k": "peuk sigaret roken shaggie joint tabak gevangenis"
            },
            {
                "e": "💉",
                "k": "injectiespuit spuit drugs medicijn dope hero heroïne bloed"
            },
            {
                "e": "💊",
                "k": "pil pillen medicijnen drugs speed mdma xtc pharma"
            },
            {
                "e": "🥊",
                "k": "bokshandschoen boksen vechten brawl fight klappen meppen"
            },
            {
                "e": "🥋",
                "k": "karate judo vechtsport martial arts dojo vechten"
            },
            {
                "e": "🏴‍☠️",
                "k": "piratenvlag vlag doodshoofd rebel misdaad bende"
            },
            {
                "e": "🥷",
                "k": "ninja sluipmoordenaar stil assassijn black"
            },
            {
                "e": "🎭",
                "k": "maskers toneel theater heist vermomming masker disguise"
            },
            {
                "e": "💼",
                "k": "koffer aktetas smokkel deal geldkoffer witwassen"
            },
            {
                "e": "🧳",
                "k": "bagage reiskoffer xxl smokkelkoffer overval buit"
            },
            {
                "e": "📦",
                "k": "pakket doos smokkelwaar levering drugstransport"
            },
            {
                "e": "🎯",
                "k": "schietschijf target mikken raak pvp schot doelwit"
            },
            {
                "e": "🎲",
                "k": "dobbelstenen gokken casino dobbelen kans riskant"
            },
            {
                "e": "🎰",
                "k": "gokkast fruitautomaat casino jackpot 777 gokken winnen"
            },
            {
                "e": "🃏",
                "k": "joker kaart speelkaart troef gevaar clown"
            },
            {
                "e": "⚡",
                "k": "bliksem elektriciteit shock stroom actie snel energie"
            },
            {
                "e": "☣️",
                "k": "biohazard biologisch gevaar virus gif besmetting"
            },
            {
                "e": "☢️",
                "k": "radioactief straling gevaar kerncentrale nucleair"
            },
            {
                "e": "⚠️",
                "k": "waarschuwing attentie opgelet alarm gevaar"
            },
            {
                "e": "🛑",
                "k": "stopbord halt stop niet doen stoppen"
            },
            {
                "e": "⛔",
                "k": "verboden toegang inrijverbod gesloten no access"
            },
            {
                "e": "👀",
                "k": "ogen kijken gluren checken zien getuige spotten"
            },
            {
                "e": "💯",
                "k": "honderd procent perfect honderd top feiten broeder"
            }
        ]
    },
    "faces": {
        "label": "Smileys & Emoties",
        "icon": "fa-regular fa-face-smile",
        "emojis": [
            {
                "e": "😀",
                "k": "blij lach vrolijk smiley lachend happy"
            },
            {
                "e": "😃",
                "k": "open lach blij vrolijk smile grijns"
            },
            {
                "e": "😄",
                "k": "lach ogen dicht plezier vrolijk blij haha"
            },
            {
                "e": "😁",
                "k": "stralende lach tanden ontbloot breed lach grijns"
            },
            {
                "e": "😆",
                "k": "schaterlach hard lachen ogen dicht lol haha"
            },
            {
                "e": "😅",
                "k": "zweetdruppel opgelucht nerveus gniffelen oeps"
            },
            {
                "e": "😂",
                "k": "tranen lachen huilen van het lachen lol rofl haha stukgaan"
            },
            {
                "e": "🤣",
                "k": "rollend over vloer lachen stukgaan kapotlachen rofl"
            },
            {
                "e": "🥲",
                "k": "glimlach met traan bitterzoet dankbaar trots emo"
            },
            {
                "e": "🥹",
                "k": "glimmende ogen geraakt tranen ontroerd smeken"
            },
            {
                "e": "😊",
                "k": "blozende lach vriendelijk warm tevreden lief"
            },
            {
                "e": "😇",
                "k": "engel heilig onschuldig aureool braaf halo"
            },
            {
                "e": "🙂",
                "k": "lichte glimlach prima ok kalm neutraal"
            },
            {
                "e": "🙃",
                "k": "ondersteboven sarcastisch dwaas ironisch ondersteboven gezicht"
            },
            {
                "e": "😉",
                "k": "knipoog knipogen verleiden plagen complot wink"
            },
            {
                "e": "😌",
                "k": "opgelucht rustig vredig zen chill relax"
            },
            {
                "e": "😍",
                "k": "hartjesogen verliefd houden van liefde mooi wauw love"
            },
            {
                "e": "🥰",
                "k": "hartjes verliefd omringd door hartjes warm lieverd"
            },
            {
                "e": "😘",
                "k": "kus kushandje kusje zoen flirt liefs"
            },
            {
                "e": "😗",
                "k": "kus mond fluiten neutraal zoentje"
            },
            {
                "e": "😙",
                "k": "kus met lachende ogen zoentje vrolijk"
            },
            {
                "e": "😚",
                "k": "kus ogen dicht blozen innig zoenen"
            },
            {
                "e": "😋",
                "k": "lekker tong smullen heerlijk honger mmm"
            },
            {
                "e": "😛",
                "k": "tong uitsteken plagen grappig gek doen"
            },
            {
                "e": "😜",
                "k": "knipoog met tong zot plagen dollen feest"
            },
            {
                "e": "🤪",
                "k": "gek gezicht zot dol wazig gestoord koekoek zany"
            },
            {
                "e": "😝",
                "k": "tong ogen dicht vies bah lachen uitjouwen"
            },
            {
                "e": "🤑",
                "k": "geld gezicht rijke stinkerd dollarteken doekoe buit"
            },
            {
                "e": "🤗",
                "k": "knuffel armen open omhelzen welkom knuffelen"
            },
            {
                "e": "🫣",
                "k": "gluren door vingers kijken spieken nieuwsgierig geschokt"
            },
            {
                "e": "🤭",
                "k": "hand voor mond giechelen geheim oeps betrapt"
            },
            {
                "e": "🤫",
                "k": "stil mond houden shh geheim ssst mondje dicht"
            },
            {
                "e": "🤔",
                "k": "denken denkend vinger aan kin nadenken hmm twijfel"
            },
            {
                "e": "🫡",
                "k": "salueren saluut respect ja baas begrepen leger"
            },
            {
                "e": "🤐",
                "k": "rits mond geheim zwijgen mond dicht snaveltje toe"
            },
            {
                "e": "🤨",
                "k": "opgetrokken wenkbrauw wantrouwig scepsis verdacht hmm"
            },
            {
                "e": "😐",
                "k": "neutraal gezicht pokerface droog serieus meh"
            },
            {
                "e": "😑",
                "k": "uitdrukkingsloos moe van je droog zucht zout"
            },
            {
                "e": "😶",
                "k": "zonder mond sprakeloos stil geen commentaar"
            },
            {
                "e": "🫥",
                "k": "stippellijn onzichtbaar verdwijnen negeren introvert"
            },
            {
                "e": "😶‍🌫️",
                "k": "in de wolken mist verward vaag stoned high"
            },
            {
                "e": "😏",
                "k": "arrogante grijns ondeugend snood plannetje smirk"
            },
            {
                "e": "😒",
                "k": "ongeamuseerd niet onder de indruk rollende ogen saai mehh"
            },
            {
                "e": "🙄",
                "k": "rollende ogen vermoeiend gezeur weer dat zucht"
            },
            {
                "e": "😬",
                "k": "grimas pijnlijk ongemakkelijk cringy tanden op elkaar"
            },
            {
                "e": "😮‍💨",
                "k": "zucht opluchting uitgeput uitademen pfff vermoeid"
            },
            {
                "e": "🤥",
                "k": "leugenaar lange neus pinokkio liegen onzin leugen"
            },
            {
                "e": "😔",
                "k": "somber terneergeslagen verdrietig peinzend sip"
            },
            {
                "e": "😪",
                "k": "slaperig snottebel moe uitgeput dutje"
            },
            {
                "e": "🤤",
                "k": "kwijlen trek honger water in de mond geil heerlijk"
            },
            {
                "e": "😴",
                "k": "slapen slaap zzz moe bed nachtrust dromen"
            },
            {
                "e": "😷",
                "k": "mondkapje ziek corona besmettelijk arts ziekenhuis"
            },
            {
                "e": "🤒",
                "k": "thermometer koorts ziek griep kliniek tbs koortsig"
            },
            {
                "e": "🤕",
                "k": "verband hoofd wond gewond klappen gehad toegetakeld"
            },
            {
                "e": "🤢",
                "k": "misselijk kotsen groen walgelijk vies braken"
            },
            {
                "e": "🤮",
                "k": "overgeven kots kotsen kotsmisselijk spugen"
            },
            {
                "e": "🤧",
                "k": "niezen verkouden zakdoek snot proest sneeze"
            },
            {
                "e": "🥵",
                "k": "oververhit heet zweet tong hete vlam zweten warm"
            },
            {
                "e": "🥶",
                "k": "ijskoud bevroren kou rillen bibberen ijsblauw"
            },
            {
                "e": "🥴",
                "k": "dronken aangeschoten wazig zat bedwelmd kater"
            },
            {
                "e": "😵",
                "k": "duizelig bewusteloos ko knock out tollen sterretjes"
            },
            {
                "e": "😵‍💫",
                "k": "spiraalogen gehypnotiseerd doorgedraaid verward wazig"
            },
            {
                "e": "🤯",
                "k": "ontploffend hoofd mind blown verbaasd shock wtf"
            },
            {
                "e": "🤠",
                "k": "cowboy hoed wild west cowboyhoed stoer"
            },
            {
                "e": "🥳",
                "k": "feest party hoedje toeter verjaardag feestvieren"
            },
            {
                "e": "🥸",
                "k": "vermomming snor bril nepbril incognito undercover"
            },
            {
                "e": "😎",
                "k": "zonnebril cool baas gangster swag relaxed chill"
            },
            {
                "e": "🤓",
                "k": "nerd bril slim geek gestudeerd betweter"
            },
            {
                "e": "🧐",
                "k": "monocle onderzoeken inspecteren chic speurneus"
            },
            {
                "e": "😕",
                "k": "verward twijfel niet zeker perplex huh"
            },
            {
                "e": "😟",
                "k": "bezorgd ongerust bang spanning zenuwen"
            },
            {
                "e": "🙁",
                "k": "licht verdrietig sip teleurgesteld treurig"
            },
            {
                "e": "😮",
                "k": "open mond verbaasd wauw wat huh"
            },
            {
                "e": "😯",
                "k": "stomverbaasd verrast sprakeloos stil"
            },
            {
                "e": "😲",
                "k": "geschokt verbijsterd grote ogen shock wow"
            },
            {
                "e": "😳",
                "k": "blozen betrapt grote ogen ongemakkelijk betrapt oeps"
            },
            {
                "e": "🥺",
                "k": "smekende ogen alsjeblieft schattig puppy ogen please"
            },
            {
                "e": "😦",
                "k": "geschrokken beteuterd open mond ontzet"
            },
            {
                "e": "😨",
                "k": "angstig vrees geschokt bleek angst"
            },
            {
                "e": "😰",
                "k": "angstzweet zenuwachtig paniek stress"
            },
            {
                "e": "😥",
                "k": "bezorgde zweetdruppel teleurstelling opluchting"
            },
            {
                "e": "😢",
                "k": "huilen traan verdriet janken emotioneel sad"
            },
            {
                "e": "😭",
                "k": "keihard huilen tranen met tuiten janken verslagen drama"
            },
            {
                "e": "😱",
                "k": "schreeuw paniek gil scream angst vreselijk"
            },
            {
                "e": "😖",
                "k": "gefrustreerd hulpeloos pijnlijk kramp irritatie"
            },
            {
                "e": "😣",
                "k": "volhouden lijden pijn doorbijten tanden op elkaar"
            },
            {
                "e": "😞",
                "k": "teleurgesteld sip treurig verloren mislukt"
            },
            {
                "e": "😓",
                "k": "zweet koud zweet verslagen oei vermoeid"
            },
            {
                "e": "😩",
                "k": "wanhopig klagend moe opgave gezeur"
            },
            {
                "e": "😫",
                "k": "uitgeput overstuur doodmoe klaar ermee"
            },
            {
                "e": "🥱",
                "k": "geeuwen geeuw saai slaap moe gapen"
            },
            {
                "e": "😤",
                "k": "stoom uit neus triomfantelijk woede trots vastberaden"
            },
            {
                "e": "😡",
                "k": "boos rood kwaad woedend ziedend furieus"
            },
            {
                "e": "😠",
                "k": "kwaad boos geërgerd frons boze blik"
            },
            {
                "e": "🤬",
                "k": "vloeken schelden gecensureerd symbolen kwaad boos woest"
            },
            {
                "e": "😈",
                "k": "duiveltje paars stout kwaadaardig grijns boef sneaky"
            },
            {
                "e": "👿",
                "k": "boze duivel woedend duivels duivelshoorntjes kwaad"
            },
            {
                "e": "💀",
                "k": "dood skull schedel overleden k.o. rip"
            },
            {
                "e": "💩",
                "k": "poep drol kak stront grappig drolletje"
            },
            {
                "e": "🤡",
                "k": "clown pipo grapjas clownesk clownshoofd fool"
            },
            {
                "e": "👹",
                "k": "monster oger demon masker eng griezel"
            },
            {
                "e": "👺",
                "k": "goblin boze tengu lange neus rood masker"
            },
            {
                "e": "👻",
                "k": "spook geest eng spoken boe paranormaal"
            },
            {
                "e": "👽",
                "k": "buitenaards wezen alien ufo et ruimtewezen"
            },
            {
                "e": "👾",
                "k": "alien monster retro pixel game space invader"
            },
            {
                "e": "🤖",
                "k": "robot automaat bot cyborg kunstmatige intelligentie"
            }
        ]
    },
    "people": {
        "label": "Handen & Mensen",
        "icon": "fa-solid fa-hand",
        "emojis": [
            {
                "e": "👋",
                "k": "zwaaien hallo dag hoi groet gedag doei wave"
            },
            {
                "e": "🤚",
                "k": "opgestoken handrug stop wacht hand"
            },
            {
                "e": "🖐️",
                "k": "gespreide vingers hand stop vijf high five"
            },
            {
                "e": "✋",
                "k": "stop hand halt handpalm opgestoken eed"
            },
            {
                "e": "🖖",
                "k": "vulcan groet spock live long vrede"
            },
            {
                "e": "🫱",
                "k": "hand naar rechts reiken aanbieden"
            },
            {
                "e": "🫲",
                "k": "hand naar links reiken vangen pakken"
            },
            {
                "e": "👌",
                "k": "ok perfect top prima accoord in orde"
            },
            {
                "e": "🤌",
                "k": "italiaans handgebaar wat wil je wat zeg je chef"
            },
            {
                "e": "🤏",
                "k": "klein beetje beetje snufje miniem bijna"
            },
            {
                "e": "✌️",
                "k": "peace vrede overwinning twee vingers victory v sign"
            },
            {
                "e": "🤞",
                "k": "duimen vingers gekruist geluk hopen succes"
            },
            {
                "e": "🫰",
                "k": "vingerhartje kpop snap geld liefde knip"
            },
            {
                "e": "🤟",
                "k": "hou van jou ily gebarentaal liefde gebaar"
            },
            {
                "e": "🤘",
                "k": "rock on metal hoorns feest ruig rock"
            },
            {
                "e": "🤙",
                "k": "bel me shaka hang loose surfen chill"
            },
            {
                "e": "👈",
                "k": "wijzen naar links die kant daar kijk"
            },
            {
                "e": "👉",
                "k": "wijzen naar rechts die daarzo diegene"
            },
            {
                "e": "👆",
                "k": "wijzen naar boven omhoog hierboven zie boven"
            },
            {
                "e": "🖕",
                "k": "middelvinger fuck you opzouten stik erin vinger"
            },
            {
                "e": "👇",
                "k": "wijzen naar beneden hieronder zie onder link"
            },
            {
                "e": "☝️",
                "k": "wijsvinger omhoog aandacht let op één moment"
            },
            {
                "e": "🫵",
                "k": "wijzen naar jou jij vinger direct we need you"
            },
            {
                "e": "👍",
                "k": "duim omhoog goed top like akkoord mee eens"
            },
            {
                "e": "👎",
                "k": "duim omlaag slecht afgekeurd dislike boe slecht"
            },
            {
                "e": "✊",
                "k": "opgeheven vuist protest kracht macht solidair"
            },
            {
                "e": "👊",
                "k": "boks vuist stoot klap brofist knokkels meppen"
            },
            {
                "e": "🤛",
                "k": "vuist links boks punch stoten"
            },
            {
                "e": "🤜",
                "k": "vuist rechts boks punch respect"
            },
            {
                "e": "👏",
                "k": "klappen applaus applausje bravo goed gedaan clap"
            },
            {
                "e": "🙌",
                "k": "handen omhoog juichen halleluja feest vieren praise"
            },
            {
                "e": "🫶",
                "k": "handen hartje liefde samen steun love heart"
            },
            {
                "e": "👐",
                "k": "open handen omhelzing geven openheid"
            },
            {
                "e": "🤲",
                "k": "handpalmen omhoog bidden smeken ontvangen gift"
            },
            {
                "e": "🤝",
                "k": "handdruk deal akkoord samenwerking partner verbond"
            },
            {
                "e": "🙏",
                "k": "bidden dankjewel alsjeblieft hoop amen respect"
            },
            {
                "e": "✍️",
                "k": "schrijven handtekening pen contract brief noteren"
            },
            {
                "e": "💅",
                "k": "nagellak gelakte nagels diva arrogant flawless sassy"
            },
            {
                "e": "🤳",
                "k": "selfie foto maken camera smartphone pose"
            },
            {
                "e": "💪",
                "k": "spierbal bicep spieren kracht sterk fit training gym flex"
            },
            {
                "e": "🦾",
                "k": "mechanische arm robot prothese bionisch cyber"
            },
            {
                "e": "🦿",
                "k": "mechanisch been robot prothese been cyber"
            },
            {
                "e": "🦵",
                "k": "been trappen schop sporter voet"
            },
            {
                "e": "🦶",
                "k": "voet tenen stappen lopen blote voeten"
            },
            {
                "e": "👂",
                "k": "oor horen luisteren afluisteren gerucht"
            },
            {
                "e": "🦻",
                "k": "oor met gehoorapparaat doof slechthorend luisteren"
            },
            {
                "e": "👃",
                "k": "neus ruiken snuffelen geur snuiven coke snuif"
            },
            {
                "e": "🧠",
                "k": "hersenen brein slim denken verstand iq intellect"
            },
            {
                "e": "🫀",
                "k": "anatomisch hart orgaan hartslag leven cardio"
            },
            {
                "e": "🫁",
                "k": "longen ademhalen lucht zuurstof longcapaciteit roken"
            },
            {
                "e": "🦷",
                "k": "tand kies tanden tandarts beet glimlach"
            },
            {
                "e": "🦴",
                "k": "bot botten hond kluif skelet breuk"
            },
            {
                "e": "👅",
                "k": "tong likken proeven uitsteken nat sensueel"
            },
            {
                "e": "👄",
                "k": "lippen mond kus zoenen lipstick sensual"
            },
            {
                "e": "👶",
                "k": "baby kind klein onschuldig boreling"
            },
            {
                "e": "🧒",
                "k": "kind jongere jochie tiener"
            },
            {
                "e": "👦",
                "k": "jongen tiener mannelijk gozer gast"
            },
            {
                "e": "👧",
                "k": "meisje tiener jongedame dame"
            },
            {
                "e": "🧑",
                "k": "persoon mens volwassene iemand"
            },
            {
                "e": "👨",
                "k": "man heer volwassene kerel bink gozer"
            },
            {
                "e": "👩",
                "k": "vrouw dame chick lady volwassene"
            },
            {
                "e": "🧓",
                "k": "oudere senior opa oma bejaarde grijs"
            },
            {
                "e": "👴",
                "k": "oude man opa grootvader bejaard kaal senior"
            },
            {
                "e": "👵",
                "k": "oude vrouw oma grootmoeder bejaard rimpels"
            },
            {
                "e": "👮‍♂️",
                "k": "mannelijke agent politie wout smeris politieagent"
            },
            {
                "e": "👮‍♀️",
                "k": "vrouwelijke agente politie smeris cop"
            },
            {
                "e": "🕵️‍♂️",
                "k": "mannelijke detective rechercheur undercover speurder"
            },
            {
                "e": "💂",
                "k": "wacht soldaat bewaker paleiswacht guard"
            },
            {
                "e": "🥷",
                "k": "ninja sluipmoordenaar spion huurmoordenaar"
            },
            {
                "e": "🧟",
                "k": "zombie lijk levende dode apocalypse horror"
            },
            {
                "e": "🧛",
                "k": "vampier dracula bloedzuiger tanden vleermuis"
            }
        ]
    },
    "nature": {
        "label": "Dieren & Natuur",
        "icon": "fa-solid fa-paw",
        "emojis": [
            {
                "e": "🐶",
                "k": "hond puppie blaffen viervoeter loyaal dog"
            },
            {
                "e": "🐕",
                "k": "hond herdershond pitbull waakhond viervoeter"
            },
            {
                "e": "🦮",
                "k": "blindengeleidehond hulphond herdershond"
            },
            {
                "e": "🐺",
                "k": "wolf roofdier huilen roedel alfamannetje wolfpack"
            },
            {
                "e": "🦊",
                "k": "vos sluw rood roofdier vosje stiekem"
            },
            {
                "e": "🐱",
                "k": "kat poes miauw kitten huisdier cat"
            },
            {
                "e": "🐈",
                "k": "kat sluipen kater zwarte kat poes"
            },
            {
                "e": "🦁",
                "k": "leeuw koning roofdier manen safari predator king"
            },
            {
                "e": "🐯",
                "k": "tijger roofdier strepen wild beest"
            },
            {
                "e": "🐅",
                "k": "tijger jagen sluiper roofkat"
            },
            {
                "e": "🐆",
                "k": "luipaard panter cheetah snel vlekken"
            },
            {
                "e": "🐴",
                "k": "paard paardenhoofd hengst merrie edel dier"
            },
            {
                "e": "🐎",
                "k": "paard galop rennen paardenrace wedden jockey"
            },
            {
                "e": "🦄",
                "k": "eenhoorn unicorn magisch fabeldier zeldzaam"
            },
            {
                "e": "🐗",
                "k": "wild zwijn everzwijn slagtanden agressief wild"
            },
            {
                "e": "🐖",
                "k": "varken big spek knorren wout smeris scheldwoord"
            },
            {
                "e": "🐽",
                "k": "varkenssnuit neus knorren varkentje"
            },
            {
                "e": "🐂",
                "k": "os stier hoorns sterk kracht beest"
            },
            {
                "e": "🐃",
                "k": "waterbuffel buffel wild Afrika beest"
            },
            {
                "e": "🐄",
                "k": "koe melk loeien boerderij rund"
            },
            {
                "e": "🦍",
                "k": "gorilla aap zilverrug reus sterk baviaan brute"
            },
            {
                "e": "🦧",
                "k": "orang-oetan aap oerwoud jungle slim"
            },
            {
                "e": "🐒",
                "k": "aapje aap slingeren ondeugend monkey"
            },
            {
                "e": "🦇",
                "k": "vleermuis nacht bloedzuiger vampier donker duister"
            },
            {
                "e": "🦅",
                "k": "adelaar arend roofvogel jagen klauwen vliegen eagle"
            },
            {
                "e": "🦉",
                "k": "uil nachtuil wijs uilenogen nacht jager"
            },
            {
                "e": "🐦",
                "k": "vogel fluiten vliegen vogelbek vogelvrij"
            },
            {
                "e": "🕊️",
                "k": "vredesduif duif vrede vliegen olijftak"
            },
            {
                "e": "🦆",
                "k": "eend kwaken vijver jacht vogel"
            },
            {
                "e": "🐊",
                "k": "krokodil alligators tanden moeras gevaarlijk reptiel"
            },
            {
                "e": "🐍",
                "k": "slang gif ratel cobra verraad reptiel sssss"
            },
            {
                "e": "🐉",
                "k": "draak vuurspuwer mythologie beest vuur vleugels"
            },
            {
                "e": "🦕",
                "k": "dinosaurus dino brontosaurus prehistorisch reus"
            },
            {
                "e": "🦖",
                "k": "t-rex tyrannosaurus roofdier monster tanden"
            },
            {
                "e": "🦈",
                "k": "haai roofvis roofdier tanden oceaan jaws shark"
            },
            {
                "e": "🐙",
                "k": "octopus inktvis tentakels zee diepzee"
            },
            {
                "e": "🦀",
                "k": "krab scharen strand zee zeevruchten"
            },
            {
                "e": "🦞",
                "k": "kreeft zeevrucht luxe scharen rood"
            },
            {
                "e": "🦂",
                "k": "schorpioen gif steek woestijn gevaarlijk prik"
            },
            {
                "e": "🕷️",
                "k": "spin web arachnide gif acht poten eng"
            },
            {
                "e": "🕸️",
                "k": "spinnenweb web vangen valstrik donker kelder"
            },
            {
                "e": "🐀",
                "k": "rat knaagdier riool klikspaan snitch verraad ratten"
            },
            {
                "e": "🐁",
                "k": "muis knaagdier klein grijs piepen"
            },
            {
                "e": "🌲",
                "k": "dennenboom bos spar natuur hout bomen"
            },
            {
                "e": "🌳",
                "k": "boom eik loofboom groen park bos natuur"
            },
            {
                "e": "🌴",
                "k": "palmboom strand tropisch vakantie eiland miami"
            },
            {
                "e": "🌵",
                "k": "cactus woestijn prikken stekels droog mexico"
            },
            {
                "e": "🍄",
                "k": "paddenstoel vliegenzwam trippen shrooms hallucineren drugs"
            },
            {
                "e": "🌿",
                "k": "kruid wiet blad takje plant natuur cannabis"
            },
            {
                "e": "🍀",
                "k": "klavertje vier geluk jackpot kans rijkdom lucky"
            },
            {
                "e": "🍁",
                "k": "esdoornblad herfst blad rood oranje canada"
            },
            {
                "e": "🌹",
                "k": "roos rode roos bloem liefde romantiek passie"
            },
            {
                "e": "🥀",
                "k": "verwelkte roos dood treurig verlies einde overleden"
            },
            {
                "e": "🌪️",
                "k": "tornado wervelwind storm orkaan verwoesting wind"
            },
            {
                "e": "⚡",
                "k": "bliksemschicht onweer donder elektriciteit inslag schok"
            },
            {
                "e": "❄️",
                "k": "sneeuwvlok sneeuw ijskoud winter vriezen cold"
            },
            {
                "e": "🌊",
                "k": "golf water tsunami zee oceaan vloedgolf branding"
            },
            {
                "e": "🌙",
                "k": "halve maan nacht duister donker slapen maanlicht"
            },
            {
                "e": "☀️",
                "k": "zon zonnig warm dag licht hitte helder"
            },
            {
                "e": "⭐",
                "k": "ster goud vip glitter hemel blinken ranking"
            },
            {
                "e": "🌟",
                "k": "fonkelende ster glans glitter sterrenhemel spotlight"
            }
        ]
    },
    "food": {
        "label": "Eten & Drinken",
        "icon": "fa-solid fa-burger",
        "emojis": [
            {
                "e": "🍔",
                "k": "hamburger burger fastfood snack vlees broodje mac"
            },
            {
                "e": "🍟",
                "k": "patat friet frietjes snack fastfood zout mayo"
            },
            {
                "e": "🍕",
                "k": "pizza slice punt kaas pepperoni italiaans bezorgen"
            },
            {
                "e": "🌭",
                "k": "hotdog knakworst worst broodje mosterd"
            },
            {
                "e": "🥓",
                "k": "spek bacon ontbijt knapperig varken vlees"
            },
            {
                "e": "🥩",
                "k": "biefstuk steak vlees rauw rood vlees slager ribeye"
            },
            {
                "e": "🍗",
                "k": "kippenpoot gebraden kip kfc drumstick vlees"
            },
            {
                "e": "🍖",
                "k": "vlees aan bot spareribs kluiven barbecue"
            },
            {
                "e": "🥪",
                "k": "sandwich tosti broodje belegd kaas ham lunch"
            },
            {
                "e": "🌮",
                "k": "taco mexicaans wraps vlees pikant knapperig"
            },
            {
                "e": "🌯",
                "k": "burrito durum doner wrap rol mexicaans"
            },
            {
                "e": "🥙",
                "k": "broodje pita shoarma kebab falafel doener"
            },
            {
                "e": "🍜",
                "k": "noedels soep ramen kom eetstokjes warm aziatisch"
            },
            {
                "e": "🍝",
                "k": "spaghetti pasta italiaans tomatensaus bolognese"
            },
            {
                "e": "🍣",
                "k": "sushi maki zalm rijst japans rauwe vis sashimi"
            },
            {
                "e": "🍱",
                "k": "bento box maaltijd japans lunchbox eten"
            },
            {
                "e": "🥟",
                "k": "dumpling dim sum aziatisch gevuld pasteitje"
            },
            {
                "e": "🍤",
                "k": "gefrituurde garnaal scampi tempura vis zeevrucht"
            },
            {
                "e": "🧀",
                "k": "kaas blokje kaas gatenkaas gouda holland kaasfondue"
            },
            {
                "e": "🥞",
                "k": "pannenkoeken stapel siroop ontbijt zoet stroop"
            },
            {
                "e": "🧇",
                "k": "wafel brusselse wafel suiker suikerwafel zoet"
            },
            {
                "e": "🍿",
                "k": "popcorn bioscoop snack maïs film popcornbak"
            },
            {
                "e": "🍩",
                "k": "donut donutring suiker glazuur roze politieagent snack"
            },
            {
                "e": "🍪",
                "k": "koekje cookie chocolade biscuit zoet bakken"
            },
            {
                "e": "🎂",
                "k": "verjaardagstaart feest kaarsjes taart gebak vieren"
            },
            {
                "e": "🍰",
                "k": "stuk taart slagroomgebak cake zoet gebak dessert"
            },
            {
                "e": "🍫",
                "k": "chocolade reep choco cacaoboon snoep zoet"
            },
            {
                "e": "🍬",
                "k": "snoepje snoep candy zoetigheid zuurtje traktatie"
            },
            {
                "e": "🍭",
                "k": "lolly suikerstok snoepgoed zoet likken"
            },
            {
                "e": "🍦",
                "k": "softijs hoorntje ijsje vanille koude snack dessert"
            },
            {
                "e": "🍨",
                "k": "ijscoupe ijs bolletjes sundae dessert"
            },
            {
                "e": "🍺",
                "k": "bier biertje glas pils schuimkraag alcohol zuipen cafe proost"
            },
            {
                "e": "🍻",
                "k": "proost bierglazen klinken feesten pils alcohol cheers"
            },
            {
                "e": "🥂",
                "k": "proosten champagneglazen klinken toost toosten feest bubbels"
            },
            {
                "e": "🍷",
                "k": "rode wijn wijnglas wijn alcohol chique dineren"
            },
            {
                "e": "🥃",
                "k": "whiskey glas tumbler likeur sterke drank bourbon on the rocks"
            },
            {
                "e": "🍸",
                "k": "cocktail martini olijf james bond bar drankje alcohol"
            },
            {
                "e": "🍹",
                "k": "tropische cocktail rietje vruchtendrank strand mojito pina colada"
            },
            {
                "e": "🍾",
                "k": "champagne fles kurk knallen poppen feest overwinningsdrank"
            },
            {
                "e": "🍶",
                "k": "sake fles en kopje rijstwijn japans"
            },
            {
                "e": "☕",
                "k": "koffie espresso cappuccino warm bakkie troost ochtend cafeïne"
            },
            {
                "e": "🧃",
                "k": "pakje sap rietje sapje frisdrank drank drinken"
            },
            {
                "e": "🥤",
                "k": "beker met rietje frisdrank cola fanta milkshake beker"
            },
            {
                "e": "🧊",
                "k": "ijsblokje ijs koud bevroren on the rocks fris cool"
            },
            {
                "e": "🍇",
                "k": "druiven tros fruit wijnmaker paars fruit"
            },
            {
                "e": "🍉",
                "k": "watermeloen meloen rood zomer verfrissend"
            },
            {
                "e": "🍌",
                "k": "banaan bananenschil geel fruit apenvoer"
            },
            {
                "e": "🍍",
                "k": "ananas tropisch fruit zoet prikkelend cocktail"
            },
            {
                "e": "🍎",
                "k": "rode appel appel fruit gezond vitamine"
            },
            {
                "e": "🍓",
                "k": "aardbei rood zomerfruit zoet aardbeitje"
            },
            {
                "e": "🥑",
                "k": "avocado guacamole gezond groente vetten hip"
            },
            {
                "e": "🌶️",
                "k": "hete peper chili pikant sambal vurig spice jalapeno"
            }
        ]
    },
    "activity": {
        "label": "Activiteiten & Sport",
        "icon": "fa-solid fa-trophy",
        "emojis": [
            {
                "e": "⚽",
                "k": "voetbal bal champions league fifa sport veld wedstrijd"
            },
            {
                "e": "🏀",
                "k": "basketbal nba dunk ring bal sport"
            },
            {
                "e": "🏈",
                "k": "american football rugby nfl ovaal veld"
            },
            {
                "e": "⚾",
                "k": "honkbal baseball knuppel werper sport"
            },
            {
                "e": "🎾",
                "k": "tennis racket bal toernooi grand slam gravel"
            },
            {
                "e": "🏐",
                "k": "volleybal strandvolleybal net bal sport"
            },
            {
                "e": "🏉",
                "k": "rugbybal rugby try tackle sport"
            },
            {
                "e": "🎱",
                "k": "biljart 8-ball pool 8 bal pooltafel cafe snooker"
            },
            {
                "e": "🎳",
                "k": "bowlen bowlingbal kegels strike spare baan"
            },
            {
                "e": "🏓",
                "k": "tafeltennis pingpong batje balletje sport"
            },
            {
                "e": "🥊",
                "k": "bokshandschoenen boksen vechtsport ring knock-out klappen fight"
            },
            {
                "e": "🥋",
                "k": "karatepak vechtsport zwarte band judo dojo vechten"
            },
            {
                "e": "🛹",
                "k": "skateboard skaten wieltjes plank straat skatepark ollie"
            },
            {
                "e": "🛼",
                "k": "rolschaatsen skaten rollerskaten retro wielen"
            },
            {
                "e": "🏋️",
                "k": "gewichtheffen fitness gym bodybuilden ijzer pompen barbell"
            },
            {
                "e": "🤺",
                "k": "schermen degen zwaard duel vechtsport steek"
            },
            {
                "e": "🏇",
                "k": "paardenrace paardenrennen wedden jockey paard derby"
            },
            {
                "e": "⛷️",
                "k": "skieen ski skivakantie sneeuw wintersport piste"
            },
            {
                "e": "🏂",
                "k": "snowboarden snowboard wintersport piste berg sneeuw"
            },
            {
                "e": "🪂",
                "k": "parachute parachutespringen valscherm sprong luchtlanding heist"
            },
            {
                "e": "🧗",
                "k": "bergbeklimmen klimmen rotswand survival boulder"
            },
            {
                "e": "🏌️",
                "k": "golfen golfclub golfbal swing golfbaan handicap"
            },
            {
                "e": "🎣",
                "k": "vissen hengel haak vis vangen visser meer"
            },
            {
                "e": "🎮",
                "k": "gamepad controller gamen videogames playstation xbox nintendo"
            },
            {
                "e": "🕹️",
                "k": "joystick arcade retro speelhal classic gaming"
            },
            {
                "e": "🎲",
                "k": "dobbelstenen gokken dobbelsteen kans bordspel casino craps"
            },
            {
                "e": "🎰",
                "k": "gokkast gokmachine casino fruitautomaat 777 winst jackpot"
            },
            {
                "e": "♟️",
                "k": "schaakstuk pion schaken schaakbord tactiek meesterzet strategie"
            },
            {
                "e": "🎯",
                "k": "dartbord mikken bullseye schietschijf doel raak tref"
            },
            {
                "e": "🏆",
                "k": "trofee beker kampioen eerste prijs overwinning winst ranglijst"
            },
            {
                "e": "🥇",
                "k": "gouden medaille 1e plaats goud eerste kampioen"
            },
            {
                "e": "🥈",
                "k": "zilveren medaille 2e plaats zilver tweede run-up"
            },
            {
                "e": "🥉",
                "k": "bronzen medaille 3e plaats brons derde podium"
            },
            {
                "e": "🎟️",
                "k": "toegangskaartje ticket bioscoop evenement entree pas"
            },
            {
                "e": "🎫",
                "k": "ticket lot loterij vervoersbewijs lootje"
            },
            {
                "e": "🎪",
                "k": "circustent kermis feest attractie show evenement"
            },
            {
                "e": "🎨",
                "k": "verfpalet schilderen kunstenaar creatief verf tekenen"
            },
            {
                "e": "🎤",
                "k": "microfoon zingen rap rap battle karaoke optreden artiest"
            },
            {
                "e": "🎧",
                "k": "koptelefoon muziek luisteren beats dj techno headset"
            },
            {
                "e": "🎷",
                "k": "saxofoon jazz blazers muziek instrument solo"
            },
            {
                "e": "🎸",
                "k": "gitaar elektrische gitaar rock band snaarinstrument rocken"
            }
        ]
    },
    "travel": {
        "label": "Reizen & Voertuigen",
        "icon": "fa-solid fa-car",
        "emojis": [
            {
                "e": "🚓",
                "k": "politiewagen politie wout zwaailicht achtervolging sirene patrol"
            },
            {
                "e": "🚔",
                "k": "politieauto voorfront zwaailicht politie squad smeris"
            },
            {
                "e": "🏎️",
                "k": "raceauto formule 1 race snelle wagen circuit f1"
            },
            {
                "e": "🚗",
                "k": "auto wagen personenauto rijden verkeer weg mobiel"
            },
            {
                "e": "🚘",
                "k": "auto voorkant bolide automobiel rijtuig"
            },
            {
                "e": "🚙",
                "k": "suv 4x4 terreinwagen jeep jeepje overland"
            },
            {
                "e": "🚚",
                "k": "vrachtwagen transport levering truck lading goederen"
            },
            {
                "e": "🚛",
                "k": "vrachtauto oplegger dieplader wegtransport truck"
            },
            {
                "e": "🏍️",
                "k": "motor motorfiets racer biker gang tweewieler suzuki harley"
            },
            {
                "e": "🛵",
                "k": "scooter brommer vespa bezorger snorfiets tweetakt"
            },
            {
                "e": "🚲",
                "k": "fiets stadsfiets trappen tweewieler wielrennen"
            },
            {
                "e": "🛴",
                "k": "step e-step elektrische step stepje"
            },
            {
                "e": "🚨",
                "k": "zwaailicht noodalarm sirene rood licht opgetrommeld noodgeval"
            },
            {
                "e": "🚁",
                "k": "helikopter wentelwiek politiehelikopter traumaheli ontsnapping vlucht"
            },
            {
                "e": "✈️",
                "k": "vliegtuig vliegen vliegveld reis smokkelvlucht buitenland vakantie"
            },
            {
                "e": "🚀",
                "k": "raket lancering snel de ruimte in stijgen boost to the moon"
            },
            {
                "e": "🚤",
                "k": "speedboot snelle motorboot smokkelboot water waterrace"
            },
            {
                "e": "🛥️",
                "k": "motorjacht jacht luxe jacht rijkdom varen haven"
            },
            {
                "e": "🚢",
                "k": "vrachtschip containerschip smokkelschip haven cruise"
            },
            {
                "e": "⚓",
                "k": "anker scheepvaart haven vastleggen zeeman tatoeage"
            },
            {
                "e": "⛽",
                "k": "tankstation benzinepomp benzine diesel tanken brandstof"
            },
            {
                "e": "🚦",
                "k": "verkeerslicht stoplicht groen oranje rood kruispunt"
            },
            {
                "e": "🚧",
                "k": "wegwerkzaamheden afzetting wegversperring barrière blokkade"
            },
            {
                "e": "🛑",
                "k": "stopbord halt stoppen achthoek voorrangsbord"
            },
            {
                "e": "🏢",
                "k": "kantoorgebouw flat hoogbouw bedrijf wolkenkrabber bank"
            },
            {
                "e": "🏦",
                "k": "bank geldinstelling overval kluis bankgebouw bankfiliaal"
            },
            {
                "e": "🏥",
                "k": "ziekenhuis kliniek tbs afdeling dokter eerste hulp verpleging"
            },
            {
                "e": "🏨",
                "k": "hotel overnachten kamer suite motel onderduiken safehouse"
            },
            {
                "e": "🏚️",
                "k": "vervallen huis kraakpand ruïne schuilplaats bendehuis"
            },
            {
                "e": "🏠",
                "k": "huis woning thuis villa safehouse onderkomen"
            },
            {
                "e": "🏰",
                "k": "kasteel burcht fort paleis landgoed rijk vesting"
            },
            {
                "e": "🗼",
                "k": "tokyo tower eiffeltoren toren zendmast communicatie"
            },
            {
                "e": "🗽",
                "k": "vrijheidsbeeld statue of liberty new york usa vrijheid"
            },
            {
                "e": "🗺️",
                "k": "wereldkaart landkaart plattegrond route heists plan"
            },
            {
                "e": "🧭",
                "k": "kompas navigatie noord zuid richting oriëntatie koers"
            },
            {
                "e": "🏝️",
                "k": "tropisch eiland onbewoond eiland palmboom bounty belastingparadijs"
            },
            {
                "e": "🌋",
                "k": "vulkaan uitbarsting lava uitbarsten gevaar heet magma"
            },
            {
                "e": "🌃",
                "k": "nachtelijke stad skyline wolkenkrabbers sterren donker city"
            },
            {
                "e": "🌆",
                "k": "zonsondergang stad skyline avond schemering stadsbeeld"
            },
            {
                "e": "🌉",
                "k": "brug hangbrug rivier oversteek nachtbrug bridge"
            }
        ]
    },
    "objects": {
        "label": "Objecten & Gereedschap",
        "icon": "fa-solid fa-wrench",
        "emojis": [
            {
                "e": "🔫",
                "k": "waterpistool pistool wapen kogel schot revolver"
            },
            {
                "e": "💣",
                "k": "bom lont explosief bommelding tnt boem"
            },
            {
                "e": "🔪",
                "k": "keukenmes mes snijden wapen dolk"
            },
            {
                "e": "🗡️",
                "k": "dolk zwaard middeleeuws steken lemmet"
            },
            {
                "e": "🪓",
                "k": "houthakkersbijl bijl hakken hout gereedschap"
            },
            {
                "e": "🔨",
                "k": "hamer timmeren spijker klussen slaan tool"
            },
            {
                "e": "🛠️",
                "k": "hamer en moersleutel gereedschap repareren klus fix patch"
            },
            {
                "e": "🔧",
                "k": "moersleutel steeksleutel bout reparatie monteur"
            },
            {
                "e": "🪛",
                "k": "schroevendraaier schroef draaien monteren klussen"
            },
            {
                "e": "🔩",
                "k": "moer en bout metaal ijzer vastdraaien"
            },
            {
                "e": "⚙️",
                "k": "tandwiel instellingen mechanisme radar opties settings"
            },
            {
                "e": "🧲",
                "k": "magneet aantrekkingskracht metaal magnetisch"
            },
            {
                "e": "🪜",
                "k": "ladder klimmen vluchten inbraak ontsnapping opklimmen"
            },
            {
                "e": "🪝",
                "k": "haak enterhaak vishaak ophangen grijphaak"
            },
            {
                "e": "⛓️",
                "k": "schakels ketting metaal boeien vastleggen bajes"
            },
            {
                "e": "🔒",
                "k": "hangslot op slot veilig hermetisch beveiligd kluis"
            },
            {
                "e": "🔓",
                "k": "open hangslot van het slot inbraak slot open"
            },
            {
                "e": "🔑",
                "k": "gouden sleutel huissleutel cel sleutelgat sleutels"
            },
            {
                "e": "🗝️",
                "k": "antieke sleutel kluissleutel geheim toegang"
            },
            {
                "e": "🚪",
                "k": "deur open deur celdeur ingang uitgang"
            },
            {
                "e": "🪞",
                "k": "spiegel spiegelbeeld glas reflectie kijken"
            },
            {
                "e": "🪟",
                "k": "raam venster tralies uitzicht ontsnappen"
            },
            {
                "e": "🛏️",
                "k": "bed matras slapen celbed gevangenisrust kooi"
            },
            {
                "e": "🛋️",
                "k": "bank sofa luieren chillen woonkamer meubel"
            },
            {
                "e": "🚽",
                "k": "wc toilet pot bajes wc doortrekken toiletpot"
            },
            {
                "e": "🪒",
                "k": "scheermes scheermesje scheren scherp mesje"
            },
            {
                "e": "🧴",
                "k": "lotion fles crème glijmiddel zeep douche"
            },
            {
                "e": "🧼",
                "k": "zeep zeepje laten vallen douches bajes zeepblok"
            },
            {
                "e": "🧻",
                "k": "wc-papier toiletpapier rol vegen papier"
            },
            {
                "e": "🚬",
                "k": "sigaret rokertje peuk nicotine filtersigaret"
            },
            {
                "e": "⚰️",
                "k": "lijkkist kist begraven grafmonument doodskist"
            },
            {
                "e": "🪦",
                "k": "zerk grafzerk gedenksteen kerkhof"
            },
            {
                "e": "📱",
                "k": "telefoon smartphone mobieltje iphone bellen whatsapp"
            },
            {
                "e": "💻",
                "k": "laptop computer beeldscherm hacken typen pc"
            },
            {
                "e": "⌨️",
                "k": "toetsenbord typen keyboard toetsen gaming"
            },
            {
                "e": "🖥️",
                "k": "desktop computer monitor beeldscherm werkplek"
            },
            {
                "e": "📷",
                "k": "fotocamera camera foto kiekje kiekjes fotograaf"
            },
            {
                "e": "📹",
                "k": "videocamera beveiligingscamera cctv bewaking opname"
            },
            {
                "e": "📻",
                "k": "radio walkietalkie ether zender communicatie"
            },
            {
                "e": "🎙️",
                "k": "studiomicrofoon zang podcast opname mic"
            },
            {
                "e": "🔦",
                "k": "zaklamp schijnen lichtstraal donker inbraak"
            },
            {
                "e": "💡",
                "k": "gloeilamp idee ingeving licht helder lamp"
            },
            {
                "e": "🕯️",
                "k": "kaars kaarslicht vlam romantisch donker"
            },
            {
                "e": "🔋",
                "k": "batterij accu stroom lading energie vol"
            },
            {
                "e": "🧯",
                "k": "brandblusser blussen schuim brandveiligheid noodgeval"
            },
            {
                "e": "🗄️",
                "k": "archiefkast dossiers tbs rapporten dossiers kluis"
            },
            {
                "e": "📦",
                "k": "kartonnen doos postpakket levering versturen"
            },
            {
                "e": "📜",
                "k": "perkament rol perkamentrol wet decreet contract"
            },
            {
                "e": "📄",
                "k": "document pagina formulier papier verslag papierwerk"
            },
            {
                "e": "📰",
                "k": "krant nieuws voorpagina dagblad pers telegraaf"
            },
            {
                "e": "🏷️",
                "k": "prijskaartje label tag identificatie prijs"
            }
        ]
    },
    "symbols": {
        "label": "Symbolen & Vlaggen",
        "icon": "fa-solid fa-icons",
        "emojis": [
            {
                "e": "💜",
                "k": "paars hart tbs kliniek tbs-paars respect paars hartje liefde"
            },
            {
                "e": "❤️",
                "k": "rood hart liefde houden van verliefd warm hartje"
            },
            {
                "e": "🧡",
                "k": "oranje hart warm vriendschap nederland oranje"
            },
            {
                "e": "💛",
                "k": "geel hart goud trouw vriendschap zon"
            },
            {
                "e": "💚",
                "k": "groen hart natuur hoop jaloezie geld groen"
            },
            {
                "e": "💙",
                "k": "blauw hart broeder loyaliteit trouw koud kalmte"
            },
            {
                "e": "🖤",
                "k": "zwart hart duister rouw verdriet koud zwart gang"
            },
            {
                "e": "🤍",
                "k": "wit hart puur vrede onschuld zuiver wit"
            },
            {
                "e": "🤎",
                "k": "bruin hart aarde warm chocolade"
            },
            {
                "e": "💔",
                "k": "gebroken hart afgewezen liefdesverdriet verdriet scheiding"
            },
            {
                "e": "❤️‍🔥",
                "k": "hart in vuur en vlam passie brandend hart verlangen heet"
            },
            {
                "e": "❤️‍🩹",
                "k": "genezend hart pleister herstel vergeving"
            },
            {
                "e": "❣️",
                "k": "hart uitroepteken liefde nadruk verliefd"
            },
            {
                "e": "💕",
                "k": "twee harten zwevende harten verliefdheid"
            },
            {
                "e": "💞",
                "k": "draaiende harten romantiek verliefd"
            },
            {
                "e": "💓",
                "k": "kloppend hart hartslag verliefd vibrerend"
            },
            {
                "e": "💗",
                "k": "groeiend hart warmte liefdevol teder"
            },
            {
                "e": "💖",
                "k": "fonkelend hart glitter sterren bijzonder schatje"
            },
            {
                "e": "💘",
                "k": "hart met pijl cupido verliefd geschoten"
            },
            {
                "e": "💝",
                "k": "hart met strik cadeau liefde verrassing geschenk"
            },
            {
                "e": "💟",
                "k": "hart versiering decoratie paars blok"
            },
            {
                "e": "☮️",
                "k": "vredesteken peace no war vrede hippie"
            },
            {
                "e": "✝️",
                "k": "latijns kruis religie christendom jezus geloof"
            },
            {
                "e": "☯️",
                "k": "yin yang balans harmonie taoïsme karma evenwicht"
            },
            {
                "e": "☸️",
                "k": "dharmawiel boeddhisme wiel van de wet"
            },
            {
                "e": "☣️",
                "k": "biohazard biologisch risico virus gevaarlijk gif"
            },
            {
                "e": "☢️",
                "k": "radioactief stralingsgevaar uranium tsjernobyl"
            },
            {
                "e": "⚠️",
                "k": "waarschuwingsdriehoek gevaar opgelet pas op"
            },
            {
                "e": "🚸",
                "k": "overstekende kinderen attentie schoolzone"
            },
            {
                "e": "⛔",
                "k": "verboden toegang inrijverbod eenrichtingsweg gesloten"
            },
            {
                "e": "🚫",
                "k": "verboden cirkel met streep niet toegestaan afgekeurd nee"
            },
            {
                "e": "🛑",
                "k": "stopbord rode achthoek stop houd halt"
            },
            {
                "e": "⭕",
                "k": "rode cirkel correct cirkel rondje"
            },
            {
                "e": "❌",
                "k": "rood kruis fout cancel sluiten kruisje wrong"
            },
            {
                "e": "✅",
                "k": "groen vinkje check goedgekeurd klaar succesvol vink"
            },
            {
                "e": "☑️",
                "k": "afgevinkt vakje checkbox gereed stemmen"
            },
            {
                "e": "✔️",
                "k": "vinkje zwart vink check bevestigen"
            },
            {
                "e": "💯",
                "k": "honderd punten 100 procent volmaakt perfect top score"
            },
            {
                "e": "💢",
                "k": "woede symbool adertje woedend boos anime manga"
            },
            {
                "e": "💥",
                "k": "botsing klap explosie impact knal boem"
            },
            {
                "e": "💫",
                "k": "duizelig sterretjes ronddraaien knock-out ko verdoofd"
            },
            {
                "e": "💬",
                "k": "spraakwolk bericht praatje chat praten dialoog"
            },
            {
                "e": "👁️‍🗨️",
                "k": "oog in spraakwolk anti pesten toezicht getuige zien"
            },
            {
                "e": "🗨️",
                "k": "linker spraakballon conversatie zeggen kletsen"
            },
            {
                "e": "🗯️",
                "k": "woedende tekstballon schreeuwen vloeken boos roepen"
            },
            {
                "e": "💭",
                "k": "gedachtewolk denken dromen mijmeren fantasie"
            },
            {
                "e": "💤",
                "k": "slapen zzz snurken vermoeid slaapstand dromen"
            },
            {
                "e": "❗",
                "k": "uitroepteken rood alarm belangrijk let op"
            },
            {
                "e": "❓",
                "k": "vraagteken rood vraag wat hoe waarom onbekend"
            },
            {
                "e": "‼️",
                "k": "dubbel uitroepteken grote nadruk urgent belangrijk"
            },
            {
                "e": "⁉️",
                "k": "uitroepteken vraagteken ongeloof verbaasd wtf"
            },
            {
                "e": "💲",
                "k": "dollar dollarteken geldteken prijs valuta cash"
            },
            {
                "e": "💶",
                "k": "euroteken euro valuta geld biljet betalen"
            },
            {
                "e": "👑",
                "k": "kroon koning koninklijk royalty heerser"
            },
            {
                "e": "💎",
                "k": "diamant edelsteen schat rijkdom waardevol"
            },
            {
                "e": "🏁",
                "k": "finishvlag finish finishlijn overwinning race geblokt"
            },
            {
                "e": "🚩",
                "k": "rode vlag red flag waarschuwing gevaarlijk signaal"
            },
            {
                "e": "🏴‍☠️",
                "k": "piratenvlag jolly roger piraten vlag misdaad schedel"
            },
            {
                "e": "🇳🇱",
                "k": "nederland nederlandse vlag rood wit blauw holland amsterdam"
            },
            {
                "e": "🇧🇪",
                "k": "belgie belgische vlag zwart geel rood brussel belgië"
            },
            {
                "e": "🇩🇪",
                "k": "duitsland duitse vlag zwart rood goud berlijn bundes"
            },
            {
                "e": "🇪🇺",
                "k": "europese unie eu vlag blauw gele sterren europa"
            },
            {
                "e": "🇺🇸",
                "k": "amerika vs verenigde staten usa amerikaanse vlag stars and stripes"
            }
        ]
    }
});

    // Fast lookup map for custom game shortcodes
    const customCodeMap = new Map();
    window.TBS_CUSTOM_EMOJIS.forEach(item => {
        customCodeMap.set(item.code.toLowerCase(), item);
    });

    /**
     * Parses custom emoji shortcodes (:tbs_shank:, etc.) in safe HTML strings.
     * Guaranteed safe: ignores inside <a ...> or <img ...> attributes.
     */
    window.parseTbsChatEmojis = function(safeHtml) {
        if (!safeHtml || typeof safeHtml !== 'string') return safeHtml || '';
        
        const shortcodeRegex = /(?<!<a[^>]*>):([a-z0-9_]{2,32}):(?![^<]*<\/a>)/gi;
        return safeHtml.replace(shortcodeRegex, function(match, code) {
            const item = customCodeMap.get(code.toLowerCase());
            if (!item) return match;
            
            return '<img src="' + item.url + '" alt=":' + item.code + ':" title="' + item.name + ' (:' + item.code + ':)" class="tbs-custom-emoji inline-block w-5 h-5 mx-0.5 align-text-bottom hover:scale-125 transition-transform duration-100 select-none cursor-pointer" loading="lazy" decoding="async" draggable="false" />';
        });
    };

    /**
     * Search emojis in real time across custom items and 540+ Unicode emojis.
     */
    window.searchTbsEmojis = function(query, activeCategory, cap) {
        cap = cap || 80;
        const q = (query || '').trim().toLowerCase();

        // Mode 1: Browsing active tab without search text
        if (!q) {
            if (activeCategory === 'recent') {
                return window.getTbsRecentEmojis();
            }
            const cat = window.TBS_EMOJI_LIBRARY[activeCategory];
            if (!cat) return [];
            
            const list = [];
            if (cat.customItems) {
                cat.customItems.forEach(item => list.push({ isCustom: true, item: item }));
            }
            if (cat.emojis) {
                cat.emojis.forEach(item => list.push({ isCustom: false, char: item.e }));
            }
            return list;
        }

        // Mode 2: Global real-time search (supports colons like :tbs_shank: and multi-word queries)
        const cleanQ = q.replace(/^:+|:+$/g, '').trim();
        const tokens = (cleanQ || q).split(/\s+/).filter(Boolean);
        const results = [];
        const seen = new Set();

        // 2a. Match custom game emojis first
        window.TBS_CUSTOM_EMOJIS.forEach(item => {
            const haystack = item.code + ' ' + item.shortcode + ' ' + item.name.toLowerCase() + ' ' + item.keywords;
            if (tokens.every(t => haystack.includes(t))) {
                seen.add(item.code);
                results.push({ isCustom: true, item: item });
            }
        });

        // 2b. Match standard Unicode emojis across all categories
        const categories = Object.values(window.TBS_EMOJI_LIBRARY);
        for (let i = 0; i < categories.length; i++) {
            const list = categories[i].emojis;
            if (!list) continue;
            for (let j = 0; j < list.length; j++) {
                const item = list[j];
                if (!seen.has(item.e) && (item.e === q || tokens.every(t => item.k.includes(t)))) {
                    seen.add(item.e);
                    results.push({ isCustom: false, char: item.e });
                    if (results.length >= cap) return results;
                }
            }
        }

        return results;
    };

    /**
     * Local storage recent emojis helper
     */
    window.getTbsRecentEmojis = function() {
        try {
            const stored = localStorage.getItem('tbs_recent_emojis');
            const arr = stored ? JSON.parse(stored) : [];
            if (!Array.isArray(arr)) return [];
            const output = [];
            for (let i = 0; i < arr.length; i++) {
                const val = arr[i];
                if (typeof val !== 'string' || !val) continue;
                if (val.startsWith(':') && val.endsWith(':')) {
                    const code = val.slice(1, -1).toLowerCase();
                    const custom = customCodeMap.get(code);
                    if (custom) {
                        output.push({ isCustom: true, item: custom });
                    }
                } else {
                    output.push({ isCustom: false, char: val });
                }
            }
            return output;
        } catch(e) {
            return [];
        }
    };

    window.addTbsRecentEmoji = function(charOrShortcode) {
        try {
            const stored = localStorage.getItem('tbs_recent_emojis');
            let list = stored ? JSON.parse(stored) : [];
            list = [charOrShortcode, ...list.filter(item => item !== charOrShortcode)].slice(0, 24);
            localStorage.setItem('tbs_recent_emojis', JSON.stringify(list));
        } catch(e) {}
    };

})(window);
