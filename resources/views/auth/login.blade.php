@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-6" x-data="{ tab: 'login' }">
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-[#241142] via-[#3a156b] to-[#241142] text-white rounded-xl shadow-lg p-6 sm:p-10 mb-8 border-2 border-purple-800 text-center relative overflow-hidden">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/20 text-amber-300 rounded-full border border-amber-500/40 text-xs font-bold uppercase tracking-wider mb-4">
                <i class="fa-solid fa-hospital"></i> De Enige Echte Nederlandse TBS Browsergame
            </div>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white uppercase drop-shadow">
                TBS <span class="text-amber-400">Kliniek</span>
            </h1>
            <p class="mt-3 text-sm sm:text-lg text-purple-200 max-w-2xl mx-auto font-medium">
                Respect verdien je. Frikandelbroodjes moet je bevechten. Overleef het strenge bewind van Directeur Gerda, train in de gym met Barry Biceps en bouw je eigen imperium op Paviljoen 7.
            </p>

            <!-- Quick Demo Login Button -->
            <div class="mt-6">
                <form method="POST" action="{{ route('login.demo') }}" class="inline-block">
                    @csrf
                    <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-purple-950 font-black text-sm sm:text-base px-6 py-3 rounded-lg shadow-md hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center gap-2 cursor-pointer border-2 border-amber-300">
                        <i class="fa-solid fa-play text-purple-900"></i>
                        <span>Direct Spelen als Demo-Personage (Kees Knakworst, Lvl 3)</span>
                    </button>
                </form>
                <p class="text-xs text-purple-300 mt-2 font-medium">Direct toegang met 1 klik zonder registratie</p>
            </div>
        </div>
    </div>

    <!-- Login & Register Split Card -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        <!-- Forms Column -->
        <div class="md:col-span-7 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b border-slate-200 bg-slate-50 text-sm font-bold">
                <button type="button" @click="tab = 'login'" :class="tab === 'login' ? 'bg-white text-purple-900 border-b-2 border-purple-900' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-3 px-4 text-center transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Inloggen</span>
                </button>
                <button type="button" @click="tab = 'register'" :class="tab === 'register' ? 'bg-white text-purple-900 border-b-2 border-purple-900' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-3 px-4 text-center transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Nieuw Dossier (Registreren)</span>
                </button>
            </div>

            <!-- Login Form -->
            <div x-show="tab === 'login'" class="p-6">
                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">E-mailadres</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" value="{{ old('email', 'demo@tbskliniek.nl') }}" required class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase mb-1">Wachtwoord</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" value="wachtwoord123" required class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-purple-900 focus:ring-purple-600">
                            <span>Sessie onthouden</span>
                        </label>
                        <span class="text-slate-400">Standaard: wachtwoord123</span>
                    </div>

                    <button type="submit" class="w-full bg-[#3a156b] hover:bg-[#280c4f] text-white font-black py-2.5 px-4 rounded text-sm shadow transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-door-open"></i>
                        <span>Binnentreden in de Kliniek</span>
                    </button>
                </form>
            </div>

            <!-- Register Form -->
            <div x-show="tab === 'register'" x-cloak class="p-6">
                <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="reg_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Volledige Naam</label>
                        <input type="text" id="reg_name" name="name" value="{{ old('name') }}" placeholder="Bijv. Jan Janssen" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 outline-none">
                    </div>

                    <div>
                        <label for="reg_char" class="block text-xs font-bold text-slate-700 uppercase mb-1">Personagenaam (Bijnaam in de Kliniek)</label>
                        <input type="text" id="reg_char" name="character_name" value="{{ old('character_name') }}" placeholder="Bijv. Snelle Eddy" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 outline-none">
                    </div>

                    <div>
                        <label for="reg_email" class="block text-xs font-bold text-slate-700 uppercase mb-1">E-mailadres</label>
                        <input type="email" id="reg_email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="reg_pass" class="block text-xs font-bold text-slate-700 uppercase mb-1">Wachtwoord</label>
                            <input type="password" id="reg_pass" name="password" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 outline-none">
                        </div>
                        <div>
                            <label for="reg_confirm" class="block text-xs font-bold text-slate-700 uppercase mb-1">Bevestigen</label>
                            <input type="password" id="reg_confirm" name="password_confirmation" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-purple-950 font-black py-2.5 px-4 rounded text-sm shadow transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-pen"></i>
                        <span>Dossier Aanmaken & Starten</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Features Info Column -->
        <div class="md:col-span-5 space-y-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-black text-purple-950 uppercase tracking-wide flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-circle-info text-amber-500"></i>
                    <span>Wat staat je te wachten?</span>
                </h3>
                <ul class="space-y-3 text-xs text-slate-600">
                    <li class="flex items-start gap-2.5">
                        <span class="w-6 h-6 rounded bg-purple-100 text-purple-900 flex items-center justify-center shrink-0 mt-0.5 font-bold">
                            <i class="fa-solid fa-dumbbell"></i>
                        </span>
                        <div>
                            <strong class="text-slate-800">Trainen & Levelen:</strong> Werk aan je kracht, conditie, intelligentie en discipline om sterker te worden.
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-6 h-6 rounded bg-purple-100 text-purple-900 flex items-center justify-center shrink-0 mt-0.5 font-bold">
                            <i class="fa-solid fa-coins"></i>
                        </span>
                        <div>
                            <strong class="text-slate-800">KliniekCash (KC):</strong> Verdien geld met missies, zet het veilig op de bank of zakrol je mede-patiënten.
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-6 h-6 rounded bg-purple-100 text-purple-900 flex items-center justify-center shrink-0 mt-0.5 font-bold">
                            <i class="fa-solid fa-users"></i>
                        </span>
                        <div>
                            <strong class="text-slate-800">Sluit je aan bij Gangs:</strong> Vecht voor De Kantinebende, De Paarse Plofkippen of De IJzeren Halters.
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-6 h-6 rounded bg-purple-100 text-purple-900 flex items-center justify-center shrink-0 mt-0.5 font-bold">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <div>
                            <strong class="text-slate-800">Isoleercel & Ontsnappen:</strong> Betrapt bij diefstal? Probeer te ontsnappen via de luchtschacht!
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Server Status Card -->
            <div class="bg-purple-950 text-white p-4 rounded-xl border border-purple-800 text-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-amber-300 uppercase tracking-wider text-[11px]">Kliniek Status</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800 font-bold text-[10px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Live op Localhost
                    </span>
                </div>
                <p class="text-purple-200 text-[11px] leading-relaxed">
                    Alle game-mechanics draaien op Laravel 12 met SQLite & transactionele database-locks.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
