@extends('layouts.app')

@section('title', 'TBS Kliniek - Het #1 Misdaad & Gevangenis Spel van Nederland')
@section('meta_description', 'Meld je gratis aan bij TBS Kliniek: hét populairste Nederlandse misdaad en gevangenis RPG browsergame van 2026! Kraak medicijnkluizen, leid je gang en overleef de afdeling.')
@section('meta_keywords', 'TBS Kliniek, boeven spel, gevangenis browsergame, misdaad rpg nederland, tekstbased rpg, gratis browsergame spelen')

@section('content')
@php
    $safeGender = in_array(old('gender'), ['male', 'female'], true) ? old('gender') : 'male';
    $safeAvatar = preg_match('~^assets/characters/avatar_(fe)?male_[1-6]\.jpg$~', (string) old('avatar')) ? old('avatar') : 'assets/characters/avatar_male_1.jpg';
    $defaultTab = (request()->routeIs('register') || request('tab') === 'register' || $errors->has('username') || $errors->has('terms') || $errors->has('password_confirmation') || $errors->has('turnstile') || old('username') || request()->has('ref') || session()->has('referral_name')) ? 'register' : 'login';
    $turnstileSiteKey = !empty($turnstileSiteKey) ? $turnstileSiteKey : (trim((string) config('services.turnstile.site_key')) ?: '1x00000000000000000000AA');
    $turnstileLoginCdata = $turnstileLoginCdata ?? \App\Services\TurnstileService::generateCdata('auth_login', request()->ip() ?: 'guest');
    $turnstileRegisterCdata = $turnstileRegisterCdata ?? \App\Services\TurnstileService::generateCdata('auth_register', request()->ip() ?: 'guest');
@endphp
<div class="max-w-6xl xl:max-w-7xl mx-auto space-y-6" x-data="{ 
    tab: @js($defaultTab), 
    regGender: @js($safeGender), 
    regAvatar: @js($safeAvatar) 
}" x-init="
    $watch('tab', (val) => {
        if (window.tbsMountAuthTab) window.tbsMountAuthTab(val);
    });
    $nextTick(() => { if (window.tbsMountAuthTab) window.tbsMountAuthTab(tab); });
