@extends('layouts.app')

@section('title', 'Account & Beveiliging - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="accountSettings()">

    <!-- Top Hero Card (TBS Modern Aesthetic) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/70 flex items-center justify-center text-2xl shadow-xs shrink-0">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Account & Beveiliging</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed max-w-xl">
                        Beheer je inloggegevens, gebruikersnaam, weergavenaam, profielfoto, tweestapsverificatie en actieve apparaten.
                    </p>
                </div>
            </div>

            <!-- User Badge -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 shrink-0">
                <div class="px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/70 text-xs font-bold text-purple-800 dark:text-purple-300">
                    <span class="text-purple-400 block text-[10px] uppercase font-mono">E-mailadres</span>
                    <span>{{ $user->email }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Sections -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 1. USERNAME & DISPLAY NAME CHANGE -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-signature text-purple-600"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">Identiteit & Namen</h3>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full {{ $canChangeUsername ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $canChangeUsername ? 'Beschikbaar' : 'Vergrendeld' }}
                </span>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Je <strong>Gebruikersnaam</strong> is jouw unieke systeemnaam (zonder rare tekens). Je <strong>Weergavenaam</strong> is de roepnaam waarmee mede-patiënten jou zien in het spel.
            </p>

            @if(! $canChangeUsername)
                <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-700/50 rounded-xl p-3 flex items-start gap-2.5">
                    <i class="fa-solid fa-lock text-amber-600 dark:text-amber-400 mt-0.5 text-xs"></i>
                    <div class="text-[11px] text-amber-900 dark:text-amber-200 leading-relaxed">
                        <strong>@Gebruikersnaam vergrendeld:</strong> gewijzigd op {{ $character->username_changed_at->format('d-m-Y H:i') }}. Volgende systeemnaam-wijziging over <span class="font-mono font-bold">{{ $daysUntilChange }} dagen</span>.
                        <span class="block text-slate-500 dark:text-slate-400 mt-0.5">Je kunt je <strong>Weergavenaam</strong> en <strong>Profielkleur</strong> hieronder wel altijd vrij aanpassen!</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('settings.username.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Gebruikersnaam (Strict Alphanumeric) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            Unieke Gebruikersnaam <span class="text-purple-600 dark:text-purple-400 font-normal">(@username)</span>
                        </label>
                        @if(! $canChangeUsername)
                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                <i class="fa-solid fa-lock text-[9px]"></i> Tijdelijk vast
                            </span>
                        @endif
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">@</span>
                        <input type="text" name="username" value="{{ old('username', $character->username) }}" maxlength="20"
                               @if(! $canChangeUsername) readonly @endif
                               class="w-full rounded-xl pl-7 pr-3.5 py-2 text-xs font-bold transition focus:outline-none {{ ! $canChangeUsername ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 cursor-not-allowed border border-slate-200 dark:border-slate-700' : 'bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-purple-600' }}">
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">
                        @if($canChangeUsername)
                            Min 3, max 20 tekens. Alleen letters (a-z), cijfers (0-9), underscores (_) en streepjes (-). <strong>Geen spaties of rare tekens</strong>.
                        @else
                            Deze systeemnaam staat vast tot {{ $character->username_changed_at->copy()->addMonths(6)->format('d-m-Y') }}.
                        @endif
                    </span>
                </div>

                <!-- Weergavenaam (Display Name - Altijd aanpasbaar!) -->
                <div>
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">
                        Weergavenaam / Roepnaam <span class="text-emerald-600 dark:text-emerald-400 font-semibold">(Altijd aanpasbaar)</span>
                    </label>
                    <input type="text" name="display_name" value="{{ old('display_name', $character->display_name ?: $character->name) }}" required maxlength="30"
                           class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    <span class="text-[10px] text-slate-400 mt-1 block">
                        Hoe je naam getoond wordt in het spel (mag spaties, leestekens en emoji's bevatten).
                    </span>
                </div>

                <!-- Accentkleur (Altijd aanpasbaar!) -->
                <div>
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">
                        Profiel Accentkleur <span class="text-emerald-600 dark:text-emerald-400 font-semibold">(Altijd aanpasbaar)</span>
                    </label>
                    <select name="profile_color" class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        <option value="purple" {{ $character->profile_color === 'purple' ? 'selected' : '' }}>TBS Paars (Standaard)</option>
                        <option value="rose" {{ $character->profile_color === 'rose' ? 'selected' : '' }}>Bloedrood</option>
                        <option value="emerald" {{ $character->profile_color === 'emerald' ? 'selected' : '' }}>Smaragdgroen</option>
                        <option value="amber" {{ $character->profile_color === 'amber' ? 'selected' : '' }}>Goudgeel</option>
                        <option value="blue" {{ $character->profile_color === 'blue' ? 'selected' : '' }}>Nachtblauw</option>
                        <option value="indigo" {{ $character->profile_color === 'indigo' ? 'selected' : '' }}>Diep Indigo</option>
                        <option value="dark" {{ $character->profile_color === 'dark' ? 'selected' : '' }}>Stealth Zwart</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-check mr-1.5"></i> Identiteit & Kleur Opslaan
                </button>
            </form>
        </div>

        <!-- 2. AVATAR UPLOAD & RASTER CROP TOOL -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-image text-purple-600"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">Profielfoto & Crop Tool</h3>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300">
                    Interactief Raster
                </span>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-purple-500 shadow-sm shrink-0 bg-slate-100 dark:bg-slate-800">
                    <img :src="currentAvatarUrl" alt="{{ $character->display_name }}" class="w-full h-full object-cover">
                </div>
                <div class="space-y-1">
                    <h4 class="text-xs font-black text-slate-900 dark:text-white">Huidige Patiëntfoto</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Upload een eigen foto en snijd hem nauwkeurig bij met het interactieve crop-raster.
                    </p>
                    <button type="button" @click="openCropModal()" 
                            class="mt-2 py-2 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-crop-simple"></i>
                        <span>Nieuwe Foto Uitsnijden</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. WACHTWOORD WIJZIGEN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                <i class="fa-solid fa-key text-purple-600 dark:text-purple-400"></i>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">Wachtwoord Wijzigen</h3>
            </div>

            <form action="{{ route('settings.password.update') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Huidig Wachtwoord</label>
                    <input type="password" name="current_password" required
                           class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Nieuw Wachtwoord</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">Minimaal 8 tekens.</span>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Bevestig Nieuw Wachtwoord</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-lock mr-1.5"></i> Wachtwoord Opslaan
                </button>
            </form>
        </div>

        <!-- 4. TWO-FACTOR AUTHENTICATION (2FA) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">2-Factor Authenticatie</h3>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full {{ $user->two_factor_enabled ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700' }}">
                    {{ $user->two_factor_enabled ? 'Ingeschakeld' : 'Uitgeschakeld' }}
                </span>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Beveilig je account extra met een tweede verificatiestap. Hierdoor kan niemand zonder jouw toestemming inloggen op je TBS profiel.
            </p>

            <form action="{{ route('settings.2fa.toggle') }}" method="POST">
                @csrf
                <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 block">Status van 2FA</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $user->two_factor_enabled ? 'Jouw account is extra beveiligd.' : 'Schakel 2FA in voor extra veiligheid.' }}
                        </span>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl {{ $user->two_factor_enabled ? 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500' }} text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                        {{ $user->two_factor_enabled ? 'Uitschakelen' : 'Inschakelen' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- 5. ACTIEVE SESSIES & AFSTAND -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4 md:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen-button text-purple-600 dark:text-purple-400"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">Actieve Sessies & Afstand</h3>
                </div>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Overzicht van apparaten waarop je momenteel bent ingelogd. Ben je vergeten uit te loggen op een andere computer of telefoon? Log direct alle andere apparaten op afstand uit.
            </p>

            <div class="space-y-2.5">
                @forelse($sessions as $sess)
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid {{ $sess['device'] === 'Mobiele Telefoon' ? 'fa-mobile-screen' : 'fa-laptop' }} text-purple-600 dark:text-purple-400 text-base"></i>
                        <div>
                            <div class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ $sess['device'] }}</span>
                                @if($sess['is_current'])
                                    <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 text-[9px] font-black uppercase px-2 py-0.5 rounded-full">Dit apparaat</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">
                                IP: {{ $sess['ip'] }} • {{ $sess['last_active'] }}
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>Huidig apparaat actief.</span>
                </div>
                @endforelse
            </div>

            <!-- Remote Logout Form -->
            <form action="{{ route('settings.sessions.logout-others') }}" method="POST" class="pt-2 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
                @csrf
                <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 block">Vul je wachtwoord in om andere apparaten te verbreken:</label>
                <div class="flex items-center gap-2">
                    <input type="password" name="password" placeholder="Jouw wachtwoord" required
                           class="flex-1 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i> Overal Uitloggen
                    </button>
                </div>
            </form>
        </div>

        <!-- 6. PUSHNOTIFICATIES (DESKTOP & MOBIELE WEBAPP) -->
        <div x-data="pushSettings()" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4 md:col-span-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">Pushnotificaties (Desktop &amp; WebApp)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ontvang live meldingen op je scherm, zelfs als de website of app afgesloten is.</p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/10 dark:bg-amber-950/40 border border-amber-500/30 dark:border-amber-700/50 text-amber-700 dark:text-amber-300 font-extrabold text-[11px] mt-1.5">
                            <i class="fa-solid fa-gift text-amber-600 dark:text-amber-400"></i>
                            <span>🎁 Bonus: Ontvang direct € 25.000 cash, 25 respect en 20 energie bij activatie!</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                          :class="isSubscribed ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700'"
                          x-text="isSubscribed ? 'Meldingen Actief' : 'Uitgeschakeld'"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 text-xs">
                <div class="lg:col-span-2 space-y-3">
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        Schakel native meldingen in om direct gewaarschuwd te worden bij kritieke gebeurtenissen in de kliniek:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] text-slate-700 dark:text-slate-300 font-semibold">
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <span class="text-rose-500 text-base">🚨</span>
                            <span>Bestolen of beroofd worden</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <span class="text-amber-500 text-base">⚔️</span>
                            <span>Aangevallen op de luchtplaats</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <span class="text-purple-500 text-base">💬</span>
                            <span>Nieuwe privéberichten (DMs)</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <span class="text-indigo-500 text-base">🛡️</span>
                            <span>Bende-uitnodigingen &amp; oorlogen</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <span class="text-blue-500 text-base">🔒</span>
                            <span>Vrijlating uit isoleercel of bajes</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <span class="text-emerald-500 text-base">⚡</span>
                            <span>Energie vol of dagelijkse beloning</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/50 flex flex-col justify-between space-y-3">
                    <div>
                        <span class="text-[10px] font-black uppercase text-purple-600 dark:text-purple-400 tracking-wider block">Jouw Apparaat</span>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                            Ondersteunt Windows, macOS, Android en iOS 16.4+ (wanneer geïnstalleerd als webapp op je startscherm).
                        </p>
                    </div>

                    <div class="space-y-2 pt-2">
                        <button type="button" @click="togglePush()" :disabled="loading"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-black transition-all duration-150 active:scale-95 flex items-center justify-center gap-2 shadow-xs cursor-pointer disabled:opacity-50"
                                :class="isSubscribed ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white'">
                            <i class="fa-solid" :class="loading ? 'fa-spinner animate-spin' : (isSubscribed ? 'fa-bell-slash' : 'fa-bell')"></i>
                            <span x-text="loading ? 'Bezig...' : (isSubscribed ? 'Meldingen Uitschakelen' : 'Meldingen Inschakelen')"></span>
                        </button>

                        <button type="button" x-show="isSubscribed" @click="sendTestNotification()" :disabled="testing"
                                class="w-full py-2 px-3 rounded-xl bg-white hover:bg-purple-50 dark:bg-slate-900 dark:hover:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-[11px] font-bold transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                            <i class="fa-solid" :class="testing ? 'fa-spinner animate-spin' : 'fa-paper-plane'"></i>
                            <span x-text="testing ? 'Verzenden...' : 'Stuur Testnotificatie'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- INTERACTIEVE CROP MODAL MET RASTER VOOR PROFIELFOTO      -->
    <!-- ======================================================== -->
    <div x-show="cropModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
        <div @click.away="closeCropModal()" class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200/90 dark:border-slate-800 space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-crop-simple"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Profielfoto Uitsnijden (Raster)</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Sleep en zoom voor de perfecte uitsnede.</p>
                    </div>
                </div>
                <button type="button" @click="closeCropModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <!-- FILE PICKER AREA (IF NO IMAGE YET) -->
            <div x-show="!imageLoaded" class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 text-center space-y-3 bg-slate-50/90 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition cursor-pointer" @click="$refs.fileInput.click()">
                <div class="w-14 h-14 mx-auto rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div>
                    <span class="text-xs font-black text-slate-800 dark:text-slate-200 block">Klik om een nieuwe foto te kiezen</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500">JPG, PNG of WebP (max 5MB)</span>
                </div>
                <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept="image/*" class="hidden">
            </div>

            <!-- INTERACTIVE CANVAS WITH GRID OVERLAY (WHEN IMAGE LOADED) -->
            <div x-show="imageLoaded" class="space-y-4">
                <div class="relative w-full aspect-square bg-slate-950 rounded-2xl overflow-hidden flex items-center justify-center border border-slate-800 select-none touch-none">
                    <canvas x-ref="cropCanvas" width="360" height="360" class="cursor-grab active:cursor-grabbing w-full h-full touch-none"
                            @mousedown="startDrag($event)" @mousemove="onDrag($event)" @mouseup="endDrag()" @mouseleave="endDrag()"
                            @touchstart="startTouch($event)" @touchmove="onTouch($event)" @touchend="endDrag()"></canvas>

                    <!-- GRID OVERLAY (RULE OF THIRDS / RASTER) -->
                    <div class="absolute inset-0 pointer-events-none grid grid-cols-3 grid-rows-3 border border-white/40">
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-b border-white/20"></div>
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-b border-white/20"></div>
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-r border-b border-white/20"></div>
                        <div></div>
                    </div>

                    <!-- ROUND GUIDE PREVIEW OVERLAY -->
                    <div class="absolute inset-0 pointer-events-none rounded-full border-2 border-purple-500/80 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]"></div>
                </div>

                <!-- CONTROLS: ZOOM SLIDER -->
                <div class="flex items-center gap-3 bg-slate-50/90 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                    <i class="fa-solid fa-magnifying-glass-minus text-slate-400 dark:text-slate-500 text-xs"></i>
                    <input type="range" min="0.5" max="3" step="0.05" x-model="zoom" @input="redrawCanvas()"
                           class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-purple-600">
                    <i class="fa-solid fa-magnifying-glass-plus text-slate-400 dark:text-slate-500 text-xs"></i>
                    <button type="button" @click="rotateImage()" class="px-2 py-1 rounded-lg bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer" title="90 graden draaien">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>

                <!-- MODAL BUTTONS -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="$refs.fileInput.click()" class="text-xs text-purple-600 dark:text-purple-400 font-bold hover:underline cursor-pointer">
                        Andere foto kiezen
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="closeCropModal()" class="py-2 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                            Annuleren
                        </button>
                        <button type="button" @click="applyCrop()" :disabled="uploadingCrop"
                                class="py-2 px-5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black shadow-xs transition-all duration-150 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                            <i x-show="uploadingCrop" class="fa-solid fa-spinner animate-spin"></i>
                            <span x-text="uploadingCrop ? 'Opslaan...' : 'Foto Opslaan'"></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
function accountSettings() {
    return {
        currentAvatarUrl: '{{ $character->avatar_url }}',
        cropModalOpen: false,
        imageLoaded: false,
        uploadingCrop: false,
        rawImage: null,
        zoom: 1,
        rotation: 0,
        offsetX: 0,
        offsetY: 0,
        isDragging: false,
        startX: 0,
        startY: 0,

        openCropModal() {
            this.cropModalOpen = true;
            this.imageLoaded = false;
            this.rawImage = null;
            this.zoom = 1;
            this.rotation = 0;
            this.offsetX = 0;
            this.offsetY = 0;
        },

        closeCropModal() {
            this.cropModalOpen = false;
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (evt) => {
                const img = new Image();
                img.onload = () => {
                    this.rawImage = img;
                    this.imageLoaded = true;
                    this.zoom = 1;
                    this.rotation = 0;
                    this.offsetX = 0;
                    this.offsetY = 0;
                    this.$nextTick(() => this.redrawCanvas());
                };
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        },

        startDrag(e) {
            this.isDragging = true;
            this.startX = e.clientX - this.offsetX;
            this.startY = e.clientY - this.offsetY;
        },

        onDrag(e) {
            if (!this.isDragging) return;
            this.offsetX = e.clientX - this.startX;
            this.offsetY = e.clientY - this.startY;
            this.redrawCanvas();
        },

        startTouch(e) {
            if (e.touches.length === 1) {
                this.isDragging = true;
                this.startX = e.touches[0].clientX - this.offsetX;
                this.startY = e.touches[0].clientY - this.offsetY;
            }
        },

        onTouch(e) {
            if (!this.isDragging || e.touches.length !== 1) return;
            this.offsetX = e.touches[0].clientX - this.startX;
            this.offsetY = e.touches[0].clientY - this.startY;
            this.redrawCanvas();
        },

        endDrag() {
            this.isDragging = false;
        },

        rotateImage() {
            this.rotation = (this.rotation + 90) % 360;
            this.redrawCanvas();
        },

        redrawCanvas() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas || !this.rawImage) return;
            const ctx = canvas.getContext('2d');
            const w = canvas.width;
            const h = canvas.height;

            ctx.clearRect(0, 0, w, h);
            ctx.save();
            ctx.translate(w / 2 + this.offsetX, h / 2 + this.offsetY);
            ctx.rotate((this.rotation * Math.PI) / 180);
            ctx.scale(this.zoom, this.zoom);

            const scale = Math.max(w / this.rawImage.width, h / this.rawImage.height);
            const drawW = this.rawImage.width * scale;
            const drawH = this.rawImage.height * scale;

            ctx.drawImage(this.rawImage, -drawW / 2, -drawH / 2, drawW, drawH);
            ctx.restore();
        },

        applyCrop() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas) return;
            this.uploadingCrop = true;

            const base64 = canvas.toDataURL('image/jpeg', 0.9);

            fetch('{{ route('settings.avatar.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ image: base64 })
            })
            .then(r => r.json())
            .then(data => {
                this.uploadingCrop = false;
                if (data.success) {
                    this.currentAvatarUrl = data.url;
                    this.closeCropModal();
                    alert(data.message || 'Profielfoto succesvol gewijzigd!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Fout bij opslaan van foto.');
                }
            })
            .catch(() => {
                this.uploadingCrop = false;
                alert('Er is een netwerkfout opgetreden bij het uploaden.');
            });
        }
    };
}

