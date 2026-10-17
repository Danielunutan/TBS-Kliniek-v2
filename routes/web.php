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
use App\Http\Controllers\CombatTrainingController;
use App\Http\Controllers\CommunityController;
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
use App\Http\Controllers\GarageController;
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
use App\Http\Controllers\PickpocketController;
use App\Http\Controllers\PrisonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RadioController;
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
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/auth/discord', [AuthController::class, 'discordLogin'])->name('login.discord');
    Route::get('/auth/discord/callback', [AuthController::class, 'discordCallback'])->name('login.discord.callback');

    // Wachtwoord Vergeten (E-mail)
    Route::get('/wachtwoord-vergeten', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/wachtwoord-vergeten', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/wachtwoord-reset/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/wachtwoord-reset', [AuthController::class, 'resetPassword'])->name('password.update');
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

    // E-mail Bevestiging
    Route::get('/email/verify', [AuthController::class, 'verifyNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');

    // Helptool: Support Ticketsysteem
    Route::get('/help/tickets', [SupportTicketController::class, 'index'])->name('help.tickets.index');
    Route::get('/help/tickets/nieuw', [SupportTicketController::class, 'create'])->name('help.tickets.create');
    Route::post('/help/tickets', [SupportTicketController::class, 'store'])->name('help.tickets.store');
    Route::get('/help/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('help.tickets.show');
    Route::post('/help/tickets/{ticket}/reactie', [SupportTicketController::class, 'reply'])->name('help.tickets.reply');
    Route::post('/help/tickets/{ticket}/sluiten', [SupportTicketController::class, 'close'])->name('help.tickets.close');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::post('/diefstal', [TheftController::class, 'steal'])->name('theft.steal');

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
    Route::post('/heists/start/{heist}', [HeistController::class, 'launch'])->name('heists.launch');

    // Extort (Afpersen)
    Route::get('/afpersen', [ExtortController::class, 'index'])->name('extort.index');
    Route::post('/afpersen/uitvoeren', [ExtortController::class, 'extort'])->name('extort.extort');

    // Pickpocket (Zakkenrollen)
    Route::get('/zakkenrollen', [PickpocketController::class, 'index'])->name('pickpocket.index');
    Route::post('/zakkenrollen/stelen', [PickpocketController::class, 'steal'])->name('pickpocket.steal');

    // Lockers (Kluisjes)
    Route::get('/kluisjes', [LockerController::class, 'index'])->name('lockers.index');
    Route::post('/kluisjes/kraken', [LockerController::class, 'crack'])->name('lockers.crack');

    // Packages (Pakketjes)
    Route::get('/pakketjes', [PackageController::class, 'index'])->name('packages.index');
    Route::post('/pakketjes/onderscheppen', [PackageController::class, 'intercept'])->name('packages.intercept');
    Route::post('/pakketjes/tas-openen', [PackageController::class, 'openBag'])->name('packages.open-bag');
    Route::post('/pakketjes/tas-verkopen', [PackageController::class, 'sellBag'])->name('packages.sell-bag');
    Route::post('/pakketjes/xxl-openen', [PackageController::class, 'openXxl'])->name('packages.open-xxl');
    Route::post('/pakketjes/xxl-kopen', [PackageController::class, 'buyXxl'])->name('packages.buy-xxl');

    // 24 Uur Battle (Dagelijkse Battle)
    Route::get('/24-uur-battle', [DailyBattleController::class, 'index'])->name('battle.index');
    Route::get('/24-uur-battle/categorie/{category}', [DailyBattleController::class, 'categoryDetails'])->name('battle.category');

    // Rad van Fortuin & Dagelijkse Beloningen
    Route::get('/rad-van-fortuin', [DailyRewardController::class, 'index'])->name('daily.index');
    Route::post('/rad-van-fortuin/spin', [DailyRewardController::class, 'spin'])->name('daily.spin');
    Route::post('/rad-van-fortuin/streak', [DailyRewardController::class, 'claimStreak'])->name('daily.claim-streak');

    // Vault (Kluis Kraak & Codes)
    Route::get('/kluis-kraak', [VaultController::class, 'index'])->name('vault.index');
    Route::post('/kluis-kraak/raden', [VaultController::class, 'guess'])->name('vault.guess');
    Route::post('/kluis-kraak/koop-code', [VaultController::class, 'buyCode'])->name('vault.buy-code');
    Route::post('/kluis-kraak/koop-hint', [VaultController::class, 'buyHint'])->name('vault.buy-hint');
    Route::post('/kluis-kraak/koop-jammer', [VaultController::class, 'buyJammer'])->name('vault.buy-jammer');
    Route::post('/kluis-kraak/inwisselen', [VaultController::class, 'redeemCode'])->name('vault.redeem');
    Route::post('/kluis-kraak/code-verkopen', [VaultController::class, 'sellCode'])->name('vault.sell-code');
    Route::post('/kluis-kraak/maskers-verkopen', [VaultController::class, 'sellMask'])->name('vault.sell-mask');

    // VIP Punten Shop (Diamanten)
    Route::get('/vip-shop', [VipController::class, 'index'])->name('vip.index');
    Route::post('/vip-shop/inwisselen', [VipController::class, 'redeem'])->name('vip.redeem');

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
    Route::post('/casino/steen-papier-schaar/spelen', [RpsController::class, 'play'])->name('rps.play');

    // High/Low (Hoger & Lager)
    Route::get('/casino/hoger-lager', [HighLowController::class, 'index'])->name('highlow.index');
    Route::post('/casino/hoger-lager/spelen', [HighLowController::class, 'play'])->name('highlow.play');

    // Number Game (Nummerspel)
    Route::get('/casino/nummerspel', [NumberGameController::class, 'index'])->name('numbergame.index');
    Route::post('/casino/nummerspel/spelen', [NumberGameController::class, 'play'])->name('numbergame.play');

    // Roulette
    Route::get('/casino/roulette', [RouletteController::class, 'index'])->name('roulette.index');
    Route::post('/casino/roulette/spelen', [RouletteController::class, 'play'])->name('roulette.play');

    // Slots (Fruitmachine)
    Route::get('/casino/fruitautomaat', [SlotsController::class, 'index'])->name('slots.index');
    Route::post('/casino/fruitautomaat/draaien', [SlotsController::class, 'spin'])->name('slots.spin');

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
    Route::get('/luchtplaats/arena/{defender}', [FightController::class, 'arena'])->name('fight.arena');
    Route::post('/luchtplaats/attack', [FightController::class, 'attack'])->name('fight.attack');

    // Bank
    Route::get('/bank', [BankController::class, 'index'])->name('bank.index');
    Route::post('/bank/storten', [BankController::class, 'deposit'])->name('bank.deposit');
    Route::post('/bank/opnemen', [BankController::class, 'withdraw'])->name('bank.withdraw');

    // Casino
    Route::get('/casino', [CasinoController::class, 'index'])->name('casino.index');
    Route::post('/casino/play', [CasinoController::class, 'play'])->name('casino.play');

    // Shop & Inventory
    Route::get('/winkel', [ShopController::class, 'index'])->name('shop.index');
    Route::post('/winkel/{item}/kopen', [ShopController::class, 'buy'])->name('shop.buy');
    Route::post('/winkel/sterren-kopen', [ShopController::class, 'buyStarItem'])->name('shop.buy-star-item');
    Route::post('/winkel/bundel-kopen', [ShopController::class, 'buyBundle'])->name('shop.buy-bundle');
    Route::post('/inventaris/{characterItem}/gebruiken', [ShopController::class, 'consume'])->name('shop.consume');

    // Persoonlijk Depot (Garage, Opbergplaats & Event Varia)
    Route::get('/depot', [DepotController::class, 'index'])->name('depot.index');
    Route::post('/depot/verkopen/{characterItem}', [DepotController::class, 'sellItem'])->name('depot.sell-item');

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
    Route::post('/speler/{name}/respect', [ProfileController::class, 'giveRespect'])->name('profile.respect');
    Route::post('/speler/{name}/gastenboek', [ProfileController::class, 'postGuestbook'])->name('guestbook.post');
    Route::delete('/speler/gastenboek/{entry}', [ProfileController::class, 'destroyGuestbook'])->name('guestbook.destroy');
    Route::post('/profiel/bijwerken', [ProfileController::class, 'updateBio'])->name('profile.update');
    Route::post('/profiel/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::post('/profiel/titel', [ProfileController::class, 'updateTitle'])->name('profile.title');

    // Gangs
    Route::get('/gangs', [GangController::class, 'index'])->name('gangs.index');
    Route::get('/gangs/leden', [GangController::class, 'leden'])->name('gangs.leden');
    Route::get('/gangs/beheer', [GangController::class, 'beheer'])->name('gangs.beheer');
    Route::get('/gangs/instellingen', [GangController::class, 'instellingen'])->name('gangs.instellingen');
    Route::post('/gangs/aanmaken', [GangController::class, 'create'])->name('gangs.create');
    Route::post('/gangs/{gang}/aansluiten', [GangController::class, 'join'])->name('gangs.join');
    Route::post('/gangs/{gang}/aanvragen', [GangController::class, 'requestJoin'])->name('gangs.request-join');
    Route::post('/gangs/verlaten', [GangController::class, 'leave'])->name('gangs.leave');
    Route::post('/gangs/doneren', [GangController::class, 'donate'])->name('gangs.donate');
    Route::post('/gangs/doneren/sterren', [GangController::class, 'donateStars'])->name('gangs.donate-stars');
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

    // Marktplaats (Marketplace)
    Route::get('/marktplaats', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::post('/marktplaats', [MarketplaceController::class, 'store'])->name('marketplace.store');
    Route::post('/marktplaats/{listing}/kopen', [MarketplaceController::class, 'buy'])->name('marketplace.buy');
    Route::post('/marktplaats/{listing}/annuleren', [MarketplaceController::class, 'cancel'])->name('marketplace.cancel');

    // Leaderboard & Leden / Patiëntenlijst
    Route::get('/ranglijst', [LeaderboardController::class, 'index'])->name('leaderboard.index');
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

        // Spelers Moderatie & Sancties (Bans & Waarschuwingen)
        Route::get('/moderatie', [AdminModerationController::class, 'index'])->name('moderation.index');
        Route::post('/moderatie/spelers/{user}/verbannen', [AdminModerationController::class, 'banUser'])->name('moderation.ban');
        Route::post('/moderatie/spelers/{user}/opheffen', [AdminModerationController::class, 'unbanUser'])->name('moderation.unban');
        Route::post('/moderatie/spelers/{user}/waarschuwen', [AdminModerationController::class, 'warnUser'])->name('moderation.warn');
        Route::delete('/moderatie/waarschuwingen/{warning}', [AdminModerationController::class, 'deleteWarning'])->name('moderation.warning.destroy');
    });

    // Officiële Waarschuwingen Bevestigen door Speler
    Route::post('/waarschuwingen/{warning}/bevestigen', [WarningController::class, 'acknowledge'])->name('warning.acknowledge');
});