">

    <!-- MAIN LOGIN / REGISTER CARD (EXPANSIVE CLEAN SHOWCASE) -->
    <div id="auth-form-card" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden grid grid-cols-1 lg:grid-cols-12">
        
        <!-- LEFT HERO COLUMN (WIDE CLEAN TBS KLINIEK ARTWORK & CHARACTERS) -->
        <div class="lg:col-span-7 xl:col-span-7 bg-slate-950 text-white relative p-6 sm:p-8 flex flex-col justify-between overflow-hidden shadow-inner min-h-[240px] sm:min-h-[360px] lg:min-h-[620px]">
            <!-- Crisp High Quality Illustration Lineup (All 5 Characters Fully Visible) -->
            <img src="{{ asset('assets/illustrations/tbs_crew_lineup.jpg') }}?v={{ file_exists(public_path('assets/illustrations/tbs_crew_lineup.jpg')) ? filemtime(public_path('assets/illustrations/tbs_crew_lineup.jpg')) : '3' }}" 
                 alt="TBS Kliniek - Het #1 Misdaad Spel van Nederland" 
                 class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none">
            
            <!-- Subtle Vignette Gradients for Legibility at Bottom without Obscuring Characters -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent pointer-events-none"></div>

            <!-- Bottom Flavor Quote & Branding Tagline -->
            <div class="relative z-10 pt-4 mt-auto border-t border-purple-400/25 flex items-start gap-3 backdrop-blur-xs">
                <i class="fa-solid fa-quote-left text-amber-400/80 text-base shrink-0 mt-0.5"></i>
                <div class="w-full">
                    <p class="text-xs sm:text-sm text-purple-100 italic leading-relaxed drop-shadow">
                        "In de kliniek draait alles om macht, aanzien en wie de sleutel heeft van de warmhoudkast."
                    </p>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <span class="text-[11px] font-black text-amber-300 uppercase tracking-wider drop-shadow">&mdash; Henkie de Frikandelkoning</span>
                        <span class="text-[11px] font-bold text-purple-200/80 uppercase tracking-wider">TBS Kliniek #1 RPG</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT FORM COLUMN (CLEAN WHITE / DARK SLATE SURFACE) -->
        <div class="lg:col-span-5 xl:col-span-5 p-6 sm:p-10 flex flex-col justify-center bg-white dark:bg-slate-900">
            <div class="w-full max-w-md mx-auto space-y-5">
                
                <!-- Header -->
                <div class="text-center sm:text-left">
                    <div class="lg:hidden flex justify-center mb-3">
                        <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek Logo" class="w-14 h-14 object-contain drop-shadow-md">
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Welkom bij de Kliniek</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Meld je aan voor je dossier of start direct.</p>
                </div>

                <!-- SOCIAL AUTH (DISCORD 1-KLIK) -->
                <div>
                    <a href="{{ route('login.discord') }}" data-turbo="false" data-turbo-prefetch="false" class="w-full flex items-center justify-center gap-2.5 bg-[#5865F2] hover:bg-[#4752c4] text-white font-extrabold py-2.5 px-4 rounded-xl text-xs sm:text-sm shadow-2xs transition active:scale-[0.99]">
                        <i class="fa-brands fa-discord text-base"></i>
                        <span>Doorgaan met Discord</span>
                        <span class="ml-auto bg-white/20 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">1-Klik</span>
                    </a>
                </div>

                <!-- CLEAN DIVIDER -->
                <div class="relative flex items-center justify-center">
                    <div class="border-t border-slate-200/80 dark:border-slate-800 w-full"></div>
                    <span class="bg-white dark:bg-slate-900 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 shrink-0">
                        of met e-mail
                    </span>
                    <div class="border-t border-slate-200/80 dark:border-slate-800 w-full"></div>
                </div>

                <!-- MODERN PILL TAB SWITCHER (MATCHES SITE TABS) -->
                <div class="bg-slate-100/90 dark:bg-slate-800/80 p-1 rounded-xl flex gap-1 border border-slate-200/80 dark:border-slate-700/70">
                    <button type="button" @click="tab = 'login'; if (window.tbsMountAuthTab) window.tbsMountAuthTab('login');"
                            :class="tab === 'login' ? 'bg-white dark:bg-purple-600 text-slate-900 dark:text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                            class="flex-1 py-2 text-xs rounded-lg transition text-center cursor-pointer">
                        Inloggen
                    </button>
                    <button type="button" @click="tab = 'register'; if (window.tbsMountAuthTab) window.tbsMountAuthTab('register');"
                            :class="tab === 'register' ? 'bg-white dark:bg-purple-600 text-slate-900 dark:text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                            class="flex-1 py-2 text-xs rounded-lg transition text-center cursor-pointer">
                        Registreren
                    </button>
                </div>

                <!-- LOGIN TAB (NO UGLY LABELS - MODERN CLEAN INPUTS) -->
                <div x-show="tab === 'login'" class="space-y-4">
                    @if($errors->any() && $errors->has('email') && !old('username') && !old('terms'))
                    <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" class="space-y-3">
                        @csrf
                        <input type="text" name="tbs_guard_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;">
                        <input type="hidden" name="cf-turnstile-response" id="turnstile-login-token" value="">

                        <!-- Email Input -->
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="E-mailadres / Dossiernummer"
                                   class="w-full pl-9 pr-3.5 py-2.5 text-base sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        </div>

                        <!-- Password Input with Eye Toggle -->
                        <div class="relative" x-data="{ showPass: false }">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </span>
                            <input :type="showPass ? 'text' : 'password'" name="password" required
                                   placeholder="Wachtwoord"
                                   class="w-full pl-9 pr-10 py-2.5 text-base sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition cursor-pointer">
                                <i class="fa-solid text-xs" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>

                        <!-- Options -->
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-0.5">
                            <label class="flex items-center gap-2 cursor-pointer font-medium hover:text-slate-700 dark:hover:text-slate-300">
                                <input type="checkbox" name="remember" checked class="w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-purple-600 focus:ring-purple-500">
                                <span>Sessie onthouden</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-bold transition">Wachtwoord kwijt?</a>
                        </div>

                        <!-- Cloudflare Turnstile Security Verification (Login) -->
                        <div class="flex justify-center items-center py-1">
                            <div id="turnstile-login-widget"
                                 data-sitekey="{{ $turnstileSiteKey }}"
                                 data-action="auth_login"
                                 data-cdata="{{ $turnstileLoginCdata }}"
                                 class="min-h-[65px]"></div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black py-3 px-4 rounded-xl text-xs sm:text-sm shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-right-to-bracket text-xs"></i>
                            <span>Inloggen op Dossier</span>
                        </button>
                    </form>
                </div>

                <!-- REGISTER TAB (SLEEK ONBOARDING: USERNAME, EMAIL, PASSWORD, AVATAR & TERMS) -->
                <div x-show="tab === 'register'" x-cloak class="space-y-4">
                    
                    @if($errors->any() && (old('username') || old('terms') || $errors->has('turnstile')))
                    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-700 dark:text-rose-300 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                            <span>Inschrijving niet gelukt:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-600 dark:text-rose-400">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('register.post') }}" class="space-y-3.5">
                        @csrf
                        <input type="text" name="tbs_guard_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;">
                        <input type="hidden" name="cf-turnstile-response" id="turnstile-register-token" value="">

                        @php
                            $activeRef = request('ref', session('referral_name'));
                            $isOohpaRef = in_array(strtolower((string)$activeRef), ['oohpaarjan', 'oohpa', 'arjan', 'oohpa_arjan', 'oohpa-arjan']);
                        @endphp
                        @if($activeRef)
                        <input type="hidden" name="ref" value="{{ $activeRef }}">
                        @if($isOohpaRef)
                        <div class="p-3.5 rounded-xl bg-gradient-to-r from-amber-500/15 via-purple-600/15 to-purple-900/20 border-2 border-amber-400 text-slate-900 dark:text-amber-200 text-xs flex items-center gap-3 shadow-md animate-pulse">
                            <div class="w-9 h-9 rounded-lg bg-amber-400 text-slate-950 flex items-center justify-center text-base shrink-0 font-black shadow-sm">
                                <i class="fa-solid fa-crown"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="font-black text-amber-700 dark:text-amber-300 block text-xs uppercase tracking-wide">🎉 OOHPA Arjan Actie Geactiveerd!</span>
                                <span class="text-[11px] font-semibold text-slate-700 dark:text-zinc-200 block">Maak nu je account aan en ontvang direct <strong class="text-amber-700 dark:text-amber-400 underline">3 Dagen Gratis VIP</strong> status cadeau!</span>
                            </div>
                        </div>
                        @else
                        <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-900 dark:text-purple-200 text-xs flex items-center gap-2.5 shadow-2xs">
                            <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center text-sm shrink-0">
                                <i class="fa-solid fa-gift"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="font-extrabold block truncate">Uitgenodigd door: {{ $activeRef }}</span>
                                <span class="text-[11px] text-purple-700 dark:text-purple-300">Meld je aan en begin direct samen in de TBS Kliniek!</span>
                            </div>
                        </div>
                        @endif
                        @endif

                        <!-- 1. ACCOUNT FIELDS -->
                        <div class="space-y-2.5">
                            <!-- Gebruikersnaam / Alias -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <i class="fa-solid fa-user-ninja text-xs"></i>
                                </span>
                                <input type="text" name="username" value="{{ old('username') }}" placeholder="Gebruikersnaam / Kliniek Alias" required minlength="3" maxlength="25"
                                       class="w-full pl-9 pr-3.5 py-2.5 text-base sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            </div>

                            <!-- Email -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <i class="fa-solid fa-envelope text-xs"></i>
                                </span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="E-mailadres" required
                                       class="w-full pl-9 pr-3.5 py-2.5 text-base sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            </div>

                            <!-- Wachtwoord & Bevestigen (Strak naast elkaar of compact onder elkaar) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" x-data="{ showRegPass: false }">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                        <i class="fa-solid fa-lock text-xs"></i>
                                    </span>
                                    <input :type="showRegPass ? 'text' : 'password'" name="password" placeholder="Wachtwoord (min. 6)" required
                                           class="w-full pl-9 pr-8 py-2.5 text-base sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                                    <button type="button" @click="showRegPass = !showRegPass" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition cursor-pointer">
                                        <i class="fa-solid text-[11px]" :class="showRegPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                        <i class="fa-solid fa-shield-halved text-xs"></i>
                                    </span>
                                    <input :type="showRegPass ? 'text' : 'password'" name="password_confirmation" placeholder="Herhaal Wachtwoord" required
                                           class="w-full pl-9 pr-3.5 py-2.5 text-base sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                                </div>
                            </div>
                        </div>

                        <!-- 2. KARAKTER ONBOARDING (GESLACHT & AVATAR KIEZEN) -->
                        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3.5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <i class="fa-solid fa-id-card text-purple-600 dark:text-purple-400"></i>
                                    <span>Patiëntenpas Onboarding</span>
                                </span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase">Stap 1 van 1</span>
                            </div>

                            <!-- Geslacht Keuze -->
                            <div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="regGender = 'male'; if(!regAvatar.includes('male')) regAvatar = 'assets/characters/avatar_male_1.jpg'"
                                            :class="regGender === 'male' ? 'bg-white dark:bg-purple-600 border-purple-600 text-purple-950 dark:text-white font-extrabold shadow-xs ring-1 ring-purple-600/20' : 'bg-slate-100/70 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold hover:bg-white dark:hover:bg-slate-700'"
                                            class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl border text-xs transition cursor-pointer">
                                        <i class="fa-solid fa-mars text-purple-600 dark:text-purple-400 text-sm"></i>
                                        <span>Man</span>
                                    </button>
                                    <button type="button" @click="regGender = 'female'; if(!regAvatar.includes('female')) regAvatar = 'assets/characters/avatar_female_1.jpg'"
                                            :class="regGender === 'female' ? 'bg-white dark:bg-purple-600 border-purple-600 text-purple-950 dark:text-white font-extrabold shadow-xs ring-1 ring-purple-600/20' : 'bg-slate-100/70 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold hover:bg-white dark:hover:bg-slate-700'"
                                            class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl border text-xs transition cursor-pointer">
                                        <i class="fa-solid fa-venus text-purple-600 dark:text-purple-400 text-sm"></i>
                                        <span>Vrouw</span>
                                    </button>
                                </div>
                                <input type="hidden" name="gender" :value="regGender">
                            </div>

                            <!-- Avatar Gallery (6 Man, 6 Vrouw - Paarse Stijl - Zonder Namen) -->
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">Kies jouw profielfoto</span>
                                
                                <!-- Mannelijke Avatars (6) -->
                                <div x-show="regGender === 'male'" class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                    @foreach([
                                        'assets/characters/avatar_male_1.jpg',
                                        'assets/characters/avatar_male_2.jpg',
                                        'assets/characters/avatar_male_3.jpg',
                                        'assets/characters/avatar_male_4.jpg',
                                        'assets/characters/avatar_male_5.jpg',
                                        'assets/characters/avatar_male_6.jpg',
                                    ] as $avFile)
                                    <button type="button" @click="regAvatar = '{{ $avFile }}'"
                                            :class="regAvatar === '{{ $avFile }}' ? 'border-purple-600 ring-2 ring-purple-600/30 bg-purple-50/50 dark:bg-purple-950/60 shadow-xs scale-105' : 'border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600 opacity-80 hover:opacity-100'"
                                            class="relative p-1 rounded-xl border flex items-center justify-center transition cursor-pointer group aspect-square">
                                        <div class="w-full h-full rounded-lg overflow-hidden relative shadow-2xs">
                                            <img src="{{ asset($avFile) }}" alt="Profielfoto" class="w-full h-full object-cover">
                                            <div x-show="regAvatar === '{{ $avFile }}'" class="absolute inset-0 bg-purple-600/20 flex items-center justify-center">
                                                <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[9px] sm:text-[10px] shadow-xs">
                                                    <i class="fa-solid fa-check"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                    @endforeach
                                </div>

                                <!-- Vrouwelijke Avatars (6) -->
                                <div x-show="regGender === 'female'" class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                    @foreach([
                                        'assets/characters/avatar_female_1.jpg',
                                        'assets/characters/avatar_female_2.jpg',
                                        'assets/characters/avatar_female_3.jpg',
                                        'assets/characters/avatar_female_4.jpg',
                                        'assets/characters/avatar_female_5.jpg',
                                        'assets/characters/avatar_female_6.jpg',
                                    ] as $avFile)
                                    <button type="button" @click="regAvatar = '{{ $avFile }}'"
                                            :class="regAvatar === '{{ $avFile }}' ? 'border-purple-600 ring-2 ring-purple-600/30 bg-purple-50/50 dark:bg-purple-950/60 shadow-xs scale-105' : 'border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600 opacity-80 hover:opacity-100'"
                                            class="relative p-1 rounded-xl border flex items-center justify-center transition cursor-pointer group aspect-square">
                                        <div class="w-full h-full rounded-lg overflow-hidden relative shadow-2xs">
                                            <img src="{{ asset($avFile) }}" alt="Profielfoto" class="w-full h-full object-cover">
                                            <div x-show="regAvatar === '{{ $avFile }}'" class="absolute inset-0 bg-purple-600/20 flex items-center justify-center">
                                                <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[9px] sm:text-[10px] shadow-xs">
                                                    <i class="fa-solid fa-check"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="avatar" :value="regAvatar">
                            </div>
                        </div>

                        <!-- 3. ALGEMENE VOORWAARDEN CHECKBOX -->
                        <div class="pt-0.5">
                            <label class="flex items-start gap-2.5 cursor-pointer text-xs text-slate-600 dark:text-slate-400 group select-none">
                                <input type="checkbox" name="terms" value="1" required class="w-4 h-4 mt-0.5 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                <span class="leading-snug group-hover:text-slate-900 dark:group-hover:text-slate-200 transition text-[11px] sm:text-xs">
                                    Ik ga akkoord met de <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener noreferrer" class="text-purple-600 dark:text-purple-400 font-extrabold hover:underline">algemene voorwaarden</a> en het <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener noreferrer" class="text-purple-600 dark:text-purple-400 font-extrabold hover:underline">privacybeleid</a>.
                                </span>
                            </label>
                        </div>

                        <!-- Cloudflare Turnstile Security Verification (Register) -->
                        <div class="flex justify-center items-center py-1">
                            <div id="turnstile-register-widget"
                                 data-sitekey="{{ $turnstileSiteKey }}"
                                 data-action="auth_register"
                                 data-cdata="{{ $turnstileRegisterCdata }}"
                                 class="min-h-[65px]"></div>
                        </div>

                        <!-- 4. SUBMIT BUTTON -->
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black py-3 px-4 rounded-xl text-xs sm:text-sm shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-user-plus text-xs"></i>
                            <span>Dossier Aanmaken & Starten</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

    <!-- EXCLUSIVE GANG OVERSTAP BONUS BANNER (HOMEPAGE PROMOTION) -->
    <div class="rounded-2xl p-5 sm:p-6 bg-gradient-to-r from-purple-50 via-indigo-50/60 to-amber-50/40 dark:from-[#180a33] dark:via-[#130b2c] dark:to-[#0f172a] border-2 border-purple-200/90 dark:border-purple-800/60 text-slate-900 dark:text-white shadow-xs relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5">
        <!-- Ambient Decorative Glows -->
        <div class="absolute -top-16 -left-16 w-44 h-44 bg-purple-500/10 dark:bg-purple-600/15 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-44 h-44 bg-amber-500/10 dark:bg-amber-600/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-2.5 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider shadow-xs">
                <i class="fa-solid fa-crown text-slate-950"></i>
                <span>Exclusieve Overstap Bonus</span>
            </div>
            
            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                Speel je met je bende op een andere game? Stap nu over naar TBS Kliniek!
            </h3>
            
            <p class="text-xs text-slate-600 dark:text-purple-200/90 leading-relaxed">
                Neem je vrienden of clan mee en meld je aan via ons ticket-systeem. Ontvang direct een <strong class="text-purple-700 dark:text-amber-300 font-black">Gratis Bende</strong>, <strong class="text-purple-700 dark:text-amber-300 font-black">€ 5.000.000 startkapitaal</strong> in jullie gangpot én <strong class="text-purple-700 dark:text-amber-300 font-black">3 dagen gratis VIP</strong> voor al je leden!
            </p>

            <!-- 3 Quick Spotlight Badges -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/90 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/60 text-[11px] font-bold text-slate-800 dark:text-purple-200 shadow-2xs">
                    <i class="fa-solid fa-shield-halved text-amber-500 text-xs"></i> Gratis Bende
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/90 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/60 text-[11px] font-bold text-slate-800 dark:text-purple-200 shadow-2xs">
                    <i class="fa-solid fa-vault text-emerald-500 text-xs"></i> € 5.000.000 Kluisgeld
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/90 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/60 text-[11px] font-bold text-slate-800 dark:text-purple-200 shadow-2xs">
                    <i class="fa-solid fa-star text-purple-500 dark:text-purple-400 text-xs"></i> 3 Dagen Gratis VIP
                </span>
            </div>
        </div>

        <div class="relative z-10 shrink-0 flex items-center gap-2.5">
            <a href="{{ route('faq.index') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold text-xs transition border border-slate-200/80 dark:border-slate-700 shadow-xs">
                Uitleg in FAQ
            </a>
            <button type="button" @click="tab = 'register'; if (window.tbsMountAuthTab) window.tbsMountAuthTab('register'); document.getElementById('auth-form-card')?.scrollIntoView({behavior: 'smooth'})" class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 active:scale-95 text-slate-950 font-black text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                <span>Meld Je Bende Aan</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- 3 SUBTLE CLEAN FEATURE CARDS (MATCHES DASHBOARD / SITE CLEANLINESS) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/40 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <h3 class="text-xs font-extrabold text-slate-900 dark:text-white">Hosselen & Kraken</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Steel frikandelbroodjes en kraak kluisjes voor cash.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/40 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-hand-fist"></i>
            </div>
            <div>
                <h3 class="text-xs font-extrabold text-slate-900 dark:text-white">Luchtplaats PvP</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Vecht tegen rivalen om respect en sticht je eigen bende.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-200/50 dark:border-purple-800/40 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-dice"></i>
            </div>
            <div>
                <h3 class="text-xs font-extrabold text-slate-900 dark:text-white">Ondergronds Casino</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Roulette, fruitautomaten en nummerspel met hoge jackpots.</p>
            </div>
        </div>
    </div>

