<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminModerationController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\ArmWrestleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BountyController;
use App\Http\Controllers\CasinoController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CinemaController;
use App\Http\Controllers\CombatTrainingController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ConnectFourController;
use App\Http\Controllers\ControlPointController;
use App\Http\Controllers\CooldownController;
use App\Http\Controllers\CpaleadPostbackController;
use App\Http\Controllers\DailyBattleController;
use App\Http\Controllers\DailyRewardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DaycareController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\DiscordShieldAdminController;
use App\Http\Controllers\DiscordShieldController;
use App\Http\Controllers\DroppingController;
use App\Http\Controllers\ExtortController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FightController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\GangController;
use App\Http\Controllers\GangPropertyController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\HackingController;
use App\Http\Controllers\HalloweenController;
use App\Http\Controllers\HeistController;
use App\Http\Controllers\HighLowController;
use App\Http\Controllers\HitmanController;
use App\Http\Controllers\HustleController;
use App\Http\Controllers\InfirmaryController;
use App\Http\Controllers\InformantController;
use App\Http\Controllers\IsolationController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LockerController;
use App\Http\Controllers\LotteryController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MurderStatsController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewspaperController;
use App\Http\Controllers\NumberGameController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PerkController;
use App\Http\Controllers\PickpocketController;
use App\Http\Controllers\PrisonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\RouletteController;
use App\Http\Controllers\RpsController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SlotsController;
use App\Http\Controllers\SmuggleController;
use App\Http\Controllers\SponsorAdController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TheftController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\TrueCrimeController;
use App\Http\Controllers\TunnelController;
use App\Http\Controllers\VaultController;
use App\Http\Controllers\VipController;
use App\Http\Controllers\WarningController;
use App\Http\Middleware\EnsureUserIsStaff;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/registreren', [AuthController::class, 'showLogin'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
    Route::get('/auth/discord', [AuthController::class, 'discordLogin'])->name('login.discord');
    Route::get('/auth/discord/callback', [AuthController::class, 'discordCallback'])->name('login.discord.callback');

    // Wachtwoord Vergeten (E-mail)
    Route::get('/wachtwoord-vergeten', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/wachtwoord-vergeten', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/wachtwoord-reset/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/wachtwoord-reset', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

// Publieke Juridische Routes (AVG, Cookiewet, Voorwaarden)
Route::get('/algemene-voorwaarden', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/voorwaarden', [LegalController::class, 'terms']);
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/privacybeleid', [LegalController::class, 'privacy']);
Route::get('/cookies', [LegalController::class, 'cookies'])->name('legal.cookies');
Route::get('/cookiebeleid', [LegalController::class, 'cookies']);
Route::get('/disclaimer', [LegalController::class, 'disclaimer'])->name('legal.disclaimer');
Route::get('/colofon', [LegalController::class, 'disclaimer']);

// Publieke Kennisbank & FAQ (Toegankelijk voor gasten en zoekmachines)
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
Route::get('/kennisbank', [FaqController::class, 'index'])->name('faq');

// Publieke Sponsor Redirect (Bypasses browser adblockers)
Route::get('/sponsorkamer/redirect', [SponsorAdController::class, 'redirect'])->name('sponsor.redirect');

// CPAlead Publisher Postback Callback (Server-to-Server GET)
Route::get('/api/cpalead/postback', [CpaleadPostbackController::class, 'handle'])->name('cpalead.postback');

// Discord Shield Verificatie & Spelers Account Koppeling
Route::get('/discord/verifieer', [DiscordShieldController::class, 'show'])->name('discord.verify');
Route::get('/discord/login', [DiscordShieldController::class, 'redirect'])->name('discord.redirect');
Route::get('/discord/callback', [DiscordShieldController::class, 'callback'])->name('discord.callback');
Route::post('/discord/verifieer/submit', [DiscordShieldController::class, 'process'])->name('discord.process');
Route::match(['GET', 'POST'], '/discord/koppel', [DiscordShieldController::class, 'linkGameAccount'])->name('discord.link');
Route::match(['GET', 'POST'], '/discord/sync-roles', [DiscordShieldController::class, 'syncRoles'])->name('discord.sync_roles');
Route::get('/discord/widget-data', [DiscordShieldController::class, 'widgetData'])->name('discord.widget_data');

// Community Hub & Social Follow Rewards
Route::get('/community', [CommunityController::class, 'index'])->name('community.index');

// Authenticated Game Routes
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Community Follow Reward Claim
    Route::post('/community/claim/{platform}', [CommunityController::class, 'claim'])->name('community.claim');

    // Onboarding Wizard (Nieuwe spelers & Discord)
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::match(['GET', 'POST'], '/onboarding/check-username', [OnboardingController::class, 'checkUsername'])->name('onboarding.check-username');
    Route::post('/onboarding/upload-avatar', [OnboardingController::class, 'uploadAvatar'])->name('onboarding.upload-avatar');
    Route::post('/onboarding/voltooien', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    // Sponsorkamer & Rewarded Ads
    Route::get('/sponsorkamer', [SponsorAdController::class, 'index'])->name('sponsor.index');
    Route::post('/sponsorkamer/start', [SponsorAdController::class, 'start'])->name('sponsor.start');
    Route::get('/sponsorkamer/status', [SponsorAdController::class, 'status'])->name('sponsor.status');
    Route::post('/sponsorkamer/claim', [SponsorAdController::class, 'claim'])->name('sponsor.claim');

    // E-mail Bevestiging (Code & Fallback Link)
    Route::get('/email/verify', [AuthController::class, 'verifyNotice'])->name('verification.notice');
    Route::post('/email/verify/code', [AuthController::class, 'verifyWithCode'])->middleware('throttle:5,1')->name('verification.code');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->middleware('throttle:3,1')->name('verification.send');

    // Helptool: Support Ticketsysteem
    Route::get('/help/tickets', [SupportTicketController::class, 'index'])->name('help.tickets.index');
    Route::get('/help/tickets/nieuw', [SupportTicketController::class, 'create'])->name('help.tickets.create');
    Route::post('/help/tickets', [SupportTicketController::class, 'store'])->name('help.tickets.store');
    Route::get('/help/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('help.tickets.show');
    Route::post('/help/tickets/{ticket}/reactie', [SupportTicketController::class, 'reply'])->name('help.tickets.reply');
    Route::post('/help/tickets/{ticket}/sluiten', [SupportTicketController::class, 'close'])->name('help.tickets.close');

    // Dashboard & Widgets
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/widgets/ontgrendelen', [DashboardController::class, 'unlockWidget'])->middleware('throttle:30,1')->name('dashboard.widgets.unlock');
    Route::get('/dashboard/weer-locatie', [DashboardController::class, 'weatherByCoords'])->middleware('throttle:30,1')->name('dashboard.weather.coords');
    Route::post('/dashboard/peuken/verkopen', [DashboardController::class, 'sellPeuken'])->name('dashboard.peuken.sell');
    Route::post('/dashboard/radar/sweep', [DashboardController::class, 'radarSweep'])->name('dashboard.radar.sweep');

    // Halloween Event 🎃 (Quests & Shop) - Active until 5 November
    Route::get('/event/quests', [HalloweenController::class, 'quests'])->name('halloween.quests');
    Route::post('/event/quests/{questKey}/claim', [HalloweenController::class, 'claimQuest'])->name('halloween.claim-quest');
    Route::get('/event/shop', [HalloweenController::class, 'shop'])->name('halloween.shop');
    Route::post('/event/shop/buy', [HalloweenController::class, 'buy'])->name('halloween.buy');
    Route::post('/event/shop/equip-border', [HalloweenController::class, 'equipBorder'])->name('halloween.equip-border');

    // Live Openbare Chat & Privé Messenger (Nieuw)
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/openbaar/berichten', [ChatController::class, 'publicMessages'])->name('chat.public.messages');
    Route::post('/chat/openbaar/verzenden', [ChatController::class, 'sendPublic'])->middleware('throttle:30,1')->name('chat.public.send');
    Route::post('/chat/openbaar/{id}/hartje', [ChatController::class, 'togglePublicHeart'])->name('chat.public.heart');
    Route::delete('/chat/openbaar/{id}', [ChatController::class, 'deletePublicMessage'])->name('chat.public.delete');
    Route::get('/chat/zoek-spelers', [ChatController::class, 'searchPlayers'])->name('chat.search-players');
    Route::get('/chat/gesprekken', [ChatController::class, 'conversations'])->name('chat.conversations');
    Route::get('/chat/prive/{partnerId}', [ChatController::class, 'directMessages'])->name('chat.direct.messages');
    Route::post('/chat/prive/{partnerId}/verzenden', [ChatController::class, 'sendDirect'])->middleware('throttle:30,1')->name('chat.direct.send');
    Route::post('/chat/prive-bericht/{id}/hartje', [ChatController::class, 'toggleDirectHeart'])->name('chat.direct.heart');
    Route::delete('/chat/prive-bericht/{id}', [ChatController::class, 'deleteDirectMessage'])->name('chat.direct.delete');
    Route::get('/chat/ongelezen-teller', [ChatController::class, 'unreadCount'])->name('chat.unread-count');
    Route::get('/chat/poll-inkomend', [ChatController::class, 'pollIncoming'])->name('chat.poll-incoming');

    // Meldingen & Rapporteren (Speler, Bende of Chat)
    Route::post('/rapporteren', [ReportController::class, 'store'])->name('report.store');

    // Berichten (Messages & Post)
    Route::get('/berichten', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/berichten/versturen', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/berichten/alles-lezen', [MessageController::class, 'markAllRead'])->name('messages.mark-all-read');
    Route::post('/berichten/{message}/lezen', [MessageController::class, 'markRead'])->name('messages.mark-read');
    Route::delete('/berichten/alles-verwijderen', [MessageController::class, 'destroyAll'])->name('messages.destroy-all');
    Route::delete('/berichten/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // Vrienden (Friends & BFF)
    Route::get('/vrienden', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/vrienden/toevoegen', [FriendController::class, 'add'])->name('friends.add');
    Route::post('/vrienden/{friend}/bff', [FriendController::class, 'toggleBff'])->name('friends.toggle-bff');
    Route::post('/vrienden/{friend}/accepteren', [FriendController::class, 'accept'])->name('friends.accept');
    Route::post('/vrienden/{friend}/weigeren', [FriendController::class, 'reject'])->name('friends.reject');
    Route::delete('/vrienden/{friend}', [FriendController::class, 'remove'])->name('friends.remove');
    Route::post('/vrienden/{friend}/respect', [FriendController::class, 'giveRespect'])->name('friends.respect');
    Route::post('/vrienden/massa-bericht', [FriendController::class, 'massMessage'])->name('friends.mass-message');

    // Tijdlijn (Twitter van de TBS Kliniek)
    Route::get('/tijdlijn', [TimelineController::class, 'index'])->name('timeline.index');
    Route::post('/tijdlijn/plaatsen', [TimelineController::class, 'store'])->name('timeline.store');
    Route::post('/tijdlijn/{post}/like', [TimelineController::class, 'like'])->name('timeline.like');
    Route::post('/tijdlijn/{post}/reageren', [TimelineController::class, 'comment'])->name('timeline.comment');
    Route::delete('/tijdlijn/reactie/{comment}', [TimelineController::class, 'destroyComment'])->name('timeline.comment.destroy');
    Route::delete('/tijdlijn/{post}', [TimelineController::class, 'destroy'])->name('timeline.destroy');

    // Missions
    Route::get('/missies', [MissionController::class, 'index'])->name('missions.index');
    Route::post('/missies/{mission}/uitvoeren', [MissionController::class, 'execute'])->name('missions.execute');

    // Training / Gym
    Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
    Route::post('/training', [TrainingController::class, 'train'])->name('training.train');

    // Behandelaarsgesprek & Verlofregeling (Dr. Visser & Yvonne)
    Route::get('/behandeling', [TreatmentController::class, 'index'])->name('treatment.index');
    Route::post('/behandeling/gesprek', [TreatmentController::class, 'submit'])->name('treatment.submit');
    Route::post('/behandeling/verlof-missie', [TreatmentController::class, 'doLeaveAction'])->name('treatment.leave-action');

    // Ziekenboeg & Pillen Palmen (Functie 1)
    Route::get('/ziekenboeg', [InfirmaryController::class, 'index'])->name('infirmary.index');
    Route::post('/ziekenboeg/innemen', [InfirmaryController::class, 'take'])->name('infirmary.take');
    Route::post('/ziekenboeg/palmen', [InfirmaryController::class, 'palm'])->name('infirmary.palm');

    // Dagbesteding & Werkplaats (Functie 6)
    Route::get('/dagbesteding', [DaycareController::class, 'index'])->name('daycare.index');
    Route::post('/dagbesteding/uitvoeren', [DaycareController::class, 'perform'])->name('daycare.perform');

    // Het Afdelingsblad - De Kliniekbode (Functie 4)
    Route::get('/afdelingsblad', [NewspaperController::class, 'index'])->name('newspaper.index');

    // Cooldown Skip (Sterren ⭐)
    Route::post('/cooldown/overslaan', [CooldownController::class, 'skip'])->name('cooldown.skip');

    // Crime & Theft
    Route::get('/diefstal', [TheftController::class, 'index'])->name('theft.index');
    Route::post('/diefstal', [TheftController::class, 'steal'])->middleware('throttle:60,1')->name('theft.steal');

    // Hustle (Hosselen)
    Route::get('/hosselen', [HustleController::class, 'index'])->name('hustle.index');
    Route::post('/hosselen/actie', [HustleController::class, 'attempt'])->name('hustle.attempt');

    // Smuggle (Dealen/Produceren)
    Route::get('/smokkelen', [SmuggleController::class, 'index'])->name('smuggle.index');
    Route::post('/smokkelen/produceren', [SmuggleController::class, 'produce'])->name('smuggle.produce');
    Route::post('/smokkelen/verkopen', [SmuggleController::class, 'sell'])->name('smuggle.sell');
    Route::post('/smokkelen/weigeren', [SmuggleController::class, 'reject'])->name('smuggle.reject');
    Route::post('/smokkelen/cooldown-overslaan', [CooldownController::class, 'skip'])->name('smuggle.skip-cooldown');

    // Functie 2: Tennisbal & Drone Droppings
    Route::get('/smokkel-droppings', [DroppingController::class, 'index'])->name('droppings.index');
    Route::post('/smokkel-droppings/tennisbal', [DroppingController::class, 'catchTennisball'])->name('droppings.tennisball');
    Route::post('/smokkel-droppings/drone', [DroppingController::class, 'orderDrone'])->name('droppings.drone');

    // Functie 3: TBS FM (Radio & Shoutouts)
    Route::get('/tbs-fm', [RadioController::class, 'index'])->name('radio.index');
    Route::post('/tbs-fm/shoutout', [RadioController::class, 'shoutout'])->name('radio.shoutout');
    Route::post('/tbs-fm/{shoutout}/like', [RadioController::class, 'like'])->name('radio.like');

    // Functie 7: Het Grote Uitbraakcomplot (Tunnel)
    Route::get('/ontsnappingstunnel', [TunnelController::class, 'index'])->name('tunnel.index');
    Route::post('/ontsnappingstunnel/graven', [TunnelController::class, 'dig'])->name('tunnel.dig');
    Route::post('/ontsnappingstunnel/zand-dumpen', [TunnelController::class, 'dumpSand'])->name('tunnel.dump');
    Route::post('/ontsnappingstunnel/uitbreken', [TunnelController::class, 'escape'])->name('tunnel.escape');

    // Grote Heists & Kraak Missies
    Route::get('/heists', [HeistController::class, 'index'])->name('heists.index');
    Route::get('/heist', fn () => redirect()->route('heists.index'));
    Route::get('/heists/gang', fn () => redirect()->route('heists.index', ['tab' => 'gang']));
    Route::get('/bende/heist', fn () => redirect()->route('heists.index', ['tab' => 'gang']));
    Route::get('/bende/heists', fn () => redirect()->route('heists.index', ['tab' => 'gang']));
    Route::get('/bende-heist', fn () => redirect()->route('heists.index', ['tab' => 'gang']));
    Route::get('/bende-heists', fn () => redirect()->route('heists.index', ['tab' => 'gang']));
    Route::get('/gang/heists', fn () => redirect()->route('heists.index', ['tab' => 'gang']));
    Route::get('/gangs/heists', fn () => redirect()->route('heists.index', ['tab' => 'gang']));
    Route::get('/heists/start/{heist}', fn () => redirect()->route('heists.index', ['tab' => 'gang']));
    Route::post('/heists/start/{heist}', [HeistController::class, 'launch'])->name('heists.launch');

    // Extort (Afpersen)
    Route::get('/afpersen', [ExtortController::class, 'index'])->name('extort.index');
    Route::post('/afpersen/uitvoeren', [ExtortController::class, 'extort'])->name('extort.extort')->middleware('throttle:30,1');

    // Pickpocket (Zakkenrollen)
    Route::get('/zakkenrollen', [PickpocketController::class, 'index'])->name('pickpocket.index');
    Route::post('/zakkenrollen/stelen', [PickpocketController::class, 'steal'])->name('pickpocket.steal')->middleware('throttle:30,1');

    // Lockers (Kluisjes)
    Route::get('/kluisjes', [LockerController::class, 'index'])->name('lockers.index');
    Route::post('/kluisjes/kraken', [LockerController::class, 'crack'])->name('lockers.crack')->middleware('throttle:30,1');

    // Packages (Pakketjes)
    Route::get('/pakketjes', [PackageController::class, 'index'])->name('packages.index');
    Route::post('/pakketjes/onderscheppen', [PackageController::class, 'intercept'])->name('packages.intercept')->middleware('throttle:30,1');
    Route::post('/pakketjes/tas-openen', [PackageController::class, 'openBag'])->name('packages.open-bag')->middleware('throttle:30,1');
    Route::post('/pakketjes/tas-verkopen', [PackageController::class, 'sellBag'])->name('packages.sell-bag')->middleware('throttle:30,1');
    Route::post('/pakketjes/xxl-openen', [PackageController::class, 'openXxl'])->name('packages.open-xxl')->middleware('throttle:30,1');
    Route::post('/pakketjes/xxl-kopen', [PackageController::class, 'buyXxl'])->name('packages.buy-xxl')->middleware('throttle:30,1');

    // 24 Uur Battle (Dagelijkse Battle)
    Route::get('/24-uur-battle', [DailyBattleController::class, 'index'])->name('battle.index');
    Route::get('/24-uur-battle/categorie/{category}', [DailyBattleController::class, 'categoryDetails'])->name('battle.category');

    // Rad van Fortuin & Dagelijkse Beloningen
    Route::get('/rad-van-fortuin', [DailyRewardController::class, 'index'])->name('daily.index');
    Route::post('/rad-van-fortuin/spin', [DailyRewardController::class, 'spin'])->name('daily.spin')->middleware('throttle:30,1');
    Route::post('/rad-van-fortuin/streak', [DailyRewardController::class, 'claimStreak'])->name('daily.claim-streak')->middleware('throttle:30,1');

    // Cyber Terminal Hacking Minigame
    Route::get('/hacking', [HackingController::class, 'index'])->name('hacking.index');
    Route::post('/hacking/puzzle', [HackingController::class, 'solvePuzzle'])->name('hacking.puzzle')->middleware('throttle:30,1');
    Route::post('/hacking/spin', [HackingController::class, 'spin'])->name('hacking.spin')->middleware('throttle:30,1');

    // Vault (Kluis Kraak & Codes)
    Route::get('/kluis-kraak', [VaultController::class, 'index'])->name('vault.index');
    Route::post('/kluis-kraak/raden', [VaultController::class, 'guess'])->name('vault.guess')->middleware('throttle:30,1');
    Route::post('/kluis-kraak/koop-code', [VaultController::class, 'buyCode'])->name('vault.buy-code')->middleware('throttle:30,1');
    Route::post('/kluis-kraak/koop-hint', [VaultController::class, 'buyHint'])->name('vault.buy-hint')->middleware('throttle:30,1');
    Route::post('/kluis-kraak/koop-jammer', [VaultController::class, 'buyJammer'])->name('vault.buy-jammer')->middleware('throttle:30,1');
    Route::post('/kluis-kraak/inwisselen', [VaultController::class, 'redeemCode'])->name('vault.redeem')->middleware('throttle:30,1');
    Route::post('/kluis-kraak/code-verkopen', [VaultController::class, 'sellCode'])->name('vault.sell-code')->middleware('throttle:30,1');
    Route::post('/kluis-kraak/maskers-verkopen', [VaultController::class, 'sellMask'])->name('vault.sell-mask')->middleware('throttle:30,1');

    // VIP Punten Shop (Diamanten)
    Route::get('/vip-shop', [VipController::class, 'index'])->name('vip.index');
    Route::post('/vip-shop/inwisselen', [VipController::class, 'redeem'])->name('vip.redeem')->middleware('throttle:30,1');

    // Combat Training (Schietoefening)
    Route::get('/vechtoefening', [CombatTrainingController::class, 'index'])->name('combat_training.index');
    Route::post('/vechtoefening/trainen', [CombatTrainingController::class, 'train'])->name('combat_training.train');

    // Hitman (Liquidatie)
    Route::get('/liquidatie', [HitmanController::class, 'index'])->name('hitman.index');
    Route::post('/liquidatie/contract', [HitmanController::class, 'contract'])->name('hitman.contract');

    // Kopgeld & Opsporingsbord (Bounty Hunter)
    Route::get('/kopgeld', [BountyController::class, 'index'])->name('bounties.index');
    Route::post('/kopgeld/plaatsen', [BountyController::class, 'store'])->name('bounties.store');

    // Informants (Spionnenleger)
    Route::get('/informanten', [InformantController::class, 'index'])->name('informants.index');
    Route::post('/informanten/inhuren', [InformantController::class, 'hire'])->name('informants.hire');

    // Isolation (Vrijwillige Isolatie)
    Route::get('/vrijwillige-isolatie', [IsolationController::class, 'index'])->name('isolation.index');
    Route::post('/vrijwillige-isolatie/activeren', [IsolationController::class, 'activate'])->name('isolation.activate');

    // Murder Stats (Moordstatistieken)
    Route::get('/geweldsstatistieken', [MurderStatsController::class, 'index'])->name('murder_stats.index');

    // RPS (Steen Papier Schaar)
    Route::get('/casino/steen-papier-schaar', [RpsController::class, 'index'])->name('rps.index');
    Route::post('/casino/steen-papier-schaar/spelen', [RpsController::class, 'play'])->middleware('throttle:30,1')->name('rps.play');

    // High/Low (Hoger & Lager)
    Route::get('/casino/hoger-lager', [HighLowController::class, 'index'])->name('highlow.index');
    Route::post('/casino/hoger-lager/spelen', [HighLowController::class, 'play'])->middleware('throttle:30,1')->name('highlow.play');

    // Number Game (Nummerspel)
    Route::get('/casino/nummerspel', [NumberGameController::class, 'index'])->name('numbergame.index');
    Route::post('/casino/nummerspel/spelen', [NumberGameController::class, 'play'])->middleware('throttle:30,1')->name('numbergame.play');

    // Roulette
    Route::get('/casino/roulette', [RouletteController::class, 'index'])->name('roulette.index');
    Route::post('/casino/roulette/spelen', [RouletteController::class, 'play'])->middleware('throttle:30,1')->name('roulette.play');

    // Slots (Fruitmachine)
    Route::get('/casino/fruitautomaat', [SlotsController::class, 'index'])->name('slots.index');
    Route::post('/casino/fruitautomaat/draaien', [SlotsController::class, 'spin'])->middleware('throttle:30,1')->name('slots.spin');

    // TBS Afdelingsloterij (Jackpot & Belastingpot)
    Route::get('/loterij', [LotteryController::class, 'index'])->name('lottery.index');
    Route::post('/loterij/kopen', [LotteryController::class, 'buyTickets'])->name('lottery.buy');

    // Realtime Character Stats API
    Route::get('/api/character/realtime-stats', [DashboardController::class, 'realtimeStats'])->name('character.realtime-stats');

    // Travel (Reizen / Verplaatsen)
    Route::get('/essentials/verplaatsen', [TravelController::class, 'index'])->name('travel.index');
    Route::post('/essentials/verplaatsen', [TravelController::class, 'move'])->name('travel.move');

    // PvP (Luchtplaats)
    Route::get('/luchtplaats', [FightController::class, 'index'])->name('fight.index');
    Route::get('/arena', fn () => redirect()->route('fight.index'))->name('arena');
    Route::get('/luchtplaats/arena/{defender}', [FightController::class, 'arena'])->name('fight.arena');
    Route::post('/luchtplaats/attack', [FightController::class, 'attack'])->middleware('throttle:60,1')->name('fight.attack');

    // Bank & Financieel Centrum
    Route::get('/bank', [BankController::class, 'index'])->name('bank.index');
    Route::post('/bank/storten', [BankController::class, 'deposit'])->name('bank.deposit');
    Route::post('/bank/opnemen', [BankController::class, 'withdraw'])->name('bank.withdraw');
    Route::post('/bank/leningen/aanvragen', [BankController::class, 'requestLoan'])->name('bank.loans.request');
    Route::post('/bank/leningen/{loan}/aflossen', [BankController::class, 'repayLoan'])->name('bank.loans.repay');
    Route::post('/bank/leningen/{loan}/alles-aflossen', [BankController::class, 'repayLoanFull'])->name('bank.loans.repay-full');

    // Casino
    Route::get('/casino', [CasinoController::class, 'index'])->name('casino.index');
    Route::post('/casino/play', [CasinoController::class, 'play'])->middleware('throttle:30,1')->name('casino.play');

    // Shop & Inventory
    Route::get('/winkel', [ShopController::class, 'index'])->name('shop.index');
    Route::post('/winkel/{item}/kopen', [ShopController::class, 'buy'])->name('shop.buy');
    Route::post('/winkel/sterren-kopen', [ShopController::class, 'buyStarItem'])->name('shop.buy-star-item');
    Route::post('/winkel/bundel-kopen', [ShopController::class, 'buyBundle'])->name('shop.buy-bundle');
    Route::post('/inventaris/{characterItem}/gebruiken', [ShopController::class, 'consume'])->name('shop.consume');

    // Permanente Karakter Perks (Sterren ⭐)
    Route::get('/perks', [PerkController::class, 'index'])->name('perks.index');
    Route::post('/perks/kopen/{perk}', [PerkController::class, 'buy'])->name('perks.buy');

    // Persoonlijk Depot (Garage, Opbergplaats & Event Varia)
    Route::get('/depot', [DepotController::class, 'index'])->name('depot.index');
    Route::post('/depot/verkopen/{characterItem}', [DepotController::class, 'sellItem'])->name('depot.sell-item');
    Route::post('/depot/maskers-verkopen', [DepotController::class, 'sellMasks'])->name('depot.sell-masks');

    // Cinema & Watchparties (YouTube, Live Session Chat, Emojis, GIFs, Video Queue)
    Route::get('/cinema', [CinemaController::class, 'index'])->name('cinema.index');
    Route::get('/cinema/search-youtube', [CinemaController::class, 'searchYoutube'])->name('cinema.search-youtube');
    Route::post('/cinema/rooms', [CinemaController::class, 'createRoom'])->name('cinema.create');
    Route::get('/cinema/{room:slug}', [CinemaController::class, 'show'])->name('cinema.show');
    Route::post('/cinema/{room:slug}/close', [CinemaController::class, 'closeRoom'])->name('cinema.close');
    Route::post('/cinema/{room}/chat', [CinemaController::class, 'sendMessage'])->name('cinema.send-message');
    Route::get('/cinema/{room}/messages', [CinemaController::class, 'getMessages'])->name('cinema.get-messages');
    Route::post('/cinema/{room:slug}/video', [CinemaController::class, 'updateVideo'])->name('cinema.update-video');
    Route::post('/cinema/{room:slug}/queue/add', [CinemaController::class, 'addToQueue'])->name('cinema.queue.add');
    Route::post('/cinema/{room:slug}/queue/play/{queueId}', [CinemaController::class, 'playFromQueue'])->name('cinema.queue.play');
    Route::post('/cinema/{room:slug}/queue/remove/{queueId}', [CinemaController::class, 'removeFromQueue'])->name('cinema.queue.remove');
    Route::post('/cinema/{room:slug}/sync', [CinemaController::class, 'syncPlayback'])->name('cinema.sync');

    // Smokkel Garage & Wagenpark
    Route::get('/garage', [GarageController::class, 'index'])->name('garage.index');
    Route::post('/garage/kopen/{vehicle}', [GarageController::class, 'buy'])->name('garage.buy');
    Route::post('/garage/actief/{characterVehicle}', [GarageController::class, 'setActive'])->name('garage.set-active');
    Route::post('/garage/repareren/{characterVehicle}', [GarageController::class, 'repair'])->name('garage.repair');

    // Prison / Isoleercel
    Route::get('/gevangenis', [PrisonController::class, 'index'])->name('prison');
    Route::post('/gevangenis/ontsnappen', [PrisonController::class, 'escape'])->name('prison.escape');
    Route::post('/gevangenis/uitkopen-sterren', [PrisonController::class, 'buyout'])->name('prison.buyout');
    Route::post('/gevangenis/bevrijd/{prisoner}', [PrisonController::class, 'breakout'])->name('prison.breakout');

    // Player Profile & Guestbook
    Route::get('/profiel', [ProfileController::class, 'show'])->name('profile.me');
    Route::get('/speler/{name}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/u/{name}', [ProfileController::class, 'show'])->name('profile');
    Route::post('/speler/{name}/respect', [ProfileController::class, 'giveRespect'])->name('profile.respect');
    Route::post('/speler/{name}/gastenboek', [ProfileController::class, 'postGuestbook'])->name('guestbook.post');
    Route::delete('/speler/gastenboek/{entry}', [ProfileController::class, 'destroyGuestbook'])->name('guestbook.destroy');
    Route::post('/profiel/bijwerken', [ProfileController::class, 'updateBio'])->name('profile.update');
    Route::post('/profiel/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::post('/profiel/titel', [ProfileController::class, 'updateTitle'])->name('profile.title');
    Route::post('/profiel/personaliseren', [ProfileController::class, 'customize'])->name('profile.customize');

    // Gangs
    Route::get('/bende/{gang}', [GangController::class, 'show'])->name('gangs.show');
    Route::get('/gangs/bekijken/{gang}', [GangController::class, 'show']);
    Route::get('/gangs', [GangController::class, 'index'])->name('gangs.index');
    Route::get('/gangs/depot', fn () => redirect()->route('gangs.index', ['tab' => 'depot']))->name('gangs.depot');
    Route::get('/gangs/leden', [GangController::class, 'leden'])->name('gangs.leden');
    Route::get('/gangs/beheer', [GangController::class, 'beheer'])->name('gangs.beheer');
    Route::get('/gangs/instellingen', [GangController::class, 'instellingen'])->name('gangs.instellingen');
    Route::post('/gangs/aanmaken', [GangController::class, 'create'])->name('gangs.create');
    Route::post('/gangs/{gang}/aansluiten', [GangController::class, 'join'])->name('gangs.join');
    Route::post('/gangs/{gang}/aanvragen', [GangController::class, 'requestJoin'])->name('gangs.request-join');
    Route::post('/gangs/verlaten', [GangController::class, 'leave'])->name('gangs.leave');
    Route::post('/gangs/doneren', [GangController::class, 'donate'])->name('gangs.donate');
    Route::post('/gangs/doneren/sterren', [GangController::class, 'donateStars'])->name('gangs.donate-stars');
    Route::post('/gangs/doneren/maskers', [GangController::class, 'donateMasks'])->name('gangs.donate-masks');
    Route::post('/gangs/doneren/item', [GangController::class, 'donateItem'])->name('gangs.donate-item');
    Route::post('/gangs/depot/maskers-verkopen', [GangController::class, 'sellGangMasks'])->name('gangs.sell-masks');
    Route::post('/gangs/depot/item-verkopen/{gangItem}', [GangController::class, 'sellGangItem'])->name('gangs.sell-item');
    Route::post('/gangs/winkel/kopen', [GangController::class, 'buyShopItem'])->name('gangs.shop-buy');
    Route::post('/gangs/instellingen', [GangController::class, 'updateSettings'])->name('gangs.settings');
    Route::post('/gangs/kick/{member}', [GangController::class, 'kick'])->name('gangs.kick');
    Route::post('/gangs/promote/{member}', [GangController::class, 'promote'])->name('gangs.promote');
    Route::post('/gangs/opnemen', [GangController::class, 'withdraw'])->name('gangs.withdraw');
    Route::post('/gangs/massa-bericht', [GangController::class, 'massMessage'])->name('gangs.mass-message');
    Route::post('/gangs/verzoeken/{requestRecord}/behandelen', [GangController::class, 'handleRequest'])->name('gangs.handle-request');
    Route::post('/gangs/bondgenoot', [GangController::class, 'addAlliance'])->name('gangs.add-alliance');
    Route::delete('/gangs/bondgenoot/{alliance}', [GangController::class, 'removeAlliance'])->name('gangs.remove-alliance');
    Route::post('/gangs/opheffen', [GangController::class, 'disband'])->name('gangs.disband');
    Route::post('/gangs/aanvallen', [GangController::class, 'attack'])->name('gangs.attack');
    Route::post('/gangs/claim-uurloon', [GangController::class, 'claimWage'])->name('gangs.claim-wage');

    // Bende Panden, Productie & NPC Rekruten
    Route::get('/gangs/panden', [GangPropertyController::class, 'index'])->name('gangs.panden');
    Route::post('/gangs/panden/kopen/{propertyKey}', [GangPropertyController::class, 'buy'])->name('gangs.panden.buy');
    Route::post('/gangs/panden/{property}/start-productie', [GangPropertyController::class, 'startProduction'])->name('gangs.panden.start-production');
    Route::post('/gangs/panden/{property}/claim-productie', [GangPropertyController::class, 'claimProduction'])->name('gangs.panden.claim-production');
    Route::post('/gangs/panden/hire-npc/{npcKey}', [GangPropertyController::class, 'hireNpc'])->name('gangs.panden.hire-npc');
    Route::post('/gangs/panden/{property}/assign-npc', [GangPropertyController::class, 'assignNpc'])->name('gangs.panden.assign-npc');

    // Bende Garage & Wagenpark
    Route::get('/gangs/garage', [GangPropertyController::class, 'garage'])->name('gangs.garage');
    Route::post('/gangs/garage/stallen/{characterVehicle}', [GangPropertyController::class, 'storeVehicle'])->name('gangs.garage.store-vehicle');
    Route::post('/gangs/garage/ophalen/{characterVehicle}', [GangPropertyController::class, 'retrieveVehicle'])->name('gangs.garage.retrieve-vehicle');

    // Controlepunten (King of the Hill - Verover en Verdien)
    Route::get('/controlepunten', fn () => redirect()->route('dashboard')->with('info', 'De centrale controlepunten-pagina is opgeheven. Controlepunten kun je nu uitsluitend direct op de locaties zelf (Luchtplaats, Kantine, Sportzaal, Depot en Medicijnkluis) opeisen of overnemen!'))->name('control-points.index');
    Route::post('/controlepunten/{point}/claim', [ControlPointController::class, 'capture'])->name('control-points.capture');

    // Marktplaats (Marketplace)
    Route::get('/marktplaats', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::post('/marktplaats', [MarketplaceController::class, 'store'])->name('marketplace.store');
    Route::post('/marktplaats/{listing}/kopen', [MarketplaceController::class, 'buy'])->name('marketplace.buy');
    Route::post('/marktplaats/{listing}/annuleren', [MarketplaceController::class, 'cancel'])->name('marketplace.cancel');

    // Leaderboard & Leden / Patiëntenlijst
    Route::get('/ranglijst', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::get('/leaderboard', fn () => redirect()->route('leaderboard.index'))->name('leaderboard');
    Route::get('/leden', [MembersController::class, 'index'])->name('members.index');
    Route::get('/online', fn () => redirect()->route('members.index', ['filter' => 'online']))->name('online.index');

    // Account & Beveiliging (2FA, Wachtwoord, Username, Remote Sessions)
    Route::get('/instellingen/account', [AccountSettingsController::class, 'index'])->name('settings.account');
    Route::post('/instellingen/wachtwoord', [AccountSettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/instellingen/gebruikersnaam', [AccountSettingsController::class, 'updateUsername'])->name('settings.username.update');
    Route::post('/instellingen/avatar', [AccountSettingsController::class, 'updateAvatar'])->name('settings.avatar.update');
    Route::post('/instellingen/2fa', [AccountSettingsController::class, 'toggleTwoFactor'])->name('settings.2fa.toggle');
    Route::post('/instellingen/sessies/uitloggen', [AccountSettingsController::class, 'logoutOtherDevices'])->name('settings.sessions.logout-others');

    // Kliniek Nieuws & Mededelingen
    Route::get('/nieuws', [NewsController::class, 'index'])->name('news.index');
    Route::get('/nieuws/{slug}', [NewsController::class, 'show'])->name('news.show');
    Route::post('/nieuws/{article}/reactie', [NewsController::class, 'comment'])->name('news.comment');
    Route::delete('/nieuws/reactie/{comment}', [NewsController::class, 'destroyComment'])->name('news.comment.destroy');

    // Toekomst & Roadmap (De 6 Nieuwe Features)
    Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap.index');
    Route::post('/roadmap/stem/{featureKey}', [RoadmapController::class, 'vote'])->name('roadmap.vote');

    // De Illegale Armpjedruk Arena (Kracht Showdown & PvP Tafel)
    Route::get('/armpjedrukken', [ArmWrestleController::class, 'index'])->name('armwrestling.index');
    Route::post('/armpjedrukken/npc/finish', [ArmWrestleController::class, 'finishNpc'])->name('armwrestling.npc.finish');
    Route::post('/armpjedrukken/uitdaging/maken', [ArmWrestleController::class, 'createChallenge'])->name('armwrestling.challenge.create');
    Route::post('/armpjedrukken/uitdaging/{id}/annuleren', [ArmWrestleController::class, 'cancelChallenge'])->name('armwrestling.challenge.cancel');
    Route::post('/armpjedrukken/uitdaging/{id}/accepteren', [ArmWrestleController::class, 'acceptChallenge'])->name('armwrestling.challenge.accept');

    // Partner Minigame 1: 4 op 'n Rij met OOHPA Arjan
    Route::get('/vier-op-een-rij', [ConnectFourController::class, 'index'])->name('connect_four.index');
    Route::post('/vier-op-een-rij/finish', [ConnectFourController::class, 'finishGame'])->name('connect_four.finish');
    Route::post('/vier-op-een-rij/skip-cooldown-ad', [ConnectFourController::class, 'skipCooldownWithAd'])->name('connect_four.skip_cooldown_ad');

    // Partner Minigame 2: True Crime Dossiers met Caroline (500 Cold Cases)
    Route::get('/true-crime', [TrueCrimeController::class, 'index'])->name('true_crime.index');
    Route::post('/true-crime/solve', [TrueCrimeController::class, 'solve'])->name('true_crime.solve');
    Route::post('/true-crime/skip-cooldown-ad', [TrueCrimeController::class, 'skipCooldownWithAd'])->name('true_crime.skip_cooldown_ad');

    // Staff Medewerkers Admin Panel
    Route::middleware(EnsureUserIsStaff::class)->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/spelers', [AdminController::class, 'players'])->name('players');
        Route::post('/spelers/{character}', [AdminController::class, 'updatePlayer'])->name('player.update');
        Route::get('/gameplay', [AdminController::class, 'gameplay'])->name('gameplay');
        Route::post('/gameplay', [AdminController::class, 'updateGameplay'])->name('gameplay.update');
        Route::get('/kisten', [AdminController::class, 'chests'])->name('chests');
        Route::post('/kisten', [AdminController::class, 'updateChests'])->name('chests.update');
        Route::get('/kluis', [AdminController::class, 'vault'])->name('vault');
        Route::post('/kluis', [AdminController::class, 'updateVault'])->name('vault.update');
        Route::post('/kluis/nieuwe-code', [AdminController::class, 'randomizeVaultCode'])->name('vault.randomize');
        Route::get('/referrals', [AdminController::class, 'referrals'])->name('referrals');
        Route::post('/systeem/migreren', [AdminController::class, 'runMigrations'])->name('system.migrate');
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reactie', [AdminTicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('tickets.status');
        Route::post('/tickets/{ticket}/approve-gang-transfer', [AdminTicketController::class, 'approveGangTransfer'])->name('tickets.approve_gang');

        // Nieuws Beheer
        Route::get('/nieuws', [AdminNewsController::class, 'index'])->name('news.index');
        Route::get('/nieuws/nieuw', [AdminNewsController::class, 'create'])->name('news.create');
        Route::post('/nieuws', [AdminNewsController::class, 'store'])->name('news.store');
        Route::get('/nieuws/{article}/bewerken', [AdminNewsController::class, 'edit'])->name('news.edit');
        Route::put('/nieuws/{article}', [AdminNewsController::class, 'update'])->name('news.update');
        Route::post('/nieuws/{article}/toggle-reacties', [AdminNewsController::class, 'toggleComments'])->name('news.toggle-comments');
        Route::delete('/nieuws/{article}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

        // Discord Shield Beheer
        Route::get('/discord-shield', [DiscordShieldAdminController::class, 'index'])->name('discord_shield.index');
        Route::post('/discord-shield/settings', [DiscordShieldAdminController::class, 'updateSettings'])->name('discord_shield.settings');
        Route::post('/discord-shield/post-message', [DiscordShieldAdminController::class, 'postVerificationMessage'])->name('discord_shield.post_message');
        Route::post('/discord-shield/ban', [DiscordShieldAdminController::class, 'ban'])->name('discord_shield.ban');
        Route::delete('/discord-shield/ban/{id}', [DiscordShieldAdminController::class, 'unban'])->name('discord_shield.unban');
        Route::post('/discord-shield/approve/{id}', [DiscordShieldAdminController::class, 'approve'])->name('discord_shield.approve');

        // Spelers Moderatie & Sancties (Bans, Waarschuwingen & Meldingen)
        Route::get('/moderatie', [AdminModerationController::class, 'index'])->name('moderation.index');
        Route::post('/moderatie/spelers/{user}/verbannen', [AdminModerationController::class, 'banUser'])->name('moderation.ban');
        Route::post('/moderatie/spelers/{user}/opheffen', [AdminModerationController::class, 'unbanUser'])->name('moderation.unban');
        Route::post('/moderatie/spelers/{user}/waarschuwen', [AdminModerationController::class, 'warnUser'])->name('moderation.warn');
        Route::delete('/moderatie/waarschuwingen/{warning}', [AdminModerationController::class, 'deleteWarning'])->name('moderation.warning.destroy');
        Route::post('/moderatie/meldingen/{report}/oplossen', [AdminModerationController::class, 'resolveReport'])->name('moderation.report.resolve');
        Route::post('/moderatie/meldingen/{report}/verwerpen', [AdminModerationController::class, 'dismissReport'])->name('moderation.report.dismiss');
        Route::delete('/moderatie/meldingen/{report}', [AdminModerationController::class, 'deleteReport'])->name('moderation.report.destroy');
    });

    // Officiële Waarschuwingen Bevestigen door Speler
    Route::post('/waarschuwingen/{warning}/bevestigen', [WarningController::class, 'acknowledge'])->name('warning.acknowledge');
});
