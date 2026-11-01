@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner (TBS Kliniek Website Styling) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-7 border border-slate-200/90 dark:border-slate-800 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-slate-800 border border-purple-200 dark:border-slate-700 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs mt-0.5">
                    <i class="fa-solid fa-radio"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-50 dark:bg-purple-950/40 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-700/60">
                            <i class="fa-solid fa-tower-broadcast"></i> Piratenzender 104.8 FM
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 animate-pulse">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Live On Air
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        TBS FM: De Afdelingsradio
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed font-medium">
                        De geheime piratenzender van de inrichting! Vraag een smartlap aan, daag een rivaliserende bende uit, of stuur een shoutout naar je mede-patiënten over de afdelingsspeakers voor €150 kleingeld.
                    </p>
                </div>
            </div>

            <!-- Stats Badges -->
            <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center min-w-[100px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Shoutouts</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white leading-none font-mono">
                        {{ $shoutouts->total() }}x
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 rounded-xl bg-purple-50/90 dark:bg-purple-950/50 border border-purple-200/80 dark:border-purple-800/70 text-center min-w-[110px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400 block mb-0.5">Tarief</span>
                    <span class="text-lg font-black text-purple-600 dark:text-purple-400 leading-none font-mono">
                        €150
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    

    <!-- Top Hit van Vandaag (Indien aanwezig) -->
    @if($topShoutout)
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-amber-200/90 dark:border-amber-900/60 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 min-w-0 flex-1">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Meest Gewaardeerde Plaat Vandaag</span>
                <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate">"{{ $topShoutout->track_title }}"</h4>
                <p class="text-xs text-slate-600 dark:text-slate-300 italic mt-0.5 break-words">"{{ $topShoutout->message }}" — <strong class="text-slate-800 dark:text-slate-100">{{ $topShoutout->is_anonymous ? 'Anonieme Patiënt' : $topShoutout->character_name }}</strong></p>
            </div>
        </div>
        <div class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-extrabold text-xs">
            <i class="fa-solid fa-heart text-amber-500"></i> {{ $topShoutout->likes_count }}
        </div>
    </div>
    @endif

    <!-- Formulier & Radio Feed Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Linker kolom: Formulier Plaat & Shoutout Aanvragen -->
        <div class="lg:col-span-5">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5 lg:sticky lg:top-20">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-microphone-lines text-purple-600 dark:text-purple-400"></i>
                        <h3 class="text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">Aanvraag Inzenden</h3>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kost €150 kleingeld &bull; Klinkt direct op de afdeling</p>
                </div>

                <form action="{{ route('radio.shoutout') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kies je Plaat:</label>
                        <select name="track_title" required class="w-full text-base sm:text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 font-medium transition">
                            @foreach($tracks as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jouw Radio Shoutout (max 200 tekens):</label>
                        <textarea name="message" rows="3" required maxlength="200" placeholder="Bijv: Groetjes aan Paviljoen 7, handjes thuis van de frikandelbroodjes!" class="w-full text-base sm:text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 p-3 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition resize-none"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                        <label for="is_anonymous" class="text-xs text-slate-600 dark:text-slate-400 font-medium cursor-pointer">Anoniem uitzenden (geheime zender)</label>
                    </div>

                    <div class="pt-2">
                        @if($shoutoutCooldown > 0)
                            <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                                <i class="fa-solid fa-clock"></i> Microfoon Bezet (Nog {{ $shoutoutCooldown }}s)
                            </button>
                        @elseif($character->cash < 150)
                            <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                                <i class="fa-solid fa-money-bill"></i> Te weinig cash (Vereist: €150)
                            </button>
                        @elseif($character->isImprisoned() || $character->isComatose())
                            <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                                <i class="fa-solid fa-ban"></i> Niet Beschikbaar (Isoleercel / Code Blauw)
                            </button>
                        @else
                            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider shadow-xs transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-tower-broadcast"></i> Zend Shoutout Uit (€150)
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Rechter kolom: Live Ether Feed -->
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wide text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-headphones text-purple-500"></i> Recente Uitzendingen
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">Laatste 20 nummers</span>
            </div>

            @if($shoutouts->isEmpty())
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-8 text-center text-slate-400 text-xs shadow-xs">
                    <i class="fa-solid fa-radio text-3xl mb-2 text-slate-300 dark:text-slate-700 block"></i>
                    Er klinkt momenteel alleen ruis uit de speakers. Wees de eerste die een plaatje aanvraagt!
                </div>
            @else
                <div class="space-y-3">
                    @foreach($shoutouts as $shout)
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition flex flex-col justify-between gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm shrink-0 {{ $shout->is_anonymous ? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/80 dark:border-slate-700' : 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70' }}">
                                        <i class="fa-solid {{ $shout->is_anonymous ? 'fa-user-secret' : 'fa-compact-disc' }}"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap min-w-0">
                                            <span class="text-xs font-black text-slate-900 dark:text-white truncate">
                                                {{ $shout->is_anonymous ? 'Anonieme Patiënt' : $shout->character_name }}
                                            </span>
                                            @if($shout->gang_name && !$shout->is_anonymous)
                                                <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-700/50 shrink-0">
                                                    {{ $shout->gang_name }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1.5 mt-0.5 min-w-0">
                                            <i class="fa-solid fa-music text-[9px] shrink-0"></i>
                                            <span class="truncate">{{ $shout->track_title }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 whitespace-nowrap shrink-0">{{ $shout->created_at->diffForHumans() }}</span>
                            </div>

                            <p class="text-xs text-slate-700 dark:text-slate-200 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 leading-snug break-words font-medium">
                                "{{ $shout->message }}"
                            </p>

                            <div class="flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800 text-xs">
                                <span class="text-[11px] text-slate-400 font-medium">
                                    <i class="fa-solid fa-signal text-emerald-500 mr-1"></i> TBS FM Ether
                                </span>

                                <form action="{{ route('radio.like', $shout) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            {{ $character->isImprisoned() || $character->isComatose() ? 'disabled' : '' }}
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition {{ $character->isImprisoned() || $character->isComatose() ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }} {{ $shout->isLikedBy($character->id) ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800' : 'bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                                        <i class="fa-solid fa-heart {{ $shout->isLikedBy($character->id) ? 'text-rose-500' : 'text-slate-400' }}"></i>
                                        <span>{{ $shout->likes_count }} Respect</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-2">
                        {{ $shoutouts->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