</div>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onAuthTurnstileLoaded&render=explicit" async defer data-tbs-turnstile-api="1"></script>
<script>
    window.tbsAuthTurnstileWidgets = window.tbsAuthTurnstileWidgets || { login: null, register: null };
    window.tbsAuthTurnstileMounting = window.tbsAuthTurnstileMounting || { login: false, register: false };
    window.tbsAuthTurnstileTabTimers = window.tbsAuthTurnstileTabTimers || { login: null, register: null };

    window.tbsHasMountedTurnstileDom = function(container) {
        if (!container) return false;
        return Boolean(container.querySelector('iframe') || container.children.length > 0);
    };

    window.tbsEnsureAuthTurnstileScript = function(forceReload = false) {
        if (!forceReload && window.turnstile && typeof window.turnstile.render === 'function') {
            return Promise.resolve(window.turnstile);
        }

        if (forceReload) {
            document.querySelectorAll('script[data-tbs-turnstile-api], script[src*="challenges.cloudflare.com/turnstile"]').forEach(function(el) {
                el.remove();
            });
            window._tbsAuthTurnstilePromise = null;
        }

        if (window._tbsAuthTurnstilePromise && !forceReload) {
            return window._tbsAuthTurnstilePromise;
        }

        window._tbsAuthTurnstilePromise = new Promise(function(resolve, reject) {
            if (window.turnstile && typeof window.turnstile.render === 'function') {
                resolve(window.turnstile);
                return;
            }

            let settled = false;
            const existing = document.querySelector('script[src*="challenges.cloudflare.com/turnstile"]');
            if (!existing || forceReload) {
                const script = document.createElement('script');
                script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onAuthTurnstileLoaded&render=explicit' + (forceReload ? '&_ts=' + Date.now() : '');
                script.async = true;
                script.defer = true;
                script.setAttribute('data-tbs-turnstile-api', '1');
                script.onerror = function() {
                    if (!settled) {
                        settled = true;
                        window._tbsAuthTurnstilePromise = null;
                        reject(new Error('Turnstile script load error'));
                    }
                };
                document.head.appendChild(script);
            }

            const startedAt = Date.now();
            const poll = setInterval(function() {
                if (window.turnstile && typeof window.turnstile.render === 'function') {
                    clearInterval(poll);
                    if (!settled) {
                        settled = true;
                        resolve(window.turnstile);
                    }
                } else if (Date.now() - startedAt > 6000) {
                    clearInterval(poll);
                    if (!settled) {
                        settled = true;
                        window._tbsAuthTurnstilePromise = null;
                        reject(new Error('Turnstile script timeout'));
                    }
                }
            }, 100);
        });

        return window._tbsAuthTurnstilePromise;
    };

    window.tbsMountAuthTab = function(mode) {
        const container = document.getElementById('turnstile-' + mode + '-widget');
        if (window.tbsAuthTurnstileWidgets[mode] !== null && window.tbsHasMountedTurnstileDom(container)) {
            return;
        }
        window.tbsMountAuthWidget(mode, false);
        if (window.tbsAuthTurnstileTabTimers[mode]) {
            clearTimeout(window.tbsAuthTurnstileTabTimers[mode]);
        }
        window.tbsAuthTurnstileTabTimers[mode] = setTimeout(function() {
            window.tbsAuthTurnstileTabTimers[mode] = null;
            const currentContainer = document.getElementById('turnstile-' + mode + '-widget');
            if (window.tbsAuthTurnstileWidgets[mode] === null || !window.tbsHasMountedTurnstileDom(currentContainer)) {
                window.tbsMountAuthWidget(mode, false);
            }
        }, 60);
    };

    window.tbsMountAuthWidget = function(mode, forceRemount = false) {
        const container = document.getElementById('turnstile-' + mode + '-widget');
        const tokenInput = document.getElementById('turnstile-' + mode + '-token');
        if (!container) return;

        // Skip mounting if container is hidden (e.g. inside inactive tab)
        const isHidden = container.offsetParent === null && container.offsetWidth === 0;
        if (isHidden) {
            return;
        }

        // Prevent concurrent or duplicate mounts if an active widget already exists
        if (window.tbsAuthTurnstileMounting[mode]) {
            return;
        }
        const hasMountedDom = window.tbsHasMountedTurnstileDom(container);
        if (!forceRemount && window.tbsAuthTurnstileWidgets[mode] !== null && hasMountedDom) {
            return;
        }

        if (!window.turnstile || typeof window.turnstile.render !== 'function') {
            window.tbsEnsureAuthTurnstileScript(forceRemount)
                .then(function() {
                    window.tbsMountAuthWidget(mode, forceRemount);
                })
                .catch(function() {});
            return;
        }

        window.tbsAuthTurnstileMounting[mode] = true;

        try {
            const existingWidgetId = window.tbsAuthTurnstileWidgets[mode];
            if (existingWidgetId !== null && typeof window.turnstile.remove === 'function') {
                try {
                    window.turnstile.remove(existingWidgetId);
                } catch (e) {}
                window.tbsAuthTurnstileWidgets[mode] = null;
            }
            container.innerHTML = '';

            const sitekey = container.dataset.sitekey || '{{ $turnstileSiteKey }}';
            const actionName = container.dataset.action || ('auth_' + mode);
            const cdataVal = container.dataset.cdata || '';

            const widgetId = window.turnstile.render(container, {
                sitekey: sitekey,
                action: actionName,
                cData: cdataVal,
                language: 'nl',
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                appearance: 'always',
                retry: 'auto',
                'retry-interval': 1500,
                'refresh-expired': 'auto',
                'refresh-timeout': 'auto',
                callback: function(token) {
                    if (tokenInput) tokenInput.value = token || '';
                },
                'expired-callback': function() {
                    if (tokenInput) tokenInput.value = '';
                    setTimeout(function() {
                        window.resetAuthTurnstile(mode);
                    }, 300);
                },
                'error-callback': function() {
                    if (tokenInput) tokenInput.value = '';
                    setTimeout(function() {
                        window.resetAuthTurnstile(mode);
                    }, 1200);
                    return true;
                },
                'timeout-callback': function() {
                    if (tokenInput) tokenInput.value = '';
                    setTimeout(function() {
                        window.resetAuthTurnstile(mode);
                    }, 600);
                }
            });

            if (typeof widgetId !== 'undefined' && widgetId !== null) {
                window.tbsAuthTurnstileWidgets[mode] = widgetId;
                container.dataset.widgetId = String(widgetId);
            }
        } catch (err) {
            console.warn('Turnstile render notice:', err);
        } finally {
            window.tbsAuthTurnstileMounting[mode] = false;
        }
    };

    window.resetAuthTurnstile = function(mode) {
        const tokenInput = document.getElementById('turnstile-' + mode + '-token');
        if (tokenInput) tokenInput.value = '';

        const widgetId = window.tbsAuthTurnstileWidgets[mode];
        if (widgetId !== null && window.turnstile && typeof window.turnstile.reset === 'function') {
            try {
                window.turnstile.reset(widgetId);
                return;
            } catch (e) {}
        }

        if (widgetId !== null && window.turnstile && typeof window.turnstile.remove === 'function') {
            try {
                window.turnstile.remove(widgetId);
            } catch (e) {}
            window.tbsAuthTurnstileWidgets[mode] = null;
        }
        window.tbsMountAuthWidget(mode, true);
    };

    window.tbsInitAuthTurnstile = function(preferredMode) {
        if (preferredMode) {
            window.tbsMountAuthTab(preferredMode);
        } else {
            const isRegisterVisible = Boolean(document.getElementById('turnstile-register-widget')?.offsetParent);
            window.tbsMountAuthTab(isRegisterVisible ? 'register' : 'login');
        }
    };

    window.onAuthTurnstileLoaded = function() {
        window.tbsInitAuthTurnstile();
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.tbsInitAuthTurnstile();
    });
    document.addEventListener('turbo:before-render', function() {
        if (window.tbsAuthTurnstileTabTimers) {
            clearTimeout(window.tbsAuthTurnstileTabTimers.login);
            clearTimeout(window.tbsAuthTurnstileTabTimers.register);
        }
        window.tbsAuthTurnstileWidgets = { login: null, register: null };
        window.tbsAuthTurnstileMounting = { login: false, register: false };
        window.tbsAuthTurnstileTabTimers = { login: null, register: null };
    });
    document.addEventListener('turbo:load', function() {
        window.tbsInitAuthTurnstile();
    });
</script>
@endsection
