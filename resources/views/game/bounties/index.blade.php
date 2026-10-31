@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ tab: 'wanted', targetSearch: '', selectedTargetId: '', selectedTargetName: '' }">

    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-5 sm:p-7 relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-600 dark:bg-rose-600 text-white flex items-center justify-center text-2xl shadow-xs shrink-0">
                    <i class="fa-solid fa-crosshairs"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full border border-rose-200/80 dark:border-rose-800/70 mb-1">
                        Premiejagers & Vogelvrij
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Kopgeld Opsporingsbord
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Zet een prijs op het hoofd van je rivalen of jaag zelf op vogelvrij verklaarde patiënten om de buit op te strijken!
                    </p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex items-center gap-1 bg-slate-100/90 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/80 dark:border-slate-700/70 shrink-0">
                <button type="button" 
                        @click="tab = 'wanted'" 
                        :class="tab === 'wanted' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-purple-200/80 dark:border-purple-800/70 font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-skull text-rose-500"></i> Gezocht ({{ $bounties->count() }})
                </button>
                <button type="button" 
                        @click="tab = 'place'" 
                        :class="tab === 'place' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-purple-200/80 dark:border-purple-800/70 font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-plus text-purple-600 dark:text-purple-400"></i> Kopgeld Uitzetten
                </button>
                <button type="button" 
                        @click="tab = 'claimed'" 
                        :class="tab === 'claimed' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-purple-200/80 dark:border-purple-800/70 font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-trophy text-amber-500"></i> Geïncasseerd
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 1: WANTED BOARD (Gezocht door Patiënten) -->
    <div x-show="tab === 'wanted'" class="space-y-4">
        @if($bounties->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center shadow-xs">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
                <h3 class="text-base font-black text-slate-800 dark:text-white">Het opsporingsbord is momenteel leeg!</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1 mb-5">
                    Er staat op dit moment op niemand een kopgeld. Ben je beroofd of beledigd? Wees de eerste en zet een premie op je aartsrivaal!
                </p>
                <button type="button" @click="tab = 'place'" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus mr-1"></i> Plaats eerste kopgeld
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($bounties as $bounty)
                @if($bounty->target)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border-2 border-rose-200 dark:border-rose-900/60 shadow-xs hover:border-rose-400 dark:hover:border-rose-600 transition-all duration-200 overflow-hidden flex flex-col justify-between group">
                    <div>
                        <!-- Header Banner -->
                        <div class="bg-rose-600 dark:bg-rose-700 px-4 py-2.5 flex items-center justify-between text-white">
                            <span class="text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5">
                                <i class="fa-solid fa-skull"></i> VOGELVRIJ
                            </span>
                            <span class="text-xs font-black tracking-tight bg-black/30 px-2.5 py-0.5 rounded-md">
                                € {{ number_format($bounty->amount, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 space-y-4">
                            <div class="flex items-center gap-3.5">
                                <div class="relative shrink-0">
                                    <img src="{{ $bounty->target->avatar_url }}" alt="{{ $bounty->target->name }}" class="w-14 h-14 rounded-xl object-cover border-2 border-rose-200 dark:border-rose-800 shadow-xs">
                                    <span class="absolute -bottom-1 -right-1 bg-slate-900 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md">
                                        Lvl {{ $bounty->target->level }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-black text-slate-900 dark:text-white truncate">
                                        <a href="{{ route('profile.show', $bounty->target->name) }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">
                                            {{ $bounty->target->name }}
                                        </a>
                                    </h3>
                                    <div class="flex items-center gap-1 text-[11px] text-slate-400 font-semibold mt-0.5">
                                        <span>Uitgeloofd door:</span>
                                        <strong class="text-slate-700 dark:text-slate-300">{{ $bounty->placedBy->name ?? 'Anoniem' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Reason Quote Box -->
                            <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs text-slate-700 dark:text-slate-300 italic font-medium leading-snug">
                                <i class="fa-solid fa-quote-left text-slate-300 dark:text-slate-600 mr-1"></i>
                                "{{ $bounty->reason ?: 'Vogelvrij verklaard!' }}"
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Attack Button -->
                    <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-3">
                        <span class="text-[10px] font-bold text-slate-400">
                            {{ $bounty->created_at->diffForHumans() }}
                        </span>

                        @if((Auth::user()?->character?->id ?? null) === $bounty->target_character_id)
                            <span class="text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 px-3 py-1.5 rounded-xl border border-rose-200/80 dark:border-rose-800/70">
                                Dit ben jij!
                            </span>
                        @else
                            <a href="{{ route('fight.arena', $bounty->target) }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-crosshairs"></i> Vecht & Incasseer
                            </a>
                        @endif
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        @endif
    </div>

    <!-- TAB 2: PLACE BOUNTY FORM -->
    <div x-show="tab === 'place'" class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 dark:border-slate-800 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center text-lg font-black border border-purple-200/80 dark:border-purple-800/70 shadow-2xs">
                    <i class="fa-solid fa-bullseye"></i>
                </span>
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">Kopgeld Uitzetten</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Het bedrag wordt direct van je contant geld afgetrokken en op het hoofd van je doelwit geplaatst.
                    </p>
                </div>
            </div>

            <form action="{{ route('bounties.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Target selection (Interactive Autocomplete) -->
                <div x-data="{
                    search: '',
                    selectedId: '',
                    openDropdown: false,
                    targets: @js(collect($potentialTargets ?? [])->map(fn($pt) => [
                        'id' => (string) $pt->id,
                        'name' => (string) $pt->name,
                        'level' => (int) $pt->level,
                        'avatar' => (string) ($pt->avatar_url ?? ''),
                    ])->values()),
                    get filtered() {
                        if (!this.search.trim()) return this.targets.slice(0, 10);
                        return this.targets.filter(t => t.name.toLowerCase().includes(this.search.toLowerCase())).slice(0, 10);
                    },
                    select(t) {
                        this.selectedId = t.id;
                        this.search = t.name;
                        this.openDropdown = false;
                    }
                }" class="relative">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Kies Doelwit (Patiënt)
                    </label>

                    <input type="hidden" name="target_id" :value="selectedId">

                    <div class="relative flex items-center">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-purple-600 dark:text-purple-400 text-sm z-10">
                            <i class="fa-solid fa-crosshairs"></i>
                        </div>
                        <input type="text" 
                               name="target_name"
                               x-model="search"
                               @focus="openDropdown = true"
                               @input="openDropdown = true; selectedId = ''"
                               @click.outside="openDropdown = false"
                               required
                               autocomplete="off"
                               placeholder="Typ de naam van de patiënt..."
                               class="w-full text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl py-3 pl-11 pr-10 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition shadow-2xs">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-slate-400 text-xs z-10">
                            <i class="fa-solid fa-chevron-down transition-transform duration-150" :class="openDropdown ? 'rotate-180' : ''"></i>
                        </div>
                    </div>

                    <!-- Autocomplete Dropdown Menu -->
                    <div x-show="openDropdown && filtered.length > 0" 
                         x-transition
                         class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                        <template x-for="t in filtered" :key="t.id">
                            <button type="button" 
                                    @click="select(t)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-purple-50 dark:hover:bg-slate-700/60 flex items-center justify-between text-xs transition cursor-pointer group">
                                <div class="flex items-center gap-3">
                                    <img :src="t.avatar" class="w-8 h-8 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs bg-slate-100 dark:bg-slate-700 shrink-0">
                                    <div>
                                        <span class="font-black text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 block text-xs" x-text="t.name"></span>
                                        <span class="text-[10px] text-slate-400 font-semibold" x-text="'Niveau ' + t.level"></span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 group-hover:bg-purple-100 dark:group-hover:bg-purple-900/60 px-2 py-0.5 rounded-md border border-purple-200/80 dark:border-purple-800/70">
                                    Kies
                                </span>
                            </button>
                        </template>
                    </div>

                    <!-- No results message -->
                    <div x-show="openDropdown && filtered.length === 0 && search.trim() !== ''"
                         x-transition
                         class="absolute z-50 left-0 right-0 mt-1 p-3 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 text-center text-xs text-slate-400">
                        Geen patiënt gevonden met deze naam
                    </div>
                </div>

                <!-- Bounty Amount -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Beloning (€ Contant)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-slate-400 font-black">€</span>
                        <input type="number" 
                               name="amount" 
                               min="1000" 
                               max="10000000" 
                               step="500"
                               required
                               value="5000"
                               class="w-full text-sm font-black pl-8 pr-4 py-3 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                        @foreach([1000, 5000, 10000, 25000, 50000] as $preset)
                        <button type="button" 
                                onclick="document.querySelector('input[name=amount]').value = {{ $preset }}"
                                class="text-[10px] font-bold px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">
                            +€ {{ number_format($preset, 0, ',', '.') }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Reason / Message -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Reden / Boodschap aan de premiejagers
                    </label>
                    <input type="text" 
                           name="reason" 
                           maxlength="150" 
                           placeholder="bijv: Heeft me bestolen op de luchtplaats! Pak hem!"
                           class="w-full text-sm font-medium bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-sm uppercase tracking-wider shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-bullseye"></i> Plaats Kopgeld op het Prikbord
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: CLAIMED BOUNTIES (Hall of Fame) -->
    <div x-show="tab === 'claimed'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 flex items-center justify-center text-xs font-bold border border-amber-200/80 dark:border-amber-800/70">
                        <i class="fa-solid fa-trophy"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Recent Geïncasseerde Premies</h3>
                </div>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($claimedBounties as $cb)
                <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-center text-sm font-black shrink-0">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate">
                                <strong class="text-purple-700 dark:text-purple-400">{{ $cb->claimedBy->name ?? 'Onbekende jager' }}</strong>
                                <span class="font-normal text-slate-400 dark:text-slate-500">versloeg</span>
                                <strong class="text-rose-600 dark:text-rose-400">{{ $cb->target->name ?? 'Onbekend' }}</strong>
                            </div>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium block mt-0.5">
                                {{ $cb->claimed_at ? $cb->claimed_at->diffForHumans() : 'Recent' }}
                            </span>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-xs sm:text-sm font-black text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-3 py-1 rounded-xl border border-emerald-200/80 dark:border-emerald-800/70">
                            +€ {{ number_format($cb->amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-xs font-bold text-slate-400 dark:text-slate-500 italic">
                    Nog geen kopgeld geïncasseerd. Wees de eerste premiejager!
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
