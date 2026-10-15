@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addFriendOpen: false, massMessageOpen: false, selectedFriendForRespect: null }">

    <!-- Clean TBS Kliniek Page Title Bar -->
    <div class="rounded-2xl bg-white p-6 sm:p-8 text-slate-800 shadow-xs border border-slate-200 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-2xl text-purple-600 shadow-2xs shrink-0">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">TBS Kliniek Sociaal</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                        Vrienden & Medepatiënten
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5 max-w-xl">
                        Bouw een betrouwbaar netwerk op binnen de muren. Stel een officiële BFF in, stuur 24-uurs omroepen en geef elkaar dagelijks respect via kliniek-verificatie.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="massMessageOpen = true" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs transition shadow-xs flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-amber-300"></i>
                    <span>Massa Bericht</span>
                </button>
                <button type="button" @click="addFriendOpen = true" class="px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-800 border border-slate-200 font-black text-xs transition shadow-2xs flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-purple-600"></i>
                    <span>Vriend Toevoegen</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Spotlight Row: BFF & Mass Message Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- BFF Showcase Card -->
        <div class="md:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-xs font-black">
                        <i class="fa-solid fa-heart"></i>
                    </span>
                    <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-tight">BFF (Beste Medepatiënt)</h2>
                </div>
                <span class="text-[10px] font-bold text-pink-600 bg-pink-50 border border-pink-100 px-2 py-0.5 rounded-full">Dubbel Respect Bonus</span>
            </div>

            @if($bff)
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                <a href="{{ route('profile.show', $bff->friend->name) }}" class="relative group shrink-0">
                    <img src="{{ $bff->friend->avatar_url }}" alt="{{ $bff->friend->name }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-pink-300 shadow-md group-hover:scale-105 transition-transform">
                    <span class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-pink-500 to-rose-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full border border-white shadow-xs inline-flex items-center gap-1">
                        <i class="fa-solid fa-heart text-[8px]"></i> <span>BFF</span>
                    </span>
                </a>
                <div class="flex-1 text-center sm:text-left space-y-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <a href="{{ route('profile.show', $bff->friend->name) }}" class="text-base font-black text-slate-900 hover:text-purple-600 transition">
                            {{ $bff->friend->name }}
                        </a>
                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">Level {{ $bff->friend->level }}</span>
                        @if($bff->friend->gang)
                        <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100">
                            [{{ $bff->friend->gang->tag }}] {{ $bff->friend->gang->name }}
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">
                        {{ $bff->friend->bio ?: 'Deze patiënt heeft nog geen biografie ingesteld in het kliniekdossier.' }}
                    </p>
                    <div class="pt-2 flex items-center justify-center sm:justify-start gap-2">
                        <a href="{{ route('messages.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-envelope text-blue-500"></i> Stuur Brief
                        </a>
                        <form action="{{ route('friends.toggle-bff', $bff) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition">
                                BFF Status Opheffen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <div class="p-6 text-center space-y-2 bg-purple-50/40 rounded-xl border border-purple-100/60 my-auto">
                <div class="w-12 h-12 mx-auto rounded-full bg-pink-100 text-pink-500 flex items-center justify-center text-xl">
                    <i class="fa-regular fa-heart"></i>
                </div>
                <h3 class="text-xs font-extrabold text-slate-800">Nog geen BFF geselecteerd</h3>
                <p class="text-[11px] text-slate-500 max-w-sm mx-auto">
                    Kies je meest trouwe bondgenoot uit je vriendenlijst hieronder om aan te wijzen als jouw officiële BFF. Een BFF levert dubbele respectbonussen op!
                </p>
            </div>
            @endif
        </div>

        <!-- Mass Message Info Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-black">
                            <i class="fa-solid fa-tower-broadcast"></i>
                        </span>
                        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-tight">Vrienden Omroep</h2>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500">1x per 24u</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Stuur met één klik een belangrijk bericht of oproep naar al je aangesloten vrienden tegelijkertijd. Handig voor bende-oproepen of deals!
                </p>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                @if($canMassMessage)
                <button type="button" @click="massMessageOpen = true" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-600 hover:to-indigo-600 text-white font-extrabold text-xs transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Massa Bericht Verzenden</span>
                </button>
                @else
                <div class="w-full py-2.5 px-4 rounded-xl bg-slate-100 text-slate-500 font-bold text-xs text-center flex items-center justify-center gap-2 cursor-not-allowed">
                    <i class="fa-solid fa-clock"></i>
                    <span>Nog {{ $massMessageCooldown }} uur cooldown</span>
                </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Inbound Pending Friend Requests -->
    @if($pendingRequests->isNotEmpty())
    <div class="bg-white rounded-2xl border-2 border-purple-300 shadow-md overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-purple-100 bg-purple-50/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm font-black shadow-xs">
                    <i class="fa-solid fa-user-clock"></i>
                </span>
                <div>
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <span>Inkomende Vriendschapsverzoeken</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-200 text-purple-900">{{ $pendingRequests->count() }}</span>
                    </h3>
                    <p class="text-xs text-slate-600">Deze medepatiënten willen een bondgenootschap met jou sluiten. Accepteer of weiger het verzoek.</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-purple-100">
            @foreach($pendingRequests as $req)
            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-purple-50/30 transition">
                <div class="flex items-center gap-3.5">
                    <a href="{{ route('profile.show', $req->character->name) }}">
                        <img src="{{ $req->character->avatar_url }}" alt="{{ $req->character->name }}" class="w-11 h-11 rounded-xl object-cover border-2 border-purple-200 shadow-xs hover:scale-105 transition-transform">
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('profile.show', $req->character->name) }}" class="font-black text-slate-900 hover:text-purple-600 transition text-sm">
                                {{ $req->character->name }}
                            </a>
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">Level {{ $req->character->level }}</span>
                            @if($req->character->gang)
                            <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100">
                                [{{ $req->character->gang->tag }}]
                            </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Verzoek verzonden {{ $req->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <form action="{{ route('friends.accept', $req) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs transition shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-check"></i> Accepteren
                        </button>
                    </form>
                    <form action="{{ route('friends.reject', $req) }}" method="POST"
                          data-confirm="Weet je zeker dat je dit vriendschapsverzoek wilt weigeren?"
                          data-confirm-title="Verzoek Weigeren"
                          data-confirm-btn="Weigeren"
                          data-confirm-type="warning">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 border border-slate-200 font-bold text-xs transition flex items-center gap-1.5">
                            <i class="fa-solid fa-xmark"></i> Weigeren
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Outbound Sent Friend Requests Pending -->
    @if($sentRequests->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-3.5 sm:p-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
            <h4 class="text-xs font-black text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-slate-400"></i>
                <span>Verzonden Vriendschapsverzoeken ({{ $sentRequests->count() }}) - Wachten op acceptatie</span>
            </h4>
        </div>
        <div class="p-3 sm:p-4 flex flex-wrap gap-2.5">
            @foreach($sentRequests as $sent)
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                <img src="{{ $sent->friend->avatar_url }}" alt="{{ $sent->friend->name }}" class="w-5 h-5 rounded-md object-cover">
                <span class="font-extrabold text-slate-800">{{ $sent->friend->name }}</span>
                <span class="text-[10px] text-amber-600 font-semibold">(Wachten op acceptatie)</span>
                <form action="{{ route('friends.reject', $sent) }}" method="POST" class="inline"
                      data-confirm="Wil je dit verzonden vriendschapsverzoek annuleren?"
                      data-confirm-title="Verzoek Annuleren"
                      data-confirm-btn="Annuleren"
                      data-confirm-type="warning">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-rose-600 ml-1" title="Verzoek annuleren">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Friends List Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users text-purple-600"></i>
                    <span>Mijn Vriendenlijst ({{ $friends->count() }})</span>
                </h3>
                <p class="text-xs text-slate-500">Patiënten waarmee je een vertrouwensband hebt opgebouwd in de kliniek.</p>
            </div>
            
            <button type="button" @click="addFriendOpen = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 text-xs font-bold hover:bg-purple-100 transition self-start sm:self-auto">
                <i class="fa-solid fa-plus"></i> Nieuwe Vriend Toevoegen
            </button>
        </div>

        @if($friends->isEmpty())
        <div class="p-12 text-center space-y-3">
            <div class="w-14 h-14 mx-auto rounded-full bg-purple-50 text-purple-400 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h4 class="text-sm font-black text-slate-800">Je hebt nog geen vrienden toegevoegd</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                Voeg medepatiënten toe om elkaar dagelijks respect te schenken en geheime brieven uit te wisselen.
            </p>
            <button type="button" @click="addFriendOpen = true" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-black hover:bg-slate-800 transition">
                Zoek Patiënten
            </button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50/30">
                        <th class="py-3 px-4">Patiënt</th>
                        <th class="py-3 px-4">Level</th>
                        <th class="py-3 px-4">Respect Status</th>
                        <th class="py-3 px-4">Relatie</th>
                        <th class="py-3 px-4 text-right">Acties</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($friends as $f)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="relative shrink-0">
                                    <a href="{{ route('profile.show', $f->friend->name) }}">
                                        <img src="{{ $f->friend->avatar_url }}" alt="{{ $f->friend->name }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-2xs hover:scale-105 transition-transform">
                                    </a>
                                    <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white {{ $f->friend->isOnline() ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $f->friend->online_status_label }}"></span>
                                </div>
                                <div>
                                    <a href="{{ route('profile.show', $f->friend->name) }}" class="font-extrabold text-slate-900 hover:text-purple-600 transition block">
                                        {{ $f->friend->name }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        @if($f->friend->gang)
                                        <span class="text-[10px] font-bold text-purple-600">
                                            [{{ $f->friend->gang->tag }}]
                                        </span>
                                        @endif
                                        <span class="text-[10px] {{ $f->friend->isOnline() ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">
                                            {{ $f->friend->isOnline() ? 'Online' : 'Offline' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-600">
                            Level {{ $f->friend->level }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-extrabold text-purple-700">
                                {{ number_format($f->friend->respect, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] text-slate-400">Respect</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($f->is_bff)
                            <span class="inline-flex items-center gap-1 text-[10px] font-black text-pink-700 bg-pink-100 border border-pink-200 px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-heart"></i> BFF
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                Vriend
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                
                                <!-- Daily Respect Button with Captcha Modal Trigger -->
                                @php
                                    $canRespect = ! $f->last_respected_at || $f->last_respected_at->diffInHours(now()) >= 24;
                                @endphp
                                @if($canRespect)
                                <button type="button" 
                                        @click="selectedFriendForRespect = { id: {{ $f->id }}, name: '{{ $f->friend->name }}', is_bff: {{ $f->is_bff ? 'true' : 'false' }} }"
                                        class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-[11px] transition shadow-2xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-award text-amber-300"></i>
                                    <span>Geef Respect</span>
                                </button>
                                @else
                                <span class="px-2.5 py-1 text-[10px] font-bold text-slate-400 bg-slate-100 rounded-lg">
                                    <i class="fa-solid fa-check text-emerald-500 mr-1"></i> Gerespecteerd
                                </span>
                                @endif

                                <!-- BFF Toggle Button -->
                                <form action="{{ route('friends.toggle-bff', $f) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            title="{{ $f->is_bff ? 'Verwijder BFF status' : 'Maak deze vriend mijn officiële BFF' }}"
                                            class="p-2 rounded-xl border text-xs font-bold transition {{ $f->is_bff ? 'bg-pink-50 border-pink-200 text-pink-600 hover:bg-pink-100' : 'bg-slate-50 border-slate-200 text-slate-400 hover:text-pink-600 hover:bg-pink-50' }}">
                                        <i class="fa-solid fa-heart"></i>
                                    </button>
                                </form>

                                <!-- Send message link -->
                                <a href="{{ route('messages.index', ['recipient' => $f->friend->name]) }}" 
                                   title="Brief sturen naar {{ $f->friend->name }}"
                                   class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 text-xs transition">
                                    <i class="fa-solid fa-envelope"></i>
                                </a>

                                <!-- Delete Friend -->
                                <form action="{{ route('friends.remove', $f) }}" method="POST"
                                      data-confirm="Weet je zeker dat je {{ $f->friend->name }} uit je vriendenlijst wilt verwijderen?"
                                      data-confirm-title="Vriend Verwijderen"
                                      data-confirm-btn="Verwijderen"
                                      data-confirm-type="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Vriend verwijderen" class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-rose-600 hover:bg-rose-50 text-xs transition">
                                        <i class="fa-solid fa-user-xmark"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- RESPECT CAPTCHA MODAL -->
    <div x-show="selectedFriendForRespect !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="selectedFriendForRespect = null" class="bg-white rounded-2xl shadow-2xl border border-purple-200 max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-award"></i>
                    </span>
                    <h3 class="text-sm font-extrabold text-slate-900">
                        Respect Geven aan <span class="text-purple-700" x-text="selectedFriendForRespect?.name"></span>
                    </h3>
                </div>
                <button type="button" @click="selectedFriendForRespect = null" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <template x-if="selectedFriendForRespect">
                <form :action="'{{ url('/vrienden') }}/' + selectedFriendForRespect.id + '/respect'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-100 text-xs text-purple-900 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">Beloning voor je vriend:</span>
                            <span class="font-black text-purple-700" x-text="selectedFriendForRespect.is_bff ? '+2 Respect (BFF Bonus!)' : '+1 Respect'"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold">XP voor jou:</span>
                            <span class="font-black text-indigo-700">+50 XP</span>
                        </div>
                    </div>

                    <!-- TBS Anti-Bot Security Captcha -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-black text-slate-800">
                                <i class="fa-solid fa-shield-halved text-purple-600 mr-1"></i> TBS Kliniek Beveiligingscontrole
                            </label>
                            <span class="text-[10px] font-bold text-slate-400">Anti-script</span>
                        </div>
                        <p class="text-[11px] text-slate-500">
                            Los deze rekensom op om te bewijzen dat je een echte patiënt bent en geen geautomatiseerd script:
                        </p>
                        
                        <div class="flex items-center gap-3">
                            <div class="px-4 py-2 bg-purple-900 text-white font-mono font-black text-base rounded-xl border border-purple-700 tracking-wider select-none shadow-xs">
                                {{ $numA }} + {{ $numB }} = ?
                            </div>
                            <input type="number" name="captcha_answer" required autofocus placeholder="Antwoord"
                                   class="flex-1 text-sm font-black bg-white border-2 border-purple-300 text-slate-900 rounded-xl px-3.5 py-2 focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="selectedFriendForRespect = null" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Annuleren
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-extrabold bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-600 hover:to-indigo-600 text-white rounded-xl transition shadow-sm inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-fire text-amber-300"></i>
                            <span>Respect Bevestigen</span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ADD FRIEND MODAL (with Autocomplete Search Dropdown) -->
    <div x-show="addFriendOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="addFriendOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-purple-600"></i>
                    <span>Medepatiënt Toevoegen als Vriend</span>
                </h3>
                <button type="button" @click="addFriendOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('friends.add') }}" method="POST" class="space-y-4">
                @csrf
                <div x-data="{
                    search: '',
                    selectedName: '',
                    openDropdown: false,
                    patients: {{ $availablePatients->map(fn($p) => ['name' => $p->name, 'level' => $p->level, 'avatar' => $p->avatar_url])->toJson() }},
                    get filtered() {
                        if (!this.search.trim()) return this.patients.slice(0, 20);
                        return this.patients.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase())).slice(0, 20);
                    },
                    select(name) {
                        this.selectedName = name;
                        this.search = name;
                        this.openDropdown = false;
                    }
                }" class="relative">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Zoek Medepatiënt op Naam</label>
                    <input type="hidden" name="friend_name" :value="selectedName || search" required>
                    
                    <div class="relative flex items-center">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-purple-500 text-xs z-10">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input type="text"
                               x-model="search"
                               @focus="openDropdown = true"
                               @input="openDropdown = true; selectedName = ''"
                               @click.outside="openDropdown = false"
                               autocomplete="off"
                               placeholder="Typ om een speler te zoeken..."
                               class="w-full text-xs font-semibold bg-slate-50 border border-purple-300 text-slate-800 rounded-xl py-2.5 focus:bg-white focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none transition tbs-search-input"
                               style="padding-left: 2.75rem !important; padding-right: 2.25rem !important;">
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-slate-400 text-xs z-10">
                            <i class="fa-solid fa-chevron-down transition-transform duration-150" :class="openDropdown ? 'rotate-180' : ''"></i>
                        </div>
                    </div>

                    <!-- Autocomplete Dropdown -->
                    <div x-show="openDropdown" 
                         x-transition
                         class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-purple-200 divide-y divide-slate-100">
                        <template x-if="filtered.length === 0">
                            <div class="p-3 text-xs text-slate-400 text-center">
                                Geen patiënt gevonden met '<span x-text="search"></span>'
                            </div>
                        </template>
                        <template x-for="p in filtered" :key="p.name">
                            <button type="button" 
                                    @click="select(p.name)"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-purple-50/70 text-left transition group">
                                <div class="flex items-center gap-2.5">
                                    <img :src="p.avatar" class="w-6 h-6 rounded-full object-cover border border-purple-100">
                                    <span class="text-xs font-bold text-slate-800 group-hover:text-purple-700" x-text="p.name"></span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full" x-text="'Level ' + p.level"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="addFriendOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-extrabold bg-purple-700 hover:bg-purple-600 text-white rounded-xl transition shadow-xs">
                        Toevoegen als Vriend
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MASS MESSAGE MODAL -->
    <div x-show="massMessageOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="massMessageOpen = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-bullhorn"></i>
                    </span>
                    <h3 class="text-sm font-extrabold text-slate-900">
                        Massa Bericht Versturen (1x per 24u)
                    </h3>
                </div>
                <button type="button" @click="massMessageOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @if(! $canMassMessage)
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center gap-3">
                <i class="fa-solid fa-clock text-amber-600 text-lg"></i>
                <div>
                    <div>Cooldown actief</div>
                    <div class="text-[11px] font-normal text-amber-700">Je hebt in de afgelopen 24 uur al een omroep gestuurd. Wacht nog {{ $massMessageCooldown }} uur.</div>
                </div>
            </div>
            @endif

            <form action="{{ route('friends.mass-message') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Onderwerp</label>
                    <input type="text" name="subject" required placeholder="Bijv. Oproep aan alle vertrouwelingen!"
                           class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-purple-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bericht</label>
                    <textarea name="message" required rows="4" placeholder="Schrijf je bericht dat naar al je vrienden verstuurd zal worden..."
                              class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl p-3 focus:bg-white focus:border-purple-600 outline-none resize-none"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-[11px] text-slate-400">Ontvangers: {{ $friends->count() }} vrienden</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="massMessageOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Sluiten
                        </button>
                        <button type="submit" @disabled(! $canMassMessage || $friends->isEmpty()) 
                                class="px-5 py-2 text-xs font-extrabold bg-purple-700 hover:bg-purple-600 disabled:opacity-50 text-white rounded-xl transition shadow-xs">
                            Verzenden
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Podium Ranglijst (Kliniek Respect Top 10, Clean TBS Style) -->
    @if(isset($topRankings) && $topRankings->count())
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <span>KLINIEK RESPECT RANGLIJST (TOP 10)</span>
            </div>
            <a href="{{ route('leaderboard.index') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800 transition">
                Volledige ranglijst bekijken &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings as $idx => $ranked)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $ranked->avatar_url }}" alt="{{ $ranked->name }}" 
                         onerror="this.onerror=null; this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}';"
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $idx === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($idx === 1 ? 'border-purple-400' : 'border-slate-200') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <span class="absolute -top-1 -left-1 w-5 h-5 rounded-full {{ $idx === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($idx < 3 ? 'bg-purple-600 text-white font-bold' : 'bg-slate-800 text-white font-bold') }} text-[11px] flex items-center justify-center shadow-xs">
                        {{ $idx + 1 }}
                    </span>

                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <a href="{{ route('profile.show', $ranked->name) }}" class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition truncate w-full block" title="{{ $ranked->name }}">
                    {{ $ranked->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400">
                    {{ number_format($ranked->respect, 0, ',', '.') }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
