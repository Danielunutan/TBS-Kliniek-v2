@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-4 sm:py-8" x-data="{ tab: 'login' }">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[580px]">
        <!-- Left Hero Column (Matches top-left image) -->
        <div class="lg:col-span-7 bg-[#1c0b38] relative flex flex-col justify-between p-8 sm:p-12 text-white overflow-hidden">
            <!-- Background Image with Overlay -->
            <img src="{{ asset('assets/illustrations/login_hero.jpg') }}" alt="TBS Kliniek" class="absolute inset-0 w-full h-full object-cover object-center opacity-45 mix-blend-luminosity hover:opacity-55 transition-opacity duration-700">
            <div class="absolute inset-0 bg-gradient-to-t from-[#140628] via-[#1c0b38]/70 to-[#270e4e]/40"></div>

            <!-- Top Branding -->
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[11px] font-black uppercase tracking-wider mb-4 shadow-sm backdrop-blur-xs">
                    <i class="fa-solid fa-crown text-amber-400"></i>
                    <span>Klassieke Nederlandse Browsergame</span>
                </div>
                
                <h1 class="text-4xl sm:text-6xl font-black uppercase tracking-tight text-white drop-shadow-xl leading-none">
                    TBS <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-200">KLINIEK</span>
                </h1>
                
                <p class="mt-4 text-base sm:text-lg font-medium text-purple-200/90 leading-relaxed max-w-md drop-shadow">
                    Respect verdien je. Frikandelbroodjes moet je bevechten.
                </p>
            </div>

            <!-- Bottom Flavor Quote -->
            <div class="relative z-10 pt-12">
                <div class="bg-purple-950/80 border border-purple-800/80 rounded-2xl p-4 backdrop-blur-md max-w-md">
                    <p class="text-xs text-purple-200 italic leading-relaxed">
                        "In de kliniek draait alles om macht, aanzien en wie de sleutel heeft van de warmhoudkast."
                    </p>
                    <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest block mt-2">— Henkie de Frikandelkoning</span>
                </div>
            </div>
        </div>

        <!-- Right Form Column (Matches right card in top-left mockup) -->
        <div class="lg:col-span-5 p-8 sm:p-10 flex flex-col justify-center bg-white">
            <!-- Tab Switcher -->
            <div class="flex border-b border-slate-200 mb-8">
                <button type="button" @click="tab = 'login'" :class="tab === 'login' ? 'border-b-2 border-purple-700 text-purple-900 font-black' : 'text-slate-400 hover:text-slate-700 font-semibold'" class="flex-1 pb-3 text-center text-sm transition">
                    Inloggen
                </button>
                <button type="button" @click="tab = 'register'" :class="tab === 'register' ? 'border-b-2 border-purple-700 text-purple-900 font-black' : 'text-slate-400 hover:text-slate-700 font-semibold'" class="flex-1 pb-3 text-center text-sm transition">
                    Registreren
                </button>
            </div>

            <!-- Login Form -->
            <div x-show="tab === 'login'">
                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf
                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email', 'demo@tbskliniek.nl') }}" required placeholder="Gebruikersnaam / E-mail" class="w-full pl-10 pr-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none transition">
                        </div>
                    </div>

                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="password" value="wachtwoord123" required placeholder="Wachtwoord" class="w-full pl-10 pr-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-600 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" name="remember" checked class="rounded text-purple-700 focus:ring-purple-600">
                            <span>Onthoud mij</span>
                        </label>
                        <a href="#" class="text-purple-700 hover:underline font-semibold">Wachtwoord vergeten?</a>
                    </div>

                    <button type="submit" class="w-full bg-[#6d28d9] hover:bg-[#5b21b6] text-white font-black py-3 px-4 rounded-xl text-sm shadow-md shadow-purple-900/20 transition-all cursor-pointer">
                        Inloggen
                    </button>
                </form>

                <!-- Quick 1-Click Demo Login -->
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <form method="POST" action="{{ route('login.demo') }}">
                        @csrf
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-purple-950 font-black py-3 px-4 rounded-xl text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-play"></i>
                            <span>Direct Spelen als Henkie (Level 12)</span>
                        </button>
                    </form>
                    <p class="text-center text-[11px] text-slate-400 mt-2 font-medium">1-klik toegang tot de speelbare localhost demo</p>
                </div>
            </div>

            <!-- Register Form -->
            <div x-show="tab === 'register'" x-cloak>
                <form method="POST" action="{{ route('register.post') }}" class="space-y-3">
                    @csrf
                    <div>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Volledige Naam" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div>
                        <input type="text" name="character_name" value="{{ old('character_name') }}" placeholder="Bijnaam in de Kliniek" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="E-mailadres" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div>
                        <input type="password" name="password" placeholder="Wachtwoord" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div>
                        <input type="password" name="password_confirmation" placeholder="Wachtwoord Bevestigen" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:ring-2 focus:ring-purple-600">
                    </div>
                    <button type="submit" class="w-full bg-[#6d28d9] hover:bg-[#5b21b6] text-white font-black py-3 px-4 rounded-xl text-xs shadow-md transition-all cursor-pointer mt-2">
                        Dossier Aanmaken & Starten
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
