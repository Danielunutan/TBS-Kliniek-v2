@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Header Card -->
    <div class="bg-slate-900 border border-purple-500/30 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-600/30 border border-purple-400/40 flex items-center justify-center text-2xl text-purple-300 shrink-0">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight">Account & Beveiliging</h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-0.5">
                        Beheer je inloggegevens, tweestapsverificatie, gebruikersnaam en actieve sessies op afstand.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 rounded-full bg-white/10 text-xs font-mono font-bold text-purple-300 border border-white/10">
                    {{ $user->email }}
                </span>
            </div>
        </div>
    </div>

    <!-- Grid Sections -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 1. USERNAME CHANGE (1x per 6 months) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-signature text-purple-600"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Patiëntnaam / Username</h3>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full {{ $canChangeUsername ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $canChangeUsername ? 'Beschikbaar' : 'Vergrendeld' }}
                </span>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Je roepnaam in de TBS Kliniek. Om identiteitsfraude en verwarring te voorkomen mag je je naam <strong>maximaal 1x per 6 maanden</strong> wijzigen.
            </p>

            @if($canChangeUsername)
                <form action="{{ route('settings.username.update') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Nieuwe Gebruikersnaam</label>
                        <input type="text" name="name" value="{{ old('name', $character->name) }}" required maxlength="25"
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white">
                        <span class="text-[11px] text-slate-400 mt-1 block">Min 3, max 25 tekens (letters, cijfers, streepjes).</span>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-check mr-1.5"></i> Naam Nu Wijzigen
                    </button>
                </form>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 space-y-2">
                    <div class="flex items-center gap-2 text-amber-900 text-xs font-extrabold">
                        <i class="fa-solid fa-lock text-amber-600"></i>
                        <span>Naamswijziging is tijdelijk vergrendeld</span>
                    </div>
                    <p class="text-xs text-amber-800">
                        Je hebt je naam gewijzigd op <strong>{{ $character->username_changed_at->format('d-m-Y H:i') }}</strong>.
                        Je kunt pas over <strong class="font-mono text-amber-950">{{ $daysUntilChange }} dagen</strong> weer een nieuwe naam kiezen.
                    </p>
                </div>
            @endif
        </div>

        <!-- 2. TWO-FACTOR AUTHENTICATION (2FA) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-purple-600"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">2-Factor Authenticatie</h3>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full {{ $user->two_factor_enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $user->two_factor_enabled ? 'Ingeschakeld' : 'Uitgeschakeld' }}
                </span>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Beveilig je account extra met een tweede verificatiestap. Hierdoor kan niemand zonder jouw toestemming inloggen op je TBS profiel.
            </p>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between gap-4">
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Status van 2FA</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        {{ $user->two_factor_enabled ? 'Je account is optimaal beschermd tegen overnames.' : 'Schakel 2FA in voor extra veiligheid.' }}
                    </p>
                </div>

                <form action="{{ route('settings.2fa.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl text-xs font-extrabold uppercase tracking-wider transition cursor-pointer {{ $user->two_factor_enabled ? 'bg-rose-100 text-rose-800 hover:bg-rose-200' : 'bg-purple-600 text-white hover:bg-purple-700' }}">
                        {{ $user->two_factor_enabled ? 'Uitschakelen' : 'Inschakelen' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- 3. CHANGE PASSWORD -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-key text-purple-600"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Wachtwoord Wijzigen</h3>
                </div>
            </div>

            <form action="{{ route('settings.password.update') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Huidig Wachtwoord</label>
                    <input type="password" name="current_password" required
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Nieuw Wachtwoord</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Bevestig Nieuw Wachtwoord</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-lock mr-1.5"></i> Wachtwoord Opslaan
                </button>
            </form>
        </div>

        <!-- 4. ACTIVE SESSIONS & REMOTE LOGOUT -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen-button text-purple-600"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Actieve Sessies & Afstand</h3>
                </div>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Overzicht van apparaten waarop je momenteel bent ingelogd. Ben je vergeten uit te loggen op een andere computer of telefoon? Log direct alle andere apparaten op afstand uit.
            </p>

            <div class="space-y-2.5">
                @forelse($sessions as $sess)
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid {{ $sess['device'] === 'Mobiele Telefoon' ? 'fa-mobile-screen' : 'fa-laptop' }} text-purple-600 text-base"></i>
                        <div>
                            <div class="font-extrabold text-slate-900 flex items-center gap-1.5">
                                <span>{{ $sess['device'] }}</span>
                                @if($sess['is_current'])
                                    <span class="bg-emerald-100 text-emerald-800 text-[9px] font-black uppercase px-2 py-0.5 rounded-full">Dit apparaat</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">
                                IP: {{ $sess['ip'] }} • {{ $sess['last_active'] }}
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>Huidig apparaat actief.</span>
                </div>
                @endforelse
            </div>

            <!-- Remote Logout Form -->
            <form action="{{ route('settings.sessions.logout-others') }}" method="POST" class="pt-2 border-t border-slate-100 space-y-2">
                @csrf
                <label class="text-[11px] font-bold text-slate-600 block">Vul je wachtwoord in om andere apparaten te verbreken:</label>
                <div class="flex items-center gap-2">
                    <input type="password" name="password" placeholder="Jouw wachtwoord" required
                           class="flex-1 bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-purple-600">
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs uppercase tracking-wider transition cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i> Overal Uitloggen
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
