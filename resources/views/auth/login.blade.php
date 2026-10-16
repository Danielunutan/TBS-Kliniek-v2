@extends('layouts.app')

@section('title', 'TBS Kliniek - Het #1 Misdaad & Gevangenis Spel van Nederland')
@section('meta_description', 'Meld je gratis aan bij TBS Kliniek: hét populairste Nederlandse misdaad en gevangenis RPG browsergame van 2026! Kraak medicijnkluizen, leid je gang en overleef de afdeling.')
@section('meta_keywords', 'TBS Kliniek, boeven spel, gevangenis browsergame, misdaad rpg nederland, tekstbased rpg, gratis browsergame spelen')

@section('content')
<div class="max-w-6xl xl:max-w-7xl mx-auto space-y-6" x-data="{ 
    tab: '{{ request()->routeIs('register') || request('tab') === 'register' || $errors->has('username') || $errors->has('terms') || $errors->has('password_confirmation') || old('username') || request()->has('ref') || session()->has('referral_name') ? 'register' : 'login' }}', 
    regGender: '{{ old('gender', 'male') }}', 
    regAvatar: '{{ old('avatar', 'assets/characters/avatar_male_1.jpg') }}' 
}">

    <!-- MAIN LOGIN / REGISTER CARD (EXPANSIVE CLEAN SHOWCASE) -->
    <div id="auth-form-card" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden grid grid-cols-1 lg:grid-cols-12">
        
        <!-- LEFT HERO COLUMN (WIDE CLEAN TBS KLINIEK ARTWORK & CHARACTERS) -->
        <div class="lg:col-span-7 xl:col-span-7 bg-slate-950 text-white relative p-6 sm:p-8 flex flex-col justify-between overflow-hidden shadow-inner min-h-[500px] lg:min-h-[620px]">
            <!-- Crisp High Quality Illustration Lineup (All 5 Characters Fully Visible) -->
            <img src="{{ asset('assets/illustrations/tbs_crew_lineup.jpg') }}?v={{ file_exists(public_path('assets/illustrations/tbs_crew_lineup.jpg')) ? filemtime(public_path('assets/illustrations/tbs_crew_lineup.jpg')) : '3' }}" 
                 alt="TBS Kliniek - Het #1 Misdaad Spel van Nederland" 
                 class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none">
            
            <!-- Subtle Vignette Gradients for Legibility without Obscuring Characters -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-transparent to-slate-950/40 pointer-events-none"></div>

            <!-- Top Floating Live Status Bar -->
            <div class="relative z-10 flex items-center justify-between gap-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-950/80 border border-purple-400/40 text-purple-200 text-xs font-black uppercase tracking-wider backdrop-blur-md shadow-md">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>{{ number_format($totalPlayers, 0, ',', '.') }} Patiënten Geregistreerd</span>
                </div>
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-950/80 border border-amber-400/40 text-amber-300 text-xs font-black uppercase tracking-wider backdrop-blur-md shadow-md">
                    <i class="fa-solid fa-coins text-amber-400"></i>
                    <span>€{{ number_format($totalCash, 0, ',', '.') }} Buit</span>
                </div>
            </div>

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

        <!-- RIGHT FORM COLUMN (CLEAN WHITE TO MATCH THE WEBSITE) -->
        <div class="lg:col-span-5 xl:col-span-5 p-6 sm:p-10 flex flex-col justify-center bg-white">
            <div class="w-full max-w-md mx-auto space-y-5">
                
                <!-- Header -->
                <div class="text-center sm:text-left">
                    <div class="lg:hidden flex justify-center mb-3">
                        <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek Logo" class="w-14 h-14 object-contain drop-shadow-md">
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Welkom bij de Kliniek</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Meld je aan voor je dossier of start direct.</p>
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
                    <div class="border-t border-slate-200 w-full"></div>
                    <span class="bg-white px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 shrink-0">
                        of met e-mail
                    </span>
                    <div class="border-t border-slate-200 w-full"></div>
                </div>

                <!-- MODERN PILL TAB SWITCHER (MATCHES SITE TABS) -->
                <div class="bg-slate-100 p-1 rounded-xl flex gap-1">
                    <button type="button" @click="tab = 'login'"
                            :class="tab === 'login' ? 'bg-white text-slate-900 font-extrabold shadow-2xs' : 'text-slate-500 hover:text-slate-900 font-bold'"
                            class="flex-1 py-2 text-xs rounded-lg transition text-center cursor-pointer">
                        Inloggen
                    </button>
                    <button type="button" @click="tab = 'register'"
                            :class="tab === 'register' ? 'bg-white text-slate-900 font-extrabold shadow-2xs' : 'text-slate-500 hover:text-slate-900 font-bold'"
                            class="flex-1 py-2 text-xs rounded-lg transition text-center cursor-pointer">
                        Registreren
                    </button>
                </div>

                <!-- LOGIN TAB (NO UGLY LABELS - MODERN CLEAN INPUTS) -->
                <div x-show="tab === 'login'" class="space-y-4">
                    <form method="POST" action="{{ route('login.post') }}" class="space-y-3">
                        @csrf

                        <!-- Email Input -->
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="E-mailadres / Dossiernummer"
                                   class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition placeholder:text-slate-400">
                        </div>

                        <!-- Password Input with Eye Toggle -->
                        <div class="relative" x-data="{ showPass: false }">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </span>
                            <input :type="showPass ? 'text' : 'password'" name="password" required
                                   placeholder="Wachtwoord"
                                   class="w-full pl-9 pr-10 py-2.5 text-xs sm:text-sm font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition placeholder:text-slate-400">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="fa-solid text-xs" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>

                        <!-- Options -->
                        <div class="flex items-center justify-between text-xs text-slate-500 pt-0.5">
                            <label class="flex items-center gap-2 cursor-pointer font-medium hover:text-slate-700">
                                <input type="checkbox" name="remember" checked class="w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span>Sessie onthouden</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-bold transition">Wachtwoord kwijt?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-black py-3 px-4 rounded-xl text-xs sm:text-sm shadow-md transition active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-right-to-bracket text-xs"></i>
                            <span>Inloggen op Dossier</span>
                        </button>
                    </form>
                </div>

                <!-- REGISTER TAB (SLEEK ONBOARDING: USERNAME, EMAIL, PASSWORD, AVATAR & TERMS) -->
                <div x-show="tab === 'register'" x-cloak class="space-y-4">
                    
                    @if($errors->any() && (old('username') || old('terms')))
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                            <span>Inschrijving niet gelukt:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-600">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('register.post') }}" class="space-y-3.5">
                        @csrf

                        @php
                            $activeRef = request('ref', session('referral_name'));
                        @endphp
                        @if($activeRef)
                        <input type="hidden" name="ref" value="{{ $activeRef }}">
                        <div class="p-3 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 text-xs flex items-center gap-2.5 shadow-2xs">
                            <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center text-sm shrink-0">
                                <i class="fa-solid fa-gift"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="font-extrabold block truncate">Uitgenodigd door: {{ $activeRef }}</span>
                                <span class="text-[11px] text-purple-700">Meld je aan en begin direct samen in de TBS Kliniek!</span>
                            </div>
                        </div>
                        @endif

                        <!-- 1. ACCOUNT FIELDS -->
                        <div class="space-y-2.5">
                            <!-- Gebruikersnaam / Alias -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-user-ninja text-xs"></i>
                                </span>
                                <input type="text" name="username" value="{{ old('username') }}" placeholder="Gebruikersnaam / Kliniek Alias" required minlength="3" maxlength="25"
                                       class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition placeholder:text-slate-400">
                            </div>

                            <!-- Email -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-envelope text-xs"></i>
                                </span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="E-mailadres" required
                                       class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition placeholder:text-slate-400">
                            </div>

                            <!-- Wachtwoord & Bevestigen (Strak naast elkaar of compact onder elkaar) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" x-data="{ showRegPass: false }">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-lock text-xs"></i>
                                    </span>
                                    <input :type="showRegPass ? 'text' : 'password'" name="password" placeholder="Wachtwoord (min. 6)" required
                                           class="w-full pl-9 pr-8 py-2.5 text-xs sm:text-sm font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition placeholder:text-slate-400">
                                    <button type="button" @click="showRegPass = !showRegPass" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <i class="fa-solid text-[11px]" :class="showRegPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-shield-halved text-xs"></i>
                                    </span>
                                    <input :type="showRegPass ? 'text' : 'password'" name="password_confirmation" placeholder="Herhaal Wachtwoord" required
                                           class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition placeholder:text-slate-400">
                                </div>
                            </div>
                        </div>

                        <!-- 2. KARAKTER ONBOARDING (GESLACHT & AVATAR KIEZEN) -->
                        <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-id-card text-indigo-600"></i>
                                    <span>Patiëntenpas Onboarding</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Stap 1 van 1</span>
                            </div>

                            <!-- Geslacht Keuze -->
                            <div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="regGender = 'male'; if(!regAvatar.includes('male')) regAvatar = 'assets/characters/avatar_male_1.jpg'"
                                            :class="regGender === 'male' ? 'bg-white border-purple-600 text-purple-950 font-extrabold shadow-xs ring-1 ring-purple-600/20' : 'bg-slate-100/70 border-slate-200 text-slate-500 font-bold hover:bg-white'"
                                            class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl border text-xs transition cursor-pointer">
                                        <i class="fa-solid fa-mars text-purple-600 text-sm"></i>
                                        <span>Man</span>
                                    </button>
                                    <button type="button" @click="regGender = 'female'; if(!regAvatar.includes('female')) regAvatar = 'assets/characters/avatar_female_1.jpg'"
                                            :class="regGender === 'female' ? 'bg-white border-purple-600 text-purple-950 font-extrabold shadow-xs ring-1 ring-purple-600/20' : 'bg-slate-100/70 border-slate-200 text-slate-500 font-bold hover:bg-white'"
                                            class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl border text-xs transition cursor-pointer">
                                        <i class="fa-solid fa-venus text-purple-600 text-sm"></i>
                                        <span>Vrouw</span>
                                    </button>
                                </div>
                                <input type="hidden" name="gender" :value="regGender">
                            </div>

                            <!-- Avatar Gallery (6 Man, 6 Vrouw - Paarse Stijl - Zonder Namen) -->
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kies jouw profielfoto</span>
                                
                                <!-- Mannelijke Avatars (6) -->
                                <div x-show="regGender === 'male'" class="grid grid-cols-6 gap-1.5 sm:gap-2">
                                    @foreach([
                                        'assets/characters/avatar_male_1.jpg',
                                        'assets/characters/avatar_male_2.jpg',
                                        'assets/characters/avatar_male_3.jpg',
                                        'assets/characters/avatar_male_4.jpg',
                                        'assets/characters/avatar_male_5.jpg',
                                        'assets/characters/avatar_male_6.jpg',
                                    ] as $avFile)
                                    <button type="button" @click="regAvatar = '{{ $avFile }}'"
                                            :class="regAvatar === '{{ $avFile }}' ? 'border-purple-600 ring-2 ring-purple-600/30 bg-purple-50/50 shadow-xs scale-105' : 'border-slate-200 bg-white hover:border-slate-300 opacity-80 hover:opacity-100'"
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
                                <div x-show="regGender === 'female'" class="grid grid-cols-6 gap-1.5 sm:gap-2">
                                    @foreach([
                                        'assets/characters/avatar_female_1.jpg',
                                        'assets/characters/avatar_female_2.jpg',
                                        'assets/characters/avatar_female_3.jpg',
                                        'assets/characters/avatar_female_4.jpg',
                                        'assets/characters/avatar_female_5.jpg',
                                        'assets/characters/avatar_female_6.jpg',
                                    ] as $avFile)
                                    <button type="button" @click="regAvatar = '{{ $avFile }}'"
                                            :class="regAvatar === '{{ $avFile }}' ? 'border-purple-600 ring-2 ring-purple-600/30 bg-purple-50/50 shadow-xs scale-105' : 'border-slate-200 bg-white hover:border-slate-300 opacity-80 hover:opacity-100'"
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
                            <label class="flex items-start gap-2.5 cursor-pointer text-xs text-slate-600 group select-none">
                                <input type="checkbox" name="terms" value="1" required class="w-4 h-4 mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                <span class="leading-snug group-hover:text-slate-900 transition text-[11px] sm:text-xs">
                                    Ik ga akkoord met de <a href="{{ route('legal.terms') }}" target="_blank" class="text-indigo-600 font-extrabold hover:underline">algemene voorwaarden</a> en het <a href="{{ route('legal.privacy') }}" target="_blank" class="text-indigo-600 font-extrabold hover:underline">privacybeleid</a>.
                                </span>
                            </label>
                        </div>

                        <!-- 4. SUBMIT BUTTON -->
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-3 px-4 rounded-xl text-xs sm:text-sm shadow-xs transition active:scale-[0.99] flex items-center justify-center gap-2">
                            <i class="fa-solid fa-user-plus text-xs"></i>
                            <span>Dossier Aanmaken & Starten</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

    <!-- 3 SUBTLE CLEAN FEATURE CARDS (MATCHES DASHBOARD / SITE CLEANLINESS) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <h3 class="text-xs font-extrabold text-slate-900">Hosselen & Kraken</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Steel frikandelbroodjes en kraak kluisjes voor cash.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-hand-fist"></i>
            </div>
            <div>
                <h3 class="text-xs font-extrabold text-slate-900">Luchtplaats PvP</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Vecht tegen rivalen om respect en sticht je eigen bende.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-dice"></i>
            </div>
            <div>
                <h3 class="text-xs font-extrabold text-slate-900">Ondergronds Casino</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Roulette, fruitautomaten en nummerspel met hoge jackpots.</p>
            </div>
        </div>
    </div>

</div>
@endsection
