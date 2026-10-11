@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ tab: 'wanted', targetSearch: '', selectedTargetId: '', selectedTargetName: '' }">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 sm:p-7 relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 via-red-600 to-rose-700 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    <i class="fa-solid fa-crosshairs"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-rose-50 text-rose-700 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md border border-rose-200 mb-1">
                        Premiejagers & Vogelvrij
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Kopgeld Opsporingsbord
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Zet een prijs op het hoofd van je rivalen of jaag zelf op vogelvrij verklaarde patiënten om de buit op te strijken!
                    </p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex items-center gap-1.5 bg-slate-100 p-1.5 rounded-xl shrink-0">
                <button type="button" 
                        @click="tab = 'wanted'" 
                        :class="tab === 'wanted' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 font-bold hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-skull text-rose-500"></i> Gezocht ({{ $bounties->count() }})
                </button>
                <button type="button" 
                        @click="tab = 'place'" 
                        :class="tab === 'place' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 font-bold hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-plus text-purple-600"></i> Kopgeld Uitzetten
                </button>
                <button type="button" 
                        @click="tab = 'claimed'" 
                        :class="tab === 'claimed' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 font-bold hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-trophy text-amber-500"></i> Geïncasseerd
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 1: WANTED BOARD (Gezocht door Patiënten) -->
    <div x-show="tab === 'wanted'" class="space-y-4">
        @if($bounties->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
                <h3 class="text-base font-black text-slate-800">Het opsporingsbord is momenteel leeg!</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-5">
                    Er staat op dit moment op niemand een kopgeld. Ben je beroofd of beledigd? Wees de eerste en zet een premie op je aartsrivaal!
                </p>
                <button type="button" @click="tab = 'place'" class="px-5 py-2.5 rounded-xl bg-purple-600 text-white font-black text-xs uppercase tracking-wider hover:bg-purple-700 transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus mr-1"></i> Plaats eerste kopgeld
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($bounties as $bounty)
                <div class="bg-white rounded-2xl border-2 border-rose-200 shadow-xs hover:border-rose-400 transition overflow-hidden flex flex-col justify-between group">
                    <div>
                        <!-- Header Banner -->
                        <div class="bg-gradient-to-r from-rose-600 to-red-700 px-4 py-2.5 flex items-center justify-between text-white">
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
                                    <img src="{{ $bounty->target->avatar_url }}" alt="{{ $bounty->target->name }}" class="w-14 h-14 rounded-xl object-cover border-2 border-rose-200 shadow-xs">
                                    <span class="absolute -bottom-1 -right-1 bg-slate-900 text-white text-[9px] font-black px-1.5 py-0.2 rounded-md">
                                        Lvl {{ $bounty->target->level }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-black text-slate-900 truncate">
                                        <a href="{{ route('profile.show', $bounty->target->name) }}" class="hover:text-purple-600 transition">
                                            {{ $bounty->target->name }}
                                        </a>
                                    </h3>
                                    <div class="flex items-center gap-1 text-[11px] text-slate-400 font-semibold mt-0.5">
                                        <span>Uitgeloofd door:</span>
                                        <strong class="text-slate-700">{{ $bounty->placedBy->name ?? 'Anoniem' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Reason Quote Box -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 italic font-medium leading-snug">
                                <i class="fa-solid fa-quote-left text-slate-300 mr-1"></i>
                                "{{ $bounty->reason ?: 'Vogelvrij verklaard!' }}"
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Attack Button -->
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
                        <span class="text-[10px] font-bold text-slate-400">
                            {{ $bounty->created_at->diffForHumans() }}
                        </span>

                        @if(Auth::user()->character->id === $bounty->target_character_id)
                            <span class="text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-200">
                                Dit ben jij!
                            </span>
                        @else
                            <a href="{{ route('fight.arena', $bounty->target) }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider transition shadow-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-crosshairs"></i> Vecht & Incasseer
                            </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- TAB 2: PLACE BOUNTY FORM -->
    <div x-show="tab === 'place'" class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-lg font-black border border-purple-200">
                    <i class="fa-solid fa-bullseye"></i>
                </span>
                <div>
                    <h2 class="text-base font-black text-slate-900">Kopgeld Uitzetten</h2>
                    <p class="text-xs text-slate-500">
                        Het bedrag wordt direct van je contant geld afgetrokken en op het hoofd van je doelwit geplaatst.
                    </p>
                </div>
            </div>

            <form action="{{ route('bounties.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Target selection -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                        Kies Doelwit (Patiënt)
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="target_name" 
                               list="patientsList" 
                               required
                               placeholder="Typ de naam van de patiënt..."
                               class="w-full text-sm font-semibold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none transition">
                        <datalist id="patientsList">
                            @foreach($potentialTargets as $pt)
                                <option value="{{ $pt->name }}">Niveau {{ $pt->level }}</option>
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <!-- Bounty Amount -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
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
                               class="w-full text-sm font-black pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none transition">
                    </div>
                    <div class="flex items-center gap-1.5 mt-2">
                        @foreach([1000, 5000, 10000, 25000, 50000] as $preset)
                        <button type="button" 
                                onclick="document.querySelector('input[name=amount]').value = {{ $preset }}"
                                class="text-[10px] font-bold px-2 py-1 rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-700 text-slate-600 transition cursor-pointer">
                            +€ {{ number_format($preset, 0, ',', '.') }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Reason / Message -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                        Reden / Boodschap aan de premiejagers
                    </label>
                    <input type="text" 
                           name="reason" 
                           maxlength="150" 
                           placeholder="bijv: Heeft me bestolen op de luchtplaats! Pak hem!"
                           class="w-full text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none transition">
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-rose-600 via-red-600 to-rose-700 hover:from-rose-700 hover:to-red-800 text-white font-black text-sm uppercase tracking-wider shadow-md transition transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-bullseye"></i> Plaats Kopgeld op het Prikbord
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: CLAIMED BOUNTIES (Hall of Fame) -->
    <div x-show="tab === 'claimed'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold border border-amber-200">
                        <i class="fa-solid fa-trophy"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Recent Geïncasseerde Premies</h3>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($claimedBounties as $cb)
                <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-black shrink-0">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs sm:text-sm font-black text-slate-900 truncate">
                                <strong class="text-purple-700">{{ $cb->claimedBy->name ?? 'Onbekende jager' }}</strong>
                                <span class="font-normal text-slate-400">versloeg</span>
                                <strong class="text-rose-600">{{ $cb->target->name ?? 'Onbekend' }}</strong>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium block mt-0.5">
                                {{ $cb->claimed_at ? $cb->claimed_at->diffForHumans() : 'Recent' }}
                            </span>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-xs sm:text-sm font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                            +€ {{ number_format($cb->amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-xs font-bold text-slate-400 italic">
                    Nog geen kopgeld geïncasseerd. Wees de eerste premiejager!
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
