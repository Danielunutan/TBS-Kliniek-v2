@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    avatarModalOpen: false, 
    bioModalOpen: false, 
    activeTab: '{{ session('profile_tab', request('tab', 'overzicht')) }}',
    init() {
        if (!'{{ session('profile_tab') }}' && !'{{ request('tab') }}' && sessionStorage.getItem('tbs_profile_tab')) {
            this.activeTab = sessionStorage.getItem('tbs_profile_tab');
        }
    },
    setActiveTab(tab) {
        this.activeTab = tab;
        sessionStorage.setItem('tbs_profile_tab', tab);
    }
}">

    <!-- Profile Header Card (Clean Modern Light) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 relative">
            
            <!-- Avatar with Selection Button -->
            <div class="relative shrink-0 group">
                <img src="{{ $profileCharacter->avatar_url }}" alt="{{ $profileCharacter->name }}" class="w-32 h-32 rounded-2xl object-cover border-4 border-slate-100 shadow-sm bg-slate-100 transition-transform duration-300 group-hover:scale-105">
                
                @if(Auth::user()->character->id === $profileCharacter->id)
                <button @click="avatarModalOpen = true" type="button" class="absolute -bottom-2 -right-2 bg-slate-900 text-white p-2.5 rounded-xl hover:bg-indigo-600 transition shadow-sm border-2 border-white flex items-center justify-center text-xs" title="Kies een andere profielfoto">
                    <i class="fa-solid fa-camera"></i>
                </button>
                @endif
            </div>

            <!-- Profile Info & Actions -->
            <div class="flex-1 w-full text-center md:text-left">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-3">
                    <div>
                        <div class="flex items-center justify-center md:justify-start gap-2.5">
                            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $profileCharacter->name }}</h1>
                            <span class="bg-slate-100 text-slate-700 text-xs font-bold px-2 py-0.5 rounded-md border border-slate-200">
                                Level {{ $profileCharacter->level }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wider">
                            Patiënt &bull; TBS Kliniek &bull; {{ $profileCharacter->gang ? $profileCharacter->gang->name : 'Geen Bende' }}
                        </p>
                    </div>
                    
                    @if(Auth::user()->character->id === $profileCharacter->id)
                    <!-- Edit Actions -->
                    <div class="flex gap-2 justify-center md:justify-end">
                        <button @click="avatarModalOpen = true" type="button" class="px-3.5 py-2 text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg transition flex items-center gap-2">
                            <i class="fa-solid fa-image text-slate-500"></i> Foto Wijzigen
                        </button>
                        <button @click="bioModalOpen = true" type="button" class="px-3.5 py-2 text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 rounded-lg transition shadow-xs flex items-center gap-2">
                            <i class="fa-solid fa-pen-to-square"></i> Bio Bewerken
                        </button>
                    </div>
                    @else
                    <!-- Interaction Actions (Friend, Message, Direct Theft & Fight) -->
                    <div class="flex flex-wrap gap-2 justify-center md:justify-end items-center">
                        <!-- Friend Request Action Button -->
                        @if($friendStatus === 'accepted')
                            <span class="px-3.5 py-2 text-xs font-black bg-purple-50 text-purple-700 border border-purple-200 rounded-xl flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-user-check text-purple-600"></i>
                                <span>Vrienden</span>
                            </span>
                        @elseif($friendStatus === 'pending_sent')
                            <span class="px-3.5 py-2 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-xl flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-clock text-amber-500"></i>
                                <span>Verzoek Verstuurd</span>
                            </span>
                        @elseif($friendStatus === 'pending_received' && $pendingRequestRecord)
                            <form action="{{ route('friends.accept', $pendingRequestRecord) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 text-xs font-black bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Accepteer Verzoek</span>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('friends.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="friend_name" value="{{ $profileCharacter->name }}">
                                <button type="submit" class="px-3.5 py-2 text-xs font-black bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-600 hover:to-indigo-600 text-white rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-user-plus text-amber-300"></i>
                                    <span>Vriend Toevoegen</span>
                                </button>
                            </form>
                        @endif

                        <!-- Send Private Letter (Friends only) -->
                        @if($character->isFriendWith($profileCharacter))
                        <a href="{{ route('messages.index', ['recipient' => $profileCharacter->name]) }}" class="px-3.5 py-2 text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs">
                            <i class="fa-solid fa-envelope text-indigo-600"></i>
                            <span>Brief Sturen</span>
                        </a>
                        @else
                        <span class="px-3.5 py-2 text-xs font-bold bg-slate-50 text-slate-400 rounded-xl border border-slate-200 flex items-center gap-1.5 cursor-not-allowed" title="Je kunt alleen brieven sturen naar vrienden. Voeg deze patiënt eerst toe als vriend!">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                            <span>Brief (Vrienden)</span>
                        </span>
                        @endif

                        <!-- Direct Steal Button with 3s anti-script loader -->
                        <div x-data="{ stealing: false, countdown: 3, intervalId: null }">
                            <form x-ref="stealForm" action="{{ route('theft.steal') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tier" value="pocket">
                                <input type="hidden" name="victim_id" value="{{ $profileCharacter->id }}">
                                
                                <button type="button" 
                                        @click="if(!stealing) { stealing = true; intervalId = setInterval(() => { countdown--; if(countdown <= 0) { clearInterval(intervalId); $refs.stealForm.submit(); } }, 1000); }"
                                        :disabled="stealing"
                                        :class="stealing ? 'bg-amber-600 text-white cursor-wait' : 'bg-amber-500 hover:bg-amber-600 text-white cursor-pointer'"
                                        class="px-4 py-2 text-xs font-black rounded-xl transition shadow-xs flex items-center gap-2">
                                    <template x-if="!stealing">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-mask"></i>
                                            <span>Beroof Patiënt (€{{ number_format($profileCharacter->cash, 0, ',', '.') }})</span>
                                        </span>
                                    </template>
                                    <template x-if="stealing">
                                        <span class="flex items-center gap-1.5 font-black">
                                            <i class="fa-solid fa-spinner fa-spin"></i>
                                            <span>Zakken doorzoeken... <span x-text="countdown"></span>s</span>
                                        </span>
                                    </template>
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('fight.index') }}" class="px-3.5 py-2 text-xs font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl transition flex items-center gap-2">
                            <i class="fa-solid fa-hand-fist"></i> Vechten
                        </a>
                    </div>
                    @endif
                </div>

                <!-- XP Bar -->
                @php
                    $lvlService = app(\App\Services\LevelService::class);
                    $reqXp = $lvlService->getXpRequiredForNextLevel($profileCharacter->level);
                    $pctXp = min(100, max(0, round(($profileCharacter->xp / max(1, $reqXp)) * 100)));
                @endphp
                <div class="flex flex-col gap-1 mb-4 md:w-3/4">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wide">Voortgang naar Level {{ $profileCharacter->level + 1 }}</span>
                        <span class="font-bold text-slate-700">{{ number_format($profileCharacter->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP ({{ $pctXp }}%)</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" style="width: {{ $pctXp }}%"></div>
                    </div>
                </div>

                <!-- Status quote -->
                <div class="bg-slate-50 border border-slate-100 rounded-lg px-4 py-2.5 inline-block text-left text-xs font-medium text-slate-600">
                    <i class="fa-solid fa-quote-left text-slate-300 mr-1.5"></i>
                    {{ $profileCharacter->status_message ?: 'Geen statusbericht ingesteld. "Kliniekleven is overleven."' }}
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100 bg-slate-50/50">
            <div class="p-4 sm:p-5 text-center flex flex-col items-center justify-center">
                <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-indigo-500"></i> Respect
                </span>
                <span class="text-lg sm:text-xl font-extrabold text-slate-900">{{ number_format($profileCharacter->respect, 0, ',', '.') }}</span>
            </div>
            <div class="p-4 sm:p-5 text-center flex flex-col items-center justify-center">
                <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-coins text-amber-500"></i> Contant Geld
                </span>
                <span class="text-lg sm:text-xl font-extrabold text-slate-900">€{{ number_format($profileCharacter->cash, 0, ',', '.') }}</span>
            </div>
            <div class="p-4 sm:p-5 text-center flex flex-col items-center justify-center">
                <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-building-columns text-emerald-500"></i> Banktegoed
                </span>
                <span class="text-lg sm:text-xl font-extrabold text-slate-900">€{{ number_format($profileCharacter->bank, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-t border-slate-100 px-6 gap-6 overflow-x-auto">
            <button @click="setActiveTab('overzicht')" :class="activeTab === 'overzicht' ? 'border-purple-600 text-purple-900 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800'" class="py-3.5 text-xs border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-id-card"></i> Dossier Overzicht
            </button>
            <button @click="setActiveTab('vrienden')" :class="activeTab === 'vrienden' ? 'border-purple-600 text-purple-900 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800'" class="py-3.5 text-xs border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-user-group text-purple-500"></i> Vriendenlijst ({{ $friendsCount }})
            </button>
            <button @click="setActiveTab('gastenboek')" :class="activeTab === 'gastenboek' ? 'border-purple-600 text-purple-900 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800'" class="py-3.5 text-xs border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-book-open"></i> Gastenboek ({{ $guestbookEntries->total() }})
            </button>
        </div>
    </div>

    <!-- Tab 1: Overzicht & Statistieken -->
    <div x-show="activeTab === 'overzicht'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Attributen & Stats -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Statistieken & Vaardigheden</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-lg">
                    <span class="font-bold text-slate-600 flex items-center gap-2.5">
                        <i class="fa-solid fa-hand-fist text-rose-500 w-4"></i> Kracht
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $profileCharacter->strength }}</span>
                </div>
                <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-lg">
                    <span class="font-bold text-slate-600 flex items-center gap-2.5">
                        <i class="fa-solid fa-shield-halved text-indigo-500 w-4"></i> Verdediging
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $profileCharacter->defense }}</span>
                </div>
                <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-lg">
                    <span class="font-bold text-slate-600 flex items-center gap-2.5">
                        <i class="fa-solid fa-bolt text-emerald-500 w-4"></i> Conditie / Stamina
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $profileCharacter->stamina }}</span>
                </div>
                <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-lg">
                    <span class="font-bold text-slate-600 flex items-center gap-2.5">
                        <i class="fa-solid fa-bullseye text-orange-500 w-4"></i> Voltooide Missies
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $profileCharacter->missions_completed }}</span>
                </div>
                <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-lg">
                    <span class="font-bold text-slate-600 flex items-center gap-2.5">
                        <i class="fa-solid fa-trophy text-amber-500 w-4"></i> Gevechten Gewonnen / Verloren
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $profileCharacter->fights_won }}W / {{ $profileCharacter->fights_lost }}V</span>
                </div>
            </div>
        </div>

        <!-- Persoonlijke Bio -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Patiëntendossier & Biografie</h3>
                    @if(Auth::user()->character->id === $profileCharacter->id)
                    <button @click="bioModalOpen = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        <i class="fa-solid fa-pen"></i> Bewerken
                    </button>
                    @endif
                </div>

                <div class="bg-slate-50 p-5 rounded-xl border border-slate-100 text-xs text-slate-600 leading-relaxed min-h-[140px]">
                    @if($profileCharacter->bio)
                        {!! nl2br(e($profileCharacter->bio)) !!}
                    @else
                        <p class="text-slate-400 italic">Deze patiënt heeft nog geen persoonlijke biografie geschreven. Klik op 'Bio Bewerken' om jouw verhaal te delen!</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Geregistreerd in kliniek: {{ $profileCharacter->created_at->format('d-m-Y') }}</span>
                <span>Actief: {{ $profileCharacter->last_active_at ? $profileCharacter->last_active_at->diffForHumans() : 'Zojuist' }}</span>
            </div>
        </div>

        <!-- Vrienden Preview Card in Overview -->
        <div class="md:col-span-2 bg-white rounded-xl shadow-xs border border-slate-200/80 p-6">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-black">
                        <i class="fa-solid fa-user-group"></i>
                    </span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Vrienden & Medepatiënten ({{ $friendsCount }})</h3>
                </div>
                <button type="button" @click="setActiveTab('vrienden')" class="text-xs font-extrabold text-purple-600 hover:text-purple-800 transition flex items-center gap-1 cursor-pointer">
                    <span>Bekijk Alle ({{ $friendsCount }})</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>

            @if($friends->isEmpty())
            <div class="p-6 text-center text-xs text-slate-400 bg-slate-50/50 rounded-xl border border-slate-100">
                Nog geen goedgekeurde vrienden verbonden in dit kliniekdossier.
            </div>
            @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                @foreach($friends->take(6) as $f)
                <a href="{{ route('profile.show', $f->friend->name) }}" class="p-2.5 rounded-xl border border-slate-200/70 hover:border-purple-300 hover:bg-purple-50/20 transition flex flex-col items-center text-center group">
                    <div class="relative mb-2">
                        <img src="{{ $f->friend->avatar_url }}" alt="{{ $f->friend->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs group-hover:scale-105 transition-transform">
                        @if($f->is_bff)
                        <span class="absolute -top-1 -right-1 bg-pink-500 text-white text-[8px] w-4 h-4 rounded-full flex items-center justify-center shadow-xs" title="BFF">
                            <i class="fa-solid fa-heart text-[7px] text-white"></i>
                        </span>
                        @endif
                    </div>
                    <span class="text-xs font-extrabold text-slate-900 group-hover:text-purple-600 transition truncate max-w-full block leading-tight">
                        {{ $f->friend->name }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 mt-0.5">Lvl {{ $f->friend->level }}</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Tab 2: Vriendenlijst (Volledig Overzicht) -->
    <div x-show="activeTab === 'vrienden'" class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users text-purple-600"></i>
                    <span>Vrienden & Bondgenoten van {{ $profileCharacter->name }} ({{ $friendsCount }})</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Medepatiënten waarmee een vertrouwensband is opgebouwd binnen de TBS kliniek.</p>
            </div>
            @if($isOwnProfile)
            <a href="{{ route('friends.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 text-xs font-bold transition">
                <i class="fa-solid fa-gear"></i> Vrienden Beheren & Toevoegen
            </a>
            @endif
        </div>

        @if($friends->isEmpty())
        <div class="p-10 text-center space-y-2 bg-slate-50 rounded-xl border border-slate-100">
            <div class="w-12 h-12 mx-auto rounded-full bg-purple-50 text-purple-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <h4 class="text-xs font-extrabold text-slate-700">Geen vrienden gevonden</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                Deze patiënt heeft momenteel nog geen officiële vrienden in het kliniekdossier.
            </p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($friends as $f)
            <div class="p-4 rounded-xl border border-slate-200/90 hover:border-purple-300 bg-white hover:bg-purple-50/20 transition flex flex-col justify-between gap-3 shadow-2xs group">
                <div class="flex items-center gap-3">
                    <a href="{{ route('profile.show', $f->friend->name) }}" class="relative shrink-0">
                        <img src="{{ $f->friend->avatar_url }}" alt="{{ $f->friend->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs group-hover:scale-105 transition-transform">
                        @if($f->is_bff)
                        <span class="absolute -top-1 -right-1 bg-pink-500 text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center shadow-xs" title="BFF (Beste Vriend)">
                            <i class="fa-solid fa-heart text-[8px]"></i>
                        </span>
                        @endif
                    </a>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('profile.show', $f->friend->name) }}" class="text-xs font-black text-slate-900 hover:text-purple-600 transition truncate block">
                                {{ $f->friend->name }}
                            </a>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded">
                                Lvl {{ $f->friend->level }}
                            </span>
                            @if($f->friend->gang)
                            <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded border border-purple-100 truncate">
                                [{{ $f->friend->gang->tag }}]
                            </span>
                            @endif
                        </div>
                        <span class="text-[10px] text-purple-700 font-extrabold mt-1 block">
                            {{ number_format($f->friend->respect, 0, ',', '.') }} Respect
                        </span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ route('profile.show', $f->friend->name) }}" class="text-[11px] font-bold text-slate-600 hover:text-purple-600 transition flex items-center gap-1">
                        <i class="fa-solid fa-address-card text-slate-400"></i> Profiel
                    </a>
                    @if(Auth::user()->character->id !== $f->friend->id)
                    <a href="{{ route('messages.index') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                        <i class="fa-solid fa-envelope text-indigo-400"></i> Brief Sturen
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Tab 3: Gastenboek -->
    <div x-show="activeTab === 'gastenboek'" class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-6">
        <div>
            <h3 class="text-sm font-bold text-slate-900 mb-1">Gastenboek van {{ $profileCharacter->name }}</h3>
            <p class="text-xs text-slate-500">Laat een bericht achter op het prikbord van deze bewoner.</p>
        </div>

        <!-- Nieuw bericht formulier -->
        <form action="{{ route('guestbook.post', $profileCharacter->name) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <textarea name="message" rows="3" required maxlength="500" placeholder="Schrijf een respectvolle (of dreigende) boodschap..." class="w-full text-xs p-3 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-5 py-2.5 rounded-lg transition shadow-xs flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-[10px]"></i> Plaats Bericht
                </button>
            </div>
        </form>

        <!-- Berichtjes lijst -->
        <div class="divide-y divide-slate-100 pt-4">
            @forelse($guestbookEntries as $entry)
            <div class="py-4 flex items-start gap-4">
                <img src="{{ $entry->author->avatar_url }}" alt="{{ $entry->author->name }}" class="w-9 h-9 rounded-lg object-cover shrink-0 border border-slate-200">
                <div class="flex-1">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('profile.show', $entry->author->name) }}" class="text-xs font-bold text-slate-900 hover:text-indigo-600 transition">
                                {{ $entry->author->name }}
                            </a>
                            @if($entry->author->user && $entry->author->user->role !== 'user')
                                @php $gBadge = $entry->author->user->role_badge; @endphp
                                <span class="text-[9px] font-black uppercase tracking-wider {{ $gBadge['bg'] }} {{ $gBadge['text'] }} border {{ $gBadge['border'] }} px-1.5 py-0.2 rounded inline-flex items-center gap-1">
                                    <i class="fa-solid {{ $gBadge['icon'] }} text-[8px]"></i> {{ $gBadge['label'] }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-400">{{ $entry->created_at->diffForHumans() }}</span>
                            @if($entry->author_character_id === Auth::user()->character->id || $entry->profile_character_id === Auth::user()->character->id || Auth::user()->canModerate())
                            <form action="{{ route('guestbook.destroy', $entry) }}" method="POST"
                                  data-confirm="{{ ($entry->author_character_id === Auth::user()->character->id || $entry->profile_character_id === Auth::user()->character->id) ? 'Weet je zeker dat je dit gastenboekbericht wilt verwijderen?' : 'Weet je zeker dat je dit gastenboekbericht als moderator/beheerder wilt verwijderen?' }}"
                                  data-confirm-title="Gastenboek Bericht Verwijderen"
                                  data-confirm-btn="Verwijderen"
                                  data-confirm-type="danger"
                                  data-confirm-icon="fa-trash-can"
                                  class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded-md hover:bg-slate-100 transition cursor-pointer" title="{{ Auth::user()->canModerate() ? 'Verwijderen als moderator/beheerder' : 'Verwijderen' }}">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $entry->message }}</p>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 text-xs">
                Nog geen berichten in dit gastenboek. Wees de eerste die iets achterlaat!
            </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL 1: Avatar Selector Modal (Diverse Generated Avatars) -->
    @if(Auth::user()->character->id === $profileCharacter->id)
    <div x-show="avatarModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="avatarModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Kies je Profielfoto</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Selecteer jouw patiëntenpas avatar uit onze officiële kliniek assets.</p>
                </div>
                <button @click="avatarModalOpen = false" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Avatars Grid -->
            <form action="{{ route('profile.avatar') }}" method="POST">
                @csrf
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5 max-h-96 overflow-y-auto custom-scrollbar p-1">
                    @php
                        $avatars = [
                            ['file' => 'assets/characters/avatar_male_1.jpg', 'title' => 'Man 1'],
                            ['file' => 'assets/characters/avatar_male_2.jpg', 'title' => 'Man 2'],
                            ['file' => 'assets/characters/avatar_male_3.jpg', 'title' => 'Man 3'],
                            ['file' => 'assets/characters/avatar_male_4.jpg', 'title' => 'Man 4'],
                            ['file' => 'assets/characters/avatar_male_5.jpg', 'title' => 'Man 5'],
                            ['file' => 'assets/characters/avatar_male_6.jpg', 'title' => 'Man 6'],
                            ['file' => 'assets/characters/avatar_female_1.jpg', 'title' => 'Vrouw 1'],
                            ['file' => 'assets/characters/avatar_female_2.jpg', 'title' => 'Vrouw 2'],
                            ['file' => 'assets/characters/avatar_female_3.jpg', 'title' => 'Vrouw 3'],
                            ['file' => 'assets/characters/avatar_female_4.jpg', 'title' => 'Vrouw 4'],
                            ['file' => 'assets/characters/avatar_female_5.jpg', 'title' => 'Vrouw 5'],
                            ['file' => 'assets/characters/avatar_female_6.jpg', 'title' => 'Vrouw 6'],
                            ['file' => 'assets/characters/henkie.jpg', 'title' => 'Henkie Legende'],
                        ];
                    @endphp

                    @foreach($avatars as $av)
                        <label class="cursor-pointer group relative flex flex-col items-center p-1.5 rounded-xl border-2 transition {{ $profileCharacter->avatar === $av['file'] ? 'border-purple-600 bg-purple-50/50 shadow-xs' : 'border-slate-200 hover:border-slate-400 bg-white' }}">
                            <input type="radio" name="avatar" value="{{ $av['file'] }}" class="sr-only" {{ $profileCharacter->avatar === $av['file'] ? 'checked' : '' }}>
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg overflow-hidden relative shadow-2xs">
                                <img src="{{ asset($av['file']) }}" alt="{{ $av['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                @if($profileCharacter->avatar === $av['file'])
                                    <span class="absolute top-1 right-1 bg-purple-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] shadow-xs">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3 mt-4">
                    <button @click="avatarModalOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg transition shadow-xs">
                        Opslaan & Toepassen
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- MODAL 2: Bio & Status Editor -->
    <div x-show="bioModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="bioModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Bio & Status Aanpassen</h3>
                <button @click="bioModalOpen = false" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Citaat (Kort)</label>
                    <input type="text" name="status_message" value="{{ old('status_message', $profileCharacter->status_message) }}" maxlength="120" placeholder="bijv. Altijd op zoek naar een goede deal..." class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Patiënt Biografie</label>
                    <textarea name="bio" rows="5" maxlength="1000" placeholder="Vertel jouw verhaal in de TBS kliniek..." class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none">{{ old('bio', $profileCharacter->bio) }}</textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button @click="bioModalOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg transition shadow-xs">
                        Opslaan
                    </button>
                </div>
            </form>

        </div>
    </div>
    @endif

</div>
@endsection
