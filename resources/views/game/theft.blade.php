@extends('layouts.app')

@section('title', 'Diefstal & Klusjes - TBS Kliniek')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{
    isolationModalOpen: false,
    pendingForm: null,
    targetName: '',
    hasIsolation: {{ $character->hasIsolationProtection() ? 'true' : 'false' }},
    handleTheft(event, name) {
        if (this.hasIsolation) {
            event.preventDefault();
            this.pendingForm = event.target;
            this.targetName = name;
            this.isolationModalOpen = true;
            return false;
        }
        return true;
    },
    confirmBreakAndTheft() {
        if (!this.pendingForm) return;
        let hidden = this.pendingForm.querySelector('input[name=confirm_break_isolation]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'confirm_break_isolation';
            this.pendingForm.appendChild(hidden);
        }
        hidden.value = '1';
        this.isolationModalOpen = false;
        this.hasIsolation = false;
        this.pendingForm.submit();
    }
}">

    @if($character->hasIsolationProtection())
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-xs">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="text-xs font-black text-amber-950 dark:text-amber-200 uppercase tracking-wider flex items-center gap-1.5">
                    <span>Vrijwillige Isolatie Actief</span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-200/80 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200 text-[10px]">100% Beschermd</span>
                </div>
                <p class="text-xs text-amber-900 dark:text-amber-300 mt-0.5">
                    Je cel is vergrendeld tot <strong>{{ $character->protection_until?->format('d-m H:i') }}</strong>. Als je iemand berooft, vraagt het spel eerst een bevestiging en vervalt je isolatie.
                </p>
            </div>
        </div>
        <a href="{{ route('isolation.index') }}" class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shrink-0 transition shadow-xs">
            Bekijk Isolatie &raquo;
        </a>
    </div>
    @endif

    @if($character->isImprisoned())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-lock text-rose-500 text-base shrink-0"></i>
        <span>Je zit momenteel in de isoleercel. Diefstal en klusjes zijn niet mogelijk.</span>
    </div>
    @elseif($character->isComatose() || $character->needsReviveSerum())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-heart-pulse text-rose-500 text-base shrink-0"></i>
        <span>Je ligt momenteel op de Intensive Care en kunt geen misdrijven plegen.</span>
    </div>
    @endif

    <!-- Mobile-First App Hero Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-4 sm:p-6 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200/90 dark:border-slate-800 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-purple-50 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-mask"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded border border-purple-200/80 dark:border-purple-800/70">
                            TBS Misdrijven
                        </span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
                        Diefstal & Klusjes
                    </h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                        Zakrol medebewoners of overval gevoelige locaties. Pas op: de isoleercel dreigt bij betrapping!
                    </p>
                </div>
            </div>

            <!-- Mobile Quick Stats Pills -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 shrink-0">
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-2.5 sm:px-3 py-1.5 rounded-xl border border-emerald-200/80 dark:border-emerald-800/70 text-[11px] sm:text-xs font-black flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-emerald-600"></i> {{ $character->thefts_succeeded }} Geslaagd
                </span>
                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 px-2.5 sm:px-3 py-1.5 rounded-xl border border-rose-200/80 dark:border-rose-800/70 text-[11px] sm:text-xs font-black flex items-center gap-1.5">
                    <i class="fa-solid fa-xmark text-rose-600"></i> {{ $character->thefts_failed }} Mislukt
                </span>
                <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700 text-[11px] sm:text-xs font-black flex items-center gap-1.5">
                    <i class="fa-solid fa-bolt text-amber-500"></i> {{ $character->energy }} EP
                </span>
            </div>
        </div>
    </div>

    <!-- PVP INMATE ROBBERY (ZAKKENROLLEN) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-4 sm:p-6 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                    <i class="fa-solid fa-users-viewfinder text-rose-600 dark:text-rose-400"></i>
                    <span>Medepatiënten Zakkenrollen (PvP)</span>
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    Rol contanten van andere patiënten. Slachtoffers krijgen direct een alarm!
                </p>
            </div>
            <span class="text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 px-2.5 py-1 rounded-xl shrink-0 inline-flex items-center gap-1">
                <i class="fa-solid fa-shield-halved text-amber-500"></i> Bankgeld is veilig
            </span>
        </div>

        @if($victims->isEmpty())
            <p class="text-xs text-slate-400 dark:text-slate-500 py-4 text-center">Er zijn momenteel geen andere patiënten binnen bereik.</p>
        @else
            <!-- Responsive Grid: 2 columns on mobile, 4 on desktop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
                @foreach($victims as $vic)
                <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/70 p-3 flex flex-col justify-between gap-2.5 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
                    <div class="flex items-center gap-2.5">
                        <div class="relative shrink-0">
                            <img src="{{ $vic->avatar_url }}" alt="{{ $vic->name }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs">
                            <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-900 {{ $vic->isOnline() ? 'bg-emerald-500' : 'bg-slate-400' }}" title="{{ $vic->online_status_label }}"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('profile.show', $vic->name) }}" class="text-xs font-black text-slate-900 dark:text-white truncate block hover:text-purple-600">{{ $vic->name }}</a>
                            <span class="text-[10px] {{ $vic->isOnline() ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400' }} block">Level {{ $vic->level }} &bull; {{ $vic->isOnline() ? 'Online' : 'Offline' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/80 dark:border-slate-700/70">
                        <div>
                            <span class="text-[8px] font-extrabold uppercase text-slate-400 block leading-none mb-0.5">Op Zak</span>
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($vic->cash, 0, ',', '.') }}</span>
                        </div>

                        <form method="POST" action="{{ route('theft.steal') }}"
                              @submit="if (!handleTheft($event, @js($vic->name))) return false;">
                            @csrf
                            <input type="hidden" name="tier" value="pocket">
                            <input type="hidden" name="victim_id" value="{{ $vic->id }}">
                            <input type="hidden" name="confirm_break_isolation" value="0">
                            <button type="submit" @if($character->energy < 8 || $character->isImprisoned() || $character->isComatose()) disabled @endif 
                                    class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 disabled:bg-slate-100 disabled:dark:bg-slate-800/80 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:border disabled:border-slate-200/80 disabled:dark:border-slate-700/60 disabled:cursor-not-allowed disabled:shadow-none text-white text-[10px] sm:text-[11px] font-black px-2.5 sm:px-3 py-2 min-h-[36px] rounded-xl shadow-xs transition-all duration-150 active:scale-95 flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-hand-holding-dollar text-[10px]"></i>
                                <span>Rollen (8 EP)</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- CRIME TIERS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
        @foreach($tiers as $key => $tier)
        @php
            $hasLevel = $character->level >= $tier['required_level'];
            $hasEnergy = $character->energy >= $tier['energy_cost'];
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border {{ $hasLevel ? 'border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80' : 'border-slate-200/70 dark:border-slate-800/50 opacity-75' }} shadow-xs overflow-hidden flex flex-col justify-between transition-all duration-200">
            <div class="p-4 sm:p-5 space-y-2.5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">{{ $tier['name'] }}</h2>
                        <span class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 block">
                            Risico: {{ $tier['jail_minutes'] }} min. isoleercel bij betrapping
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $hasLevel ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' }} shrink-0">
                        Min. Lvl {{ $tier['required_level'] }}
                    </span>
                </div>

                <!-- Specs -->
                <div class="grid grid-cols-3 gap-1.5 sm:gap-2 pt-1 text-xs">
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-2 sm:p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 text-center">
                        <span class="text-[8px] text-slate-400 uppercase font-black tracking-wider block">Energie</span>
                        <span class="font-black text-amber-600 dark:text-amber-400 mt-0.5 block text-xs"><i class="fa-solid fa-bolt mr-0.5"></i>{{ $tier['energy_cost'] }}</span>
                    </div>
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-2 sm:p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 text-center">
                        <span class="text-[8px] text-slate-400 uppercase font-black tracking-wider block">Buit</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 block text-xs truncate">€{{ number_format($tier['min_cash'], 0, ',', '.') }}–{{ number_format($tier['max_cash'], 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-2 sm:p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 text-center">
                        <span class="text-[8px] text-slate-400 uppercase font-black tracking-wider block">Kans</span>
                        <span class="font-black text-purple-600 dark:text-purple-400 mt-0.5 block text-xs">{{ $tier['base_chance'] }}%</span>
                    </div>
                </div>
            </div>

            <!-- Action Button: Full width thumb button on mobile -->
            <div class="px-4 py-3 bg-slate-50/90 dark:bg-slate-800/60 border-t border-slate-200/80 dark:border-slate-700/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                    @if($character->isComatose())
                        <i class="fa-solid fa-heart-pulse text-rose-500 mr-1"></i> Coma (Code Blauw)
                    @elseif($character->isImprisoned())
                        <i class="fa-solid fa-lock text-rose-500 mr-1"></i> In isoleercel
                    @elseif(! $hasLevel)
                        <i class="fa-solid fa-lock text-slate-400 mr-1"></i> Level {{ $tier['required_level'] }} vereist
                    @elseif(! $hasEnergy)
                        <i class="fa-solid fa-battery-empty text-amber-500 mr-1"></i> Energie ontoereikend
                    @else
                        <i class="fa-solid fa-hand-holding-dollar text-emerald-600 dark:text-emerald-400 mr-1"></i> Doelwit gereed
                    @endif
                </span>

                <form method="POST" action="{{ route('theft.steal') }}" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="tier" value="{{ $key }}">
                    <button type="submit" @if(! $hasLevel || ! $hasEnergy || $character->isImprisoned() || $character->isComatose()) disabled @endif 
                            class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 disabled:bg-slate-100 disabled:dark:bg-slate-800/80 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:border disabled:border-slate-200/80 disabled:dark:border-slate-700/60 disabled:cursor-not-allowed disabled:shadow-none text-white font-black px-4 py-2.5 rounded-xl text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-hand-sparkles"></i>
                        <span>Slag Slaan</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- RECENT CRIME HISTORY -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-4 sm:p-6">
        <h3 class="text-xs font-black uppercase text-slate-900 dark:text-white tracking-wider flex items-center gap-2 mb-3">
            <i class="fa-solid fa-list-check text-purple-600 dark:text-purple-400"></i>
            <span>Jouw Recente Diefstalgeschiedenis</span>
        </h3>
        <div class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
            @forelse($recentThefts as $rec)
            <div class="py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    @if($rec->success)
                        <span class="w-5 h-5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 flex items-center justify-center text-[10px] shrink-0 font-bold"><i class="fa-solid fa-check"></i></span>
                    @else
                        <span class="w-5 h-5 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 flex items-center justify-center text-[10px] shrink-0 font-bold"><i class="fa-solid fa-xmark"></i></span>
                    @endif
                    <span class="font-medium text-slate-800 dark:text-slate-200 truncate text-[11px] sm:text-xs">{{ $rec->log_message }}</span>
                </div>
                <span class="text-[10px] text-slate-400 shrink-0 font-mono">{{ $rec->created_at?->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-slate-400 dark:text-slate-500 py-3 text-center text-xs">Nog geen diefstallen geregistreerd in je dossier.</p>
            @endforelse
        </div>
    </div>

    <!-- MODAL: ISOLATIE VERBREKEN BEVESTIGING -->
    <div x-show="isolationModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs transition-opacity duration-200"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-slate-900 border-2 border-amber-300 dark:border-amber-700 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 relative"
             @click.away="isolationModalOpen = false"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">
            
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Isolatie Vroegtijdig Beëindigen?</h3>
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400">Let op: je verlaat je veilige cel!</span>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200 space-y-2">
                <p>
                    Je hebt momenteel <strong>Vrijwillige Isolatie</strong> aanstaan (tot <strong>{{ $character->protection_until?->format('d-m H:i') }}</strong>).
                </p>
                <p>
                    Als je nu <span class="font-extrabold text-slate-900 dark:text-white" x-text="targetName"></span> berooft, verlaat je je cel en <strong>vervalt je isolatiebescherming direct</strong>. Andere patiënten kunnen jou daarna ook weer aanvallen!
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
                <button type="button"
                        @click="isolationModalOpen = false"
                        class="py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-extrabold text-xs transition cursor-pointer">
                    Nee, veilig blijven
                </button>
                <button type="button"
                        @click="confirmBreakAndTheft()"
                        class="py-3 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs transition shadow-xs active:scale-95 cursor-pointer">
                    Ja, val toch aan!
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