function pushSettings() {
    return {
        isSubscribed: false,
        loading: false,
        testing: false,

        async init() {
            if (window.tbsPush) {
                this.isSubscribed = await window.tbsPush.isSubscribed();
            }
        },

        async togglePush() {
            if (!window.tbsPush) return;
            this.loading = true;
            try {
                if (this.isSubscribed) {
                    await window.tbsPush.unsubscribe();
                    this.isSubscribed = false;
                    alert('Pushnotificaties zijn uitgeschakeld.');
                } else {
                    const res = await window.tbsPush.subscribe();
                    if (res.success) {
                        this.isSubscribed = true;
                        alert('Top! Pushnotificaties zijn ingeschakeld op dit apparaat.');
                    } else {
                        alert(res.message || 'Inschakelen mislukt.');
                    }
                }
            } catch (err) {
                alert(err.message || 'Er is een fout opgetreden.');
            } finally {
                this.loading = false;
            }
        },

        async sendTestNotification() {
            if (!window.tbsPush) return;
            this.testing = true;
            try {
                const res = await window.tbsPush.sendTest();
                if (res.success) {
                    alert('Testnotificatie is verzonden! Controleer je scherm.');
                } else {
                    alert(res.message || 'Verzenden mislukt.');
                }
            } catch (err) {
                alert(err.message || 'Netwerkfout.');
            } finally {
                this.testing = false;
            }
        }
    };
}
</script>
@endsection
