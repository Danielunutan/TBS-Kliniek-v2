@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Character Profile Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-[#241142] via-[#3a156b] to-[#241142] p-6 text-white flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <!-- Avatar -->
                <div class="w-20 h-20 rounded-xl bg-purple-900 border-2 border-amber-400 flex items-center justify-center text-amber-300 text-3xl font-black shadow-md shrink-0">
                    <i class="fa-solid fa-user-ninja"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl font-black tracking-wide text-white">{{ $character->name }}</h1>
                        <span class="px-2 py-0.5 rounded bg-amber-500 text-purple-950 font-black text-xs uppercase tracking-wider">Level {{ $character->level }}</span>
                        @if($character->gang)
                        <span class="px-2 py-0.5 rounded bg-purple-950 text-purple-200 font-bold text-xs border border-purple-800">
                            [{{ $character->gang->tag }}] {{ $character->gang->name }}
                        </span>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-purple-200 mt-0.5">Titel: <span class="text-amber-300">{{ $character->title }}</span></p>
                    <p class="text-xs text-purple-300 italic mt-2 bg-purple-950/60 py-1 px-3 rounded border border-purple-800/60 inline-block">
                        <i class="fa-solid fa-quote-left text-[10px] mr-1 text-amber-400"></i>{{ $character->status_message }}
                    </p>
                </div>
            </div>

            <!-- Quick Action Shortcuts -->
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
                <a href="{{ route('missions.index') }}" class="bg-amber-500 hover:bg-amber-400 text-purple-950 font-black px-4 py-2 rounded text-xs shadow flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-bullseye"></i> Missie Doen
                </a>
                <a href="{{ route('training.index') }}" class="bg-purple-800 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded text-xs shadow border border-purple-600 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-dumbbell"></i> Trainen
                </a>
                <a href="{{ route('theft.index') }}" class="bg-purple-800 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded text-xs shadow border border-purple-600 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-mask"></i> Zakrollen
                </a>
            </div>
        </div>

        <!-- Level XP Progress Bar Full Width -->
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 text-xs">
            <div class="flex justify-between items-center mb-1 font-semibold text-slate-700">
                <span>Voortgang naar Level {{ $character->level + 1 }}</span>
                <span><strong class="text-purple-900">{{ number_format($character->xp, 0, ',', '.') }}</strong> / {{ number_format($nextLevelXp, 0, ',', '.') }} XP ({{ $xpPercentage }}%)</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden shadow-inner">
                <div class="bg-gradient-to-r from-purple-700 to-amber-500 h-full transition-all duration-300" style="width: {{ $xpPercentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- 4 Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Currencies -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">KliniekCash</span>
                <p class="text-base font-black text-slate-800">{{ number_format($character->cash, 0, ',', '.') }} KC <span class="text-xs font-normal text-slate-500">contant</span></p>
                <p class="text-xs text-emerald-700 font-semibold">{{ number_format($character->bank, 0, ',', '.') }} KC <span class="text-slate-400 font-normal">op bank</span></p>
            </div>
        </div>

        <!-- Card 2: Energy & Regen -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-yellow-50 text-yellow-600 border border-yellow-200 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Energie</span>
                <p class="text-base font-black text-slate-800">{{ $character->energy }} / {{ $character->max_energy }}</p>
                <p class="text-xs text-slate-500 font-medium">+1 energie per 5 minuten</p>
            </div>
        </div>

        <!-- Card 3: Attributes -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-hand-fist"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Kracht & Weerstand</span>
                <p class="text-xs font-bold text-slate-800">Kracht: <span class="text-purple-900 font-black">{{ $character->strength }}</span> | Verd: <span class="text-purple-900 font-black">{{ $character->defense }}</span></p>
                <p class="text-xs text-slate-500">Conditie: {{ $character->stamina }} | Intel: {{ $character->intelligence }}</p>
            </div>
        </div>

        <!-- Card 4: Record -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-award"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Reputatie & Misdaden</span>
                <p class="text-base font-black text-indigo-900">{{ $character->respect }} Respect</p>
                <p class="text-xs text-slate-500 font-medium">{{ $character->thefts_succeeded }} diefstallen / {{ $character->missions_completed }} missies</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Quick Missions -->
        <div class="lg:col-span-7 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#241142] text-white px-5 py-3 flex items-center justify-between border-b border-purple-900">
                <h2 class="text-sm font-black uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-bullseye text-amber-400"></i>
                    <span>Beschikbare Dagelijkse Missies</span>
                </h2>
                <a href="{{ route('missions.index') }}" class="text-xs text-amber-300 hover:text-white font-bold">
                    Alle missies bekijken &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($quickMissions as $m)
                <div class="p-4 hover:bg-slate-50 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-sm">{{ $m->title }}</span>
                            <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-900 font-bold text-[10px]">Lvl {{ $m->required_level }}</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $m->description }}</p>
                        <div class="flex items-center gap-3 text-[11px] text-slate-500 font-medium">
                            <span class="text-yellow-700"><i class="fa-solid fa-bolt text-yellow-500"></i> {{ $m->energy_cost }} Energie</span>
                            <span class="text-amber-700"><i class="fa-solid fa-coins text-amber-500"></i> {{ $m->min_cash_reward }}-{{ $m->max_cash_reward }} KC</span>
                            <span class="text-purple-700"><i class="fa-solid fa-star text-purple-500"></i> +{{ $m->xp_reward }} XP</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('missions.execute', $m) }}" class="shrink-0 w-full sm:w-auto">
                        @csrf
                        <button type="submit" @if($character->energy < $m->energy_cost || $character->level < $m->required_level || $character->isImprisoned()) disabled @endif class="w-full sm:w-auto bg-amber-500 hover:bg-amber-400 disabled:opacity-50 disabled:cursor-not-allowed text-purple-950 font-black px-4 py-2 rounded text-xs shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-play"></i> Uitvoeren
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Notifications & Recent Guestbook -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Notifications Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase text-slate-800 tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-bell text-purple-700"></i>
                        <span>Recente Meldingen</span>
                    </h3>
                    <span class="text-[11px] text-slate-500 font-bold">{{ $notifications->count() }} meldingen</span>
                </div>
                <div class="divide-y divide-slate-100 max-h-64 overflow-y-auto">
                    @forelse($notifications as $notif)
                    <div class="p-3 text-xs hover:bg-slate-50 transition flex items-start gap-3">
                        <span class="w-7 h-7 rounded bg-purple-100 text-purple-900 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid {{ $notif->icon }}"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800">{{ $notif->title }}</p>
                            <p class="text-slate-600 text-[11px] mt-0.5">{{ $notif->message }}</p>
                            <span class="text-[10px] text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-xs text-slate-500">
                        Geen nieuwe meldingen.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Guestbook Preview -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase text-slate-800 tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-book-open text-purple-700"></i>
                        <span>Gastenboek op je Dossier</span>
                    </h3>
                    <a href="{{ route('profile.me') }}" class="text-xs text-purple-800 hover:underline font-bold">
                        Bekijken &rarr;
                    </a>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($recentGuestbook as $entry)
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/80 text-xs">
                        <div class="flex items-center justify-between mb-1">
                            <a href="{{ route('profile.show', $entry->author->name) }}" class="font-bold text-purple-900 hover:underline">
                                {{ $entry->author->name }}
                            </a>
                            <span class="text-[10px] text-slate-400">{{ $entry->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-700 text-xs italic">"{{ $entry->message }}"</p>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 text-center py-2">Nog geen gastenboekberichten achtergelaten.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
