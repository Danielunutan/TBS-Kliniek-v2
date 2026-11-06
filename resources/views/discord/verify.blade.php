<!DOCTYPE html>
<html lang="nl" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBS Shield • Officiële Discord Verificatie & Toegang</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script>
        (function() {
            const saved = localStorage.getItem('tbs_theme');
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
</head>
<body class="min-h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased selection:bg-purple-600 selection:text-white flex flex-col justify-between">

    <!-- Top Navigation Header -->
    <header class="w-full border-b border-slate-200/90 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md sticky top-0 z-30 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group" title="Naar TBS Kliniek">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center p-1.5 shadow-2xs group-hover:scale-105 transition-transform shrink-0">
                    <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="font-black text-sm tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5 font-['Outfit']">
                        TBS <span class="text-purple-600 dark:text-purple-400">SHIELD</span>
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold block leading-none">Officiële Discord Beveiligingspoort</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 px-3 py-1 rounded-full shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Server Beveiligd</span>
                </span>
                <a href="{{ url('/') }}" class="text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-gamepad text-purple-600 dark:text-purple-400"></i>
                    <span class="hidden sm:inline">Naar het Spel</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Grid -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-12 flex flex-col justify-center">

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-3 shadow-xs">
            <i class="fa-solid fa-circle-check text-base text-emerald-600 dark:text-emerald-400 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-3 shadow-xs">
            <i class="fa-solid fa-triangle-exclamation text-base text-rose-600 dark:text-rose-400 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- LEFT COLUMN: The Verification Card (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">

                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden" x-data="discordShieldApp()">

                    <!-- Step Progress Indicator -->
                    <div class="flex items-center justify-between mb-8 pb-5 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black {{ $discordUser ? 'bg-emerald-500 text-white shadow-xs' : 'bg-purple-600 text-white shadow-xs ring-4 ring-purple-100 dark:ring-purple-900/40' }}">
                                @if($discordUser) <i class="fa-solid fa-check text-[11px]"></i> @else 1 @endif
                            </span>
                            <div>
                                <span class="text-xs font-extrabold text-slate-900 dark:text-white block leading-tight">Stap 1: Discord</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold">{{ $discordUser ? 'Succesvol gekoppeld' : 'Account verbinden' }}</span>
                            </div>
                        </div>

                        <div class="flex-1 mx-4 h-0.5 {{ $discordUser ? 'bg-emerald-400 dark:bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>

                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black {{ $discordUser ? 'bg-purple-600 text-white shadow-xs ring-4 ring-purple-100 dark:ring-purple-900/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                2
                            </span>
                            <div>
                                <span class="text-xs font-extrabold text-slate-900 dark:text-white block leading-tight">Stap 2: Check</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold">Veiligheidscontrole</span>
                            </div>
                        </div>
                    </div>

                    @if(! $discordUser)
                    <!-- STEP 1: Connect Discord -->
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-[#5865F2]/10 border border-[#5865F2]/20 flex items-center justify-center text-3xl text-[#5865F2] shrink-0 shadow-2xs">
                                <i class="fa-brands fa-discord"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200/80 dark:border-purple-800/70 inline-block mb-1">
                                    Snelle Toegang
                                </span>
                                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-['Outfit'] tracking-tight">
                                    Welkom bij TBS Kliniek!
                                </h1>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Koppel eenmalig je Discord om toegang te krijgen tot alle chats, de Minecraft server en giveaways.
                                </p>
                            </div>
                        </div>

                        <!-- Main Action Button -->
                        <div class="pt-2">
                            <a href="{{ route('discord.redirect') }}" class="w-full inline-flex items-center justify-center gap-3 bg-[#5865F2] hover:bg-[#4752C4] text-white font-extrabold py-3.5 px-6 rounded-2xl text-sm transition shadow-sm hover:shadow-md active:scale-[0.99] cursor-pointer">
                                <i class="fa-brands fa-discord text-lg"></i>
                                <span>Inloggen met Discord</span>
                            </a>
                            <span class="text-[10px] text-center text-slate-400 dark:text-slate-500 mt-2 block font-medium">
                                <i class="fa-solid fa-lock text-[9px] mr-1"></i> Veilig via officiële Discord OAuth2. We vragen nooit om je wachtwoord.
                            </span>
                        </div>

                        <!-- 3 Core Trust Badges -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 p-3.5 rounded-xl">
                                <div class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-900/60 text-purple-600 dark:text-purple-300 flex items-center justify-center text-xs mb-2">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <span class="text-xs font-black text-slate-900 dark:text-white block">Eén Account</span>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 leading-tight">Houdt spammers en verbannen accounts direct buiten.</p>
                            </div>

                            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 p-3.5 rounded-xl">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-xs mb-2">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <span class="text-xs font-black text-slate-900 dark:text-white block">Direct Toegang</span>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 leading-tight">Binnen een paar seconden staat je rol automatisch aan.</p>
                            </div>

                            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 p-3.5 rounded-xl">
                                <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xs mb-2">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <span class="text-xs font-black text-slate-900 dark:text-white block">Echte Spelers</span>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 leading-tight">Alleen actieve en gezellige leden op de server.</p>
                            </div>
                        </div>
                    </div>

                    @else
                    <!-- STEP 2: Turnstile & Security Check -->
                    <div class="space-y-6">
                        <!-- Connected User Card -->
                        <div class="flex items-center justify-between p-4 rounded-xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/70">
                            <div class="flex items-center gap-3">
                                @if($discordUser['avatar'])
                                <img src="https://cdn.discordapp.com/avatars/{{ $discordUser['id'] }}/{{ $discordUser['avatar'] }}.png?size=80" alt="{{ $discordUser['username'] }}" class="w-12 h-12 rounded-full object-cover border-2 border-purple-400 shadow-2xs">
                                @else
                                <div class="w-12 h-12 rounded-full bg-[#5865F2] flex items-center justify-center text-white font-black text-base shadow-2xs">
                                    <i class="fa-brands fa-discord"></i>
                                </div>
                                @endif
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-black text-slate-900 dark:text-white">{{ $discordUser['global_name'] ?? $discordUser['username'] }}</span>
                                        <span class="text-[11px] text-purple-700 dark:text-purple-300 font-bold font-mono">({{ $discordUser['username'] }})</span>
                                    </div>
                                    <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 block">ID: {{ $discordUser['id'] }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-black uppercase text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 px-2.5 py-1 rounded-full flex items-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-check text-[9px]"></i> Verbonden
                            </span>
                        </div>

                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white font-['Outfit']">
                                Even je verificatie afronden
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Vink de check hieronder aan en klik op de knop om je rol op Discord te activeren.
                            </p>
                        </div>

                        <!-- Cloudflare Turnstile Box -->
                        <div class="tbs-turnstile-shell">
                            <div class="cf-turnstile tbs-turnstile-frame" 
                                 data-sitekey="{{ $turnstileSiteKey }}" 
                                 data-action="discord_shield_verify"
                                 data-cdata="{{ $turnstileCdata ?? '' }}"
                                 data-size="flexible"
                                 data-language="nl"
                                 data-callback="onTurnstileSuccess"
                                 data-expired-callback="onTurnstileExpired"
                                 data-error-callback="onTurnstileError"
                                 data-retry="auto"
                                 data-refresh-expired="auto"
                                 data-theme="auto"></div>
                        </div>

                        <!-- Error Message -->
                        <div x-show="errorMessage" x-cloak class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-start gap-3 shadow-xs">
                            <i class="fa-solid fa-circle-xmark text-base text-rose-600 dark:text-rose-400 shrink-0 mt-0.5"></i>
                            <span x-text="errorMessage"></span>
                        </div>

                        <!-- Success Message -->
                        <div x-show="successMessage" x-cloak class="p-5 sm:p-6 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-950 dark:text-emerald-100 text-xs flex flex-col gap-4 shadow-xs">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-black text-sm sm:text-base text-emerald-950 dark:text-emerald-100" x-text="successMessage"></div>
                                    <p class="text-xs text-emerald-800 dark:text-emerald-300 mt-1 font-medium leading-relaxed">
                                        Gefeliciteerd! Je geverifieerde rol is actief op Discord. Je kunt nu direct deelnemen aan alle gesprekken in de chat.
                                    </p>
                                </div>
                            </div>

                            <!-- Direct to Discord Chat CTA Button -->
                            <div>
                                <a href="https://discord.com/channels/1546867905139056650/1546867905235255333" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-2.5 bg-gradient-to-r from-[#5865F2] to-[#4752C4] hover:from-[#4752C4] hover:to-[#3c45a5] text-white font-extrabold py-3.5 px-6 rounded-xl text-sm transition shadow-sm hover:shadow-md cursor-pointer hover:scale-[1.01] active:scale-[0.99]">
                                    <i class="fa-brands fa-discord text-base"></i>
                                    <span>Open Discord & Praat Direct Mee</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </a>
                            </div>

                            <!-- Post-Verification Quick Game CTA (Explicitly Optional) -->
                            <div class="pt-3 border-t border-emerald-200/80 dark:border-emerald-800/70 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xs shadow-2xs">
                                        <i class="fa-solid fa-gift"></i>
                                    </span>
                                    <div>
                                        <span class="text-xs font-black text-slate-900 dark:text-white block leading-tight">Optioneel: Gratis Startersbonus in de webgame!</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Staat los van Discord • €2.500 cash + 3 dagen VIP</span>
                                    </div>
                                </div>
                                @if($character)
                                    @if(! $hasClaimedBonus)
                                    <form method="POST" action="{{ route('discord.link') }}">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs transition shadow-2xs cursor-pointer">
                                            Bonus Claimen (€2.500 + VIP)
                                        </button>
                                    </form>
                                    @endif
                                @else
                                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs transition shadow-2xs cursor-pointer">
                                        Spel Proberen (Optioneel)
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Action Submit Button with Anti-Spam Cooldown -->
                        <div x-show="!isVerified">
                            <button type="button" 
                                    @click="submitVerification()" 
                                    :disabled="isLoading || cooldown > 0"
                                    class="w-full inline-flex items-center justify-center gap-2.5 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 text-white font-black py-3.5 px-6 rounded-xl text-sm transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                                <template x-if="isLoading">
                                    <span class="inline-flex items-center gap-2">
                                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                                        <span>Even controleren...</span>
                                    </span>
                                </template>
                                <template x-if="!isLoading && cooldown > 0">
                                    <span class="inline-flex items-center gap-2 text-purple-200">
                                        <i class="fa-solid fa-hourglass-half"></i>
                                        <span>Even wachten... (<span x-text="cooldown"></span>s)</span>
                                    </span>
                                </template>
                                <template x-if="!isLoading && cooldown <= 0">
                                    <span class="inline-flex items-center gap-2">
                                        <i class="fa-solid fa-shield-halved"></i>
                                        <span>Verificatie afronden & toegang krijgen</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Privacy & Guarantee Notice -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex items-center gap-3 text-slate-500 dark:text-slate-400 text-xs">
                    <i class="fa-solid fa-shield-heart text-purple-600 dark:text-purple-400 text-lg shrink-0"></i>
                    <p class="text-[11px] leading-relaxed">
                        <strong>Privacy:</strong> We slaan geen gevoelige persoonsgegevens op. Deze check kijkt puur of je geen VPN, proxy of dubbel account gebruikt.
                    </p>
                </div>

                <!-- Custom Live Discord Server Widget Embed (Clean Light Theme) -->
                @php
                    $members = $widgetData['members'] ?? [];
                    $presenceCount = $widgetData['presence_count'] ?? count($members);
                    $instantInvite = $widgetData['instant_invite'] ?? 'https://discord.gg/1546867905139056650';
                    $serverName = $widgetData['name'] ?? 'TBS Kliniek';
                @endphp
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs relative overflow-hidden" x-data="{ searchMember: '', copiedInvite: false }">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-purple-100/50 dark:bg-purple-900/20 rounded-full blur-2xl pointer-events-none"></div>

                    <!-- Widget Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-[#5865F2] flex items-center justify-center text-white text-xl shadow-xs shrink-0">
                                <i class="fa-brands fa-discord"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-sm sm:text-base text-slate-900 dark:text-white tracking-tight font-['Outfit']">{{ $serverName }}</span>
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-mono">Server</span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.7)] animate-pulse"></span>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 font-mono">{{ $presenceCount }} Leden Nu Online</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Copy Invite Link Button -->
                            <button type="button" 
                                    @click="navigator.clipboard.writeText(@js($instantInvite)); copiedInvite = true; setTimeout(() => copiedInvite = false, 2000)" 
                                    title="Kopieer server invite link"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200/80 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition cursor-pointer active:scale-95 shadow-2xs">
                                <template x-if="!copiedInvite">
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="fa-regular fa-copy text-xs text-slate-500 dark:text-slate-400"></i>
                                        <span class="hidden sm:inline">Invite</span>
                                    </span>
                                </template>
                                <template x-if="copiedInvite">
                                    <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-black">
                                        <i class="fa-solid fa-check text-xs"></i>
                                        <span class="hidden sm:inline">Gekopieerd!</span>
                                    </span>
                                </template>
                            </button>

                            <a href="{{ $instantInvite }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#5865F2] hover:bg-[#4752C4] text-white font-black text-xs transition shadow-2xs hover:scale-[1.02] active:scale-95 cursor-pointer">
                                <span>Openen</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Live Members Showcase -->
                    <div class="pt-4">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Actieve Leden Nu ({{ count($members) }})
                            </span>
                            <span class="text-[10px] font-semibold text-purple-600 dark:text-purple-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live Server Feed
                            </span>
                        </div>

                        <!-- Real-time Search / Filter Input -->
                        <div class="relative mb-2.5">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-xs pointer-events-none"></i>
                            <input type="text" 
                                   x-model="searchMember" 
                                   placeholder="Zoek actief lid of game..." 
                                   class="w-full pl-8 pr-8 py-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition shadow-2xs">
                            <button type="button" 
                                    x-show="searchMember" 
                                    @click="searchMember = ''" 
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 text-xs p-1 cursor-pointer">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Scrollable Members List (Clean Contrast Theme) -->
                        <div class="space-y-1 max-h-56 overflow-y-auto pr-1 divide-y divide-slate-100 dark:divide-slate-800/80 custom-scrollbar">
                            @forelse($members as $m)
                                @php
                                    $statusColor = match($m['status'] ?? 'online') {
                                        'online' => 'bg-emerald-500 ring-white dark:ring-slate-900',
                                        'idle' => 'bg-amber-400 ring-white dark:ring-slate-900',
                                        'dnd' => 'bg-rose-500 ring-white dark:ring-slate-900',
                                        default => 'bg-slate-400 ring-white dark:ring-slate-900',
                                    };
                                    $game = $m['game']['name'] ?? null;
                                    $isBot = ($m['bot'] ?? false) || in_array(strtolower($m['username']), ['counting', 'craig', 'disboard', 'flavibot', 'truth or dare', 'guess the song', 'dank memer', 'tbs kliniek']);
                                    $searchString = strtolower($m['username'] . ' ' . ($game ?? ''));
                                @endphp
                                <div x-show="!searchMember || @js($searchString).includes(searchMember.toLowerCase().trim())"
                                     class="flex items-center justify-between py-2 px-2.5 rounded-xl hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition group">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="relative shrink-0">
                                            <img src="{{ $m['avatar_url'] }}" alt="{{ $m['username'] }}" class="w-7 h-7 rounded-full object-cover bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-900 ring-1 {{ $statusColor }}"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                                                    {{ $m['username'] }}
                                                </span>
                                                @if($isBot)
                                                    <span class="text-[8px] font-black uppercase px-1 py-0.5 bg-[#5865F2]/10 text-[#5865F2] border border-[#5865F2]/20 rounded font-mono">BOT</span>
                                                @endif
                                            </div>
                                            @if($game)
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate block leading-tight font-medium">
                                                    <i class="fa-solid fa-gamepad text-[9px] text-purple-500 dark:text-purple-400 mr-0.5"></i> {{ Str::limit($game, 24) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 capitalize shrink-0 pl-2">
                                        {{ $m['status'] === 'dnd' ? 'Niet storen' : ($m['status'] === 'idle' ? 'Afwezig' : 'Online') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-slate-400 dark:text-slate-500">
                                    <i class="fa-solid fa-users text-lg text-slate-300 dark:text-slate-600 mb-1 block"></i>
                                    Geen openbare ledenlijst beschikbaar.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Widget Footer -->
                    <div class="pt-3.5 mt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1.5 text-[11px]">
                            <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400 text-xs"></i>
                            <span>Officiële TBS Kliniek Community</span>
                        </span>
                        <a href="{{ $instantInvite }}" target="_blank" rel="noopener noreferrer" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-extrabold transition flex items-center gap-1 text-[11px]">
                            <span>Openen in Discord</span>
                            <i class="fa-solid fa-chevron-right text-[9px]"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Community Showcase & Server Rules (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- PROMO BANNER: Play TBS-Kliniek.nl with Starter Bonus (Seamless Clean Light/Dark Design) -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4 relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-28 h-28 bg-purple-50 dark:bg-purple-900/20 rounded-full blur-xl pointer-events-none"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-sm shadow-2xs">
                                <i class="fa-solid fa-gamepad"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-sm text-slate-900 dark:text-white font-['Outfit']">TBS-Kliniek de Webgame</h3>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block -mt-0.5">Optioneel online misdaadspel</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-black uppercase text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-2.5 py-0.5 rounded-full font-mono">
                            Vrijblijvend
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Wil je naast de Discord ook ons online misdaadspel spelen? Koppel je account en ontvang direct een gratis startersbonus:
                    </p>

                    <!-- Rustige en nette info notice -->
                    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-slate-600 dark:text-slate-300 text-[11px] leading-relaxed">
                        <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400 text-xs shrink-0 mt-0.5"></i>
                        <span>
                            <strong class="text-slate-800 dark:text-slate-200">Staat los van verificatie:</strong> Je hebt <u>geen</u> game-account nodig voor Discord toegang. Dit is puur een extraatje voor wie ook het spel wil spelen!
                        </span>
                    </div>

                    <!-- Perks Grid (Subtiel & Clean) -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/70 flex items-center justify-center text-sm shrink-0">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <span class="text-xs font-black text-slate-900 dark:text-white block leading-tight">€2.500 Cash</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Direct op zak</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/60 dark:border-purple-800/70 flex items-center justify-center text-sm shrink-0">
                                <i class="fa-solid fa-crown"></i>
                            </div>
                            <div>
                                <span class="text-xs font-black text-slate-900 dark:text-white block leading-tight">3 Dagen VIP</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Gratis extra voordelen</span>
                            </div>
                        </div>
                    </div>

                    <!-- Discord Role Sync Notice -->
                    <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 pt-0.5">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-xs shrink-0"></i>
                            <span class="text-[11px]">Ontgrendelt direct de rol <strong class="text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-1.5 py-0.5 rounded border border-purple-200/80 dark:border-purple-800/70">TBS-Kliniek.nl</strong> op Discord</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-xs shrink-0"></i>
                            <span class="text-[11px]">Krijg automatisch de gouden <strong class="text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-1.5 py-0.5 rounded border border-amber-200/80 dark:border-amber-800/70">VIP rol</strong></span>
                        </div>
                    </div>

                    <!-- Interactive Action based on Authentication -->
                    @if($character)
                        @if($hasClaimedBonus)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-900 dark:text-emerald-200 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base shrink-0"></i>
                                    <div class="min-w-0">
                                        <span class="font-extrabold block truncate">Gekoppeld als {{ $character->name }}</span>
                                        <span class="text-[10px] text-emerald-700 dark:text-emerald-300 font-medium">Bonus reeds geclaimd</span>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('discord.sync_roles') }}" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[11px] transition shadow-2xs cursor-pointer">
                                        Rollen Syncen
                                    </button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('discord.link') }}">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black py-3 px-4 rounded-xl text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                                    <i class="fa-solid fa-gift"></i>
                                    <span>Koppel met {{ $character->name }} & Claim Bonus</span>
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="space-y-2 pt-1">
                            <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black py-3 px-4 rounded-xl text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                                <i class="fa-solid fa-user-plus text-xs"></i>
                                <span>Game Account Aanmaken (Optioneel)</span>
                            </a>
                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-[11px] font-bold text-slate-400 dark:text-slate-500 hover:text-purple-600 dark:hover:text-purple-400 transition">
                                    Al een spelersaccount? Log hier in &rarr;
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- What you unlock card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-sm shadow-2xs">
                            <i class="fa-solid fa-unlock-keyhole"></i>
                        </div>
                        <h3 class="font-black text-sm text-slate-900 dark:text-white font-['Outfit']">Wat ontgrendel je na verificatie?</h3>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <i class="fa-solid fa-comments text-purple-600 dark:text-purple-400 text-sm shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="text-slate-900 dark:text-white block">Algemene Chat & Gezelligheid</strong>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Praat mee over TBS Kliniek, drop je screenshots en join het gesprek.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <i class="fa-solid fa-cube text-emerald-600 dark:text-emerald-400 text-sm shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="text-slate-900 dark:text-white block">Minecraft Survival Server</strong>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Bouw en speel mee op onze eigen server met farms, steden en expedities.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <i class="fa-solid fa-gift text-amber-500 dark:text-amber-400 text-sm shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="text-slate-900 dark:text-white block">Giveaways & Drops</strong>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Regelmatig koffer-drops, VIP acties en leuke community events.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <i class="fa-solid fa-headset text-indigo-600 dark:text-indigo-400 text-sm shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="text-slate-900 dark:text-white block">Support & Ideeën</strong>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Vraag snel hulp via tickets of geef nieuwe ideeën door voor de game.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Community Rules Summary -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-scale-balanced text-slate-400 dark:text-slate-500"></i>
                        <h4 class="font-extrabold text-xs text-slate-900 dark:text-white uppercase tracking-wider">Huisregels</h4>
                    </div>

                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Door te verifiëren ga je akkoord met onze regels:
                    </p>

                    <ul class="text-[11px] space-y-2 text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                            <span>Houd het gezellig en behandel elkaar netjes.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                            <span>Geen haatzaaien, racisme of toxic gedrag.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                            <span>Geen ongevraagde reclame of spam via DM.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                            <span>Maximaal 1 Discord account per persoon (geen alts).</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 py-4 text-center text-xs text-slate-500 dark:text-slate-400 font-medium">
        &copy; {{ date('Y') }} TBS Kliniek • Alle rechten voorbehouden.
    </footer>

    <!-- Client-side Fingerprinting & Verification Engine -->
    <script>
        let globalTurnstileToken = '';

        function onTurnstileSuccess(token) {
            globalTurnstileToken = token;
        }

        function onTurnstileExpired() {
            globalTurnstileToken = '';
            if (window.turnstile && typeof window.turnstile.reset === 'function') {
                try { window.turnstile.reset(); } catch (e) {}
            }
        }

        function onTurnstileError() {
            globalTurnstileToken = '';
            setTimeout(() => {
                if (window.turnstile && typeof window.turnstile.reset === 'function') {
                    try { window.turnstile.reset(); } catch (e) {}
                }
            }, 1500);
        }

        function discordShieldApp() {
            return {
                isLoading: false,
                isVerified: false,
                errorMessage: '',
                successMessage: '',
                cooldown: 0,
                cooldownTimer: null,

                launchConfetti() {
                    if (typeof confetti === 'function') {
                        // Multi-burst celebration
                        confetti({
                            particleCount: 80,
                            spread: 75,
                            origin: { y: 0.6 },
                            colors: ['#7c3aed', '#6366f1', '#10b981', '#f59e0b', '#ec4899']
                        });
                        setTimeout(() => {
                            confetti({
                                particleCount: 50,
                                angle: 60,
                                spread: 55,
                                origin: { x: 0 },
                                colors: ['#7c3aed', '#10b981', '#6366f1']
                            });
                            confetti({
                                particleCount: 50,
                                angle: 120,
                                spread: 55,
                                origin: { x: 1 },
                                colors: ['#7c3aed', '#f59e0b', '#10b981']
                            });
                        }, 220);
                    }
                },

                async getWebRtcIps() {
                    return new Promise((resolve) => {
                        const ips = [];
                        try {
                            const RTCPC = window.RTCPeerConnection || window.webkitRTCPeerConnection || window.mozRTCPeerConnection;
                            if (!RTCPC) {
                                return resolve([]);
                            }
                            const pc = new RTCPC({
                                iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
                            });
                            pc.createDataChannel("");
                            pc.createOffer().then(o => pc.setLocalDescription(o)).catch(() => {});
                            pc.onicecandidate = (e) => {
                                if (!e || !e.candidate || !e.candidate.candidate) {
                                    return;
                                }
                                const match = e.candidate.candidate.match(/([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/);
                                if (match && match[1]) {
                                    const ip = match[1];
                                    if (!ips.includes(ip) && !ip.endsWith('.local') && !ip.startsWith('127.')) {
                                        ips.push(ip);
                                    }
                                }
                            };
                            setTimeout(() => {
                                try { pc.close(); } catch(err){}
                                resolve(ips);
                            }, 800);
                        } catch(err) {
                            resolve([]);
                        }
                    });
                },
                
                async submitVerification() {
                    if (this.cooldown > 0) return;
                    this.errorMessage = '';
                    this.isLoading = true;

                    try {
                        // Gather fingerprint and WebRTC candidates in parallel
                        const [fpData, webrtcIps] = await Promise.all([
                            this.generateFingerprint(),
                            this.getWebRtcIps()
                        ]);

                        const timezoneOffset = new Date().getTimezoneOffset();
                        const timezoneName = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Unknown';

                        const res = await fetch("{{ route('discord.process') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                turnstile_token: globalTurnstileToken,
                                fingerprint_hash: fpData.hash,
                                fingerprint_raw: fpData.raw,
                                device_type: fpData.deviceType,
                                browser: fpData.browser,
                                os: fpData.os,
                                webrtc_ips: webrtcIps,
                                timezone_offset: timezoneOffset,
                                timezone_name: timezoneName
                            })
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.isVerified = true;
                            this.successMessage = data.message;
                            this.launchConfetti();
                        } else {
                            this.errorMessage = data.message || 'Verificatie geweigerd.';
                            globalTurnstileToken = '';
                            if (window.turnstile && typeof window.turnstile.reset === 'function') {
                                try { window.turnstile.reset(); } catch (e) {}
                            }
                            // Start 5 second cooldown to prevent accidental or automated spamming
                            this.cooldown = 5;
                            if (this.cooldownTimer) clearInterval(this.cooldownTimer);
                            this.cooldownTimer = setInterval(() => {
                                this.cooldown--;
                                if (this.cooldown <= 0) {
                                    clearInterval(this.cooldownTimer);
                                    this.cooldownTimer = null;
                                }
                            }, 1000);
                        }
                    } catch (err) {
                        this.errorMessage = 'Er is een verbindingsfout opgetreden. Probeer het opnieuw.';
                        globalTurnstileToken = '';
                        if (window.turnstile && typeof window.turnstile.reset === 'function') {
                            try { window.turnstile.reset(); } catch (e) {}
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                async generateFingerprint() {
                    const rawComponents = {};

                    // 1. Canvas Fingerprint
                    try {
                        const canvas = document.createElement('canvas');
                        canvas.width = 200;
                        canvas.height = 50;
                        const ctx = canvas.getContext('2d');
                        ctx.textBaseline = "top";
                        ctx.font = "14px 'Arial'";
                        ctx.textBaseline = "alphabetic";
                        ctx.fillStyle = "#f60";
                        ctx.fillRect(125,1,62,20);
                        ctx.fillStyle = "#069";
                        ctx.fillText("TBS_SHIELD_2026", 2, 15);
                        ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
                        ctx.fillText("TBS_SHIELD_2026", 4, 17);
                        rawComponents.canvas = canvas.toDataURL();
                    } catch(e) {
                        rawComponents.canvas = 'none';
                    }

                    // 2. WebGL Hardware Fingerprint
                    try {
                        const glCanvas = document.createElement('canvas');
                        const gl = glCanvas.getContext('webgl') || glCanvas.getContext('experimental-webgl');
                        if (gl) {
                            const dbg = gl.getExtension('WEBGL_debug_renderer_info');
                            rawComponents.webgl_vendor = dbg ? gl.getParameter(dbg.UNMASKED_VENDOR_WEBGL) : gl.getParameter(gl.VENDOR);
                            rawComponents.webgl_renderer = dbg ? gl.getParameter(dbg.UNMASKED_RENDERER_WEBGL) : gl.getParameter(gl.RENDERER);
                        }
                    } catch(e) {
                        rawComponents.webgl = 'none';
                    }

                    // 3. Screen & Hardware details
                    rawComponents.screen = `${window.screen.width}x${window.screen.height}x${window.screen.colorDepth}`;
                    rawComponents.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                    rawComponents.concurrency = navigator.hardwareConcurrency || 4;
                    rawComponents.device_memory = navigator.deviceMemory || 4;
                    rawComponents.platform = navigator.platform || 'Unknown';
                    rawComponents.languages = (navigator.languages || [navigator.language]).join(',');

                    // 4. Persistent LocalStorage UUID & Cookie UID (Dual-Storage Anchor)
                    let localSeed = localStorage.getItem('tbs_shield_seed');
                    if (!localSeed) {
                        const match = document.cookie.match(new RegExp('(^| )tbs_perm_uid=([^;]+)'));
                        if (match && match[2]) {
                            localSeed = match[2];
                        } else {
                            localSeed = Math.random().toString(36).substring(2) + Date.now().toString(36);
                        }
                        try { localStorage.setItem('tbs_shield_seed', localSeed); } catch(e){}
                    }
                    // Re-arm cookie for 10 years so incognito/cleared cookies don't bypass
                    document.cookie = "tbs_perm_uid=" + localSeed + "; path=/; max-age=315360000; SameSite=Lax";
                    rawComponents.seed = localSeed;
                    rawComponents.cookie_uid = localSeed;

                    // Combine into deterministic SHA-256 hash
                    const combinedStr = Object.values(rawComponents).join('|||');
                    const hashBuffer = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(combinedStr));
                    const hashArray = Array.from(new Uint8Array(hashBuffer));
                    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

                    // Detect device type & OS
                    const ua = navigator.userAgent;
                    let os = 'Windows';
                    if (ua.indexOf('Mac') !== -1) os = 'MacOS';
                    if (ua.indexOf('Linux') !== -1) os = 'Linux';
                    if (ua.indexOf('Android') !== -1) os = 'Android';
                    if (ua.indexOf('iPhone') !== -1 || ua.indexOf('iPad') !== -1) os = 'iOS';

                    let browser = 'Chrome';
                    if (ua.indexOf('Firefox') !== -1) browser = 'Firefox';
                    if (ua.indexOf('Safari') !== -1 && ua.indexOf('Chrome') === -1) browser = 'Safari';
                    if (ua.indexOf('Edg') !== -1) browser = 'Edge';

                    const isMobile = /Mobi|Android/i.test(ua);

                    return {
                        hash: hashHex,
                        raw: rawComponents,
                        deviceType: isMobile ? 'Mobiel' : 'Desktop',
                        browser: browser,
                        os: os
                    };
                }
            };
        }
    </script>
</body>
</html>
