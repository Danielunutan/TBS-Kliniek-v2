@extends('layouts.app')

@section('title', 'Controlepunten (King of the Hill) - TBS Kliniek')

@section('content')
<div class="space-y-6 pb-12" x-data="{
    now: new Date().getTime(),
    init() {
        setInterval(() => {
            this.now = new Date().getTime();
        }, 1000);
    }
}">

    <!-- HERO HEADER -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white flex items-center justify-center text-2xl shadow-lg shadow-amber-500/20 shrink-0">
                    <i class="fa-solid fa-flag"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Kliniek Controlepunten</h1>
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800 font-black text-[10px] uppercase tracking-wider animate-pulse">
                            King of the Hill
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                        Verover strategische hotspots binnen de TBS Kliniek en blijf de baas! Zolang jij een controlepunt bezit, bouwt jouw buit elke minuut automatisch op met <strong class="text-emerald-600 dark:text-emerald-400 font-black">+€50</strong> en <strong class="text-amber-500 font-black">+1 Ster ⭐</strong>. Zodra een rivaal jou van de troon stoot, wordt de volledige opgebouwde buit direct op jouw saldo bijgeschreven!
                    </p>
                </div>
            </div>

            <!-- QUICK STATS -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 shrink-0">
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/60 text-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">In Jouw Bezit</span>
                    <span class="text-lg font-black {{ $userHeldCount > 0 ? 'text-emerald-500' : 'text-slate-700 dark:text-slate-200' }}">
                        {{ $userHeldCount }} / {{ $points->count() }}
                    </span>
                </div>
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/60 text-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Tarief / Minuut</span>
                    <span class="text-xs font-black text-amber-500">
                        +€50 & +1 ⭐
                    </span>
                </div>
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/60 text-center col-span-2 sm:col-span-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Kosten Claim</span>
                    <span class="text-xs font-black text-indigo-500">
                        2⚡ Energie
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    

    <!-- 5 CONTROLEPUNTEN GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($points as $point)
            @php
                $isHolder = $character && $point->current_character_id === $character->id;
                $heldMinutes = $point->getHeldMinutes();
                $pendingCash = $point->getPendingCash();
                $pendingStars = $point->getPendingStars();
            @endphp
            <div class="bg-white dark:bg-slate-900 border {{ $isHolder ? 'border-amber-400 dark:border-amber-500 shadow-lg shadow-amber-500/10 ring-2 ring-amber-400/20' : 'border-slate-200 dark:border-slate-800 shadow-xs' }} rounded-2xl p-5 flex flex-col justify-between transition relative overflow-hidden group">
                
                @if($isHolder)
                <div class="absolute -top-3 -right-3 px-6 py-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white text-[10px] font-black uppercase tracking-widest shadow-md rotate-12 flex items-center gap-1.5 z-20">
                    <i class="fa-solid fa-crown text-amber-200 text-xs"></i>
                    <span>JOUW PUNT</span>
                </div>
                @endif

                <div>
                    <!-- TOP BAR: ICON & BADGE -->
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl {{ $isHolder ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-purple-600 dark:text-purple-400' }} flex items-center justify-center text-xl shrink-0 shadow-inner">
                                <i class="fa-solid {{ $point->icon }}"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-black text-slate-900 dark:text-white leading-tight">
                                    {{ $point->name }}
                                </h2>
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-location-dot text-indigo-500 text-[10px]"></i>
                                    {{ $point->location_name }}
                                </span>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $point->current_character_id ? ($isHolder ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60' : 'bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-700/60') : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700/60' }}">
                            {{ $point->current_character_id ? ($isHolder ? 'In jouw bezit' : 'Bezet') : 'Vrij' }}
                        </span>
                    </div>

                    <!-- DESCRIPTION -->
                    <p class="text-xs text-slate-600 dark:text-slate-300 mb-4 leading-relaxed line-clamp-2">
                        {{ $point->description }}
                    </p>

                    <!-- CURRENT HOLDER BOX -->
                    <div class="p-3.5 rounded-xl {{ $isHolder ? 'bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60' : 'bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800' }} mb-4">
                        @if($point->holder)
                            <div class="flex items-center gap-3">
                                <img src="{{ $point->holder->avatar_url }}" 
                                     alt="{{ $point->holder->name }}" 
                                     class="w-10 h-10 rounded-xl object-cover border {{ $isHolder ? 'border-amber-400' : 'border-slate-300 dark:border-slate-700' }} shrink-0">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('profile.show', $point->holder->name) }}" class="text-xs font-black {{ $isHolder ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} hover:underline truncate">
                                            {{ $point->holder->name }}
                                        </a>
                                        @if($point->holder->gang)
                                            <span class="text-[9px] font-black uppercase text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/80 px-1.5 py-0.2 rounded border border-purple-200 dark:border-purple-800">
                                                [{{ $point->holder->gang->tag }}]
                                            </span>
                                        @endif
                                        <span class="text-[10px] font-extrabold text-slate-400">Lvl {{ $point->holder->level }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                                        <span><i class="fa-regular fa-clock text-[10px]"></i> Al <strong>{{ $heldMinutes }}m</strong> de baas</span>
                                    </div>
                                </div>
                            </div>

                            <!-- ACCUMULATING POT -->
                            <div class="mt-3 pt-3 border-t {{ $isHolder ? 'border-amber-200/60 dark:border-amber-800/40' : 'border-slate-200/60 dark:border-slate-700/40' }} flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ $isHolder ? 'Jouw opgebouwde buit:' : 'Opgebouwde buit:' }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                        +€{{ number_format($pendingCash, 0, ',', '.') }}
                                    </span>
                                    <span class="text-xs font-black text-amber-500 flex items-center gap-0.5">
                                        +{{ $pendingStars }} <i class="fa-solid fa-star text-[10px]"></i>
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="py-2 text-center text-xs text-slate-400 italic">
                                <i class="fa-solid fa-hourglass-start mr-1"></i> Nog geen heerser! Wees de allereerste die dit controlepunt opeist.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ACTION BUTTON -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-3">
                    <span class="text-[10px] text-slate-400 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-bolt text-indigo-500"></i> Kost {{ $point->energy_cost }}⚡
                    </span>

                    @if($isHolder)
                        <button disabled 
                                class="px-4 py-2 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/40 font-black text-xs uppercase tracking-wider cursor-not-allowed flex items-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            <span>Jij Heerst Hier!</span>
                        </button>
                    @elseif($character && $character->isImprisoned())
                        <button disabled 
                                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-black text-xs uppercase tracking-wider cursor-not-allowed flex items-center gap-2">
                            <i class="fa-solid fa-lock"></i>
                            <span>Isoleercel</span>
                        </button>
                    @elseif($character && $character->energy < $point->energy_cost)
                        <button disabled 
                                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-black text-xs uppercase tracking-wider cursor-not-allowed flex items-center gap-2">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Te Weinig Energie</span>
                        </button>
                    @else
                        <form method="POST" action="{{ route('control-points.capture', $point) }}">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-black text-xs uppercase tracking-wider transition shadow-sm shadow-amber-500/20 flex items-center gap-2 hover:scale-[1.02] active:scale-[0.98]">
                                <i class="fa-solid fa-hand-fist"></i>
                                <span>{{ $point->current_character_id ? 'Overnemen' : 'Opeisen' }}</span>
                            </button>
                        </form>
                    @endif
                </div>

            </div>
        @endforeach
    </div>

    <!-- RECENT TAKEOVER LOGS -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Recent Overgenomen Punten & Uitbetalingen</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Volledige historiek van heerschappij en automatische uitbetalingen aan onttroonde heersers.</p>
                </div>
            </div>
            <span class="text-xs font-bold text-slate-400">{{ $logs->count() }} gebeurtenissen</span>
        </div>

        @if($logs->isEmpty())
            <div class="p-8 text-center text-xs text-slate-400 italic">
                Er zijn nog geen controlepunten overgenomen. Wees de eerste die toeslaat!
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] uppercase font-black tracking-wider text-slate-400">
                            <th class="py-3 px-3">Tijdstip</th>
                            <th class="py-3 px-3">Controlepunt</th>
                            <th class="py-3 px-3">Vorige Heerser</th>
                            <th class="py-3 px-3">Overgenomen Door</th>
                            <th class="py-3 px-3 text-center">Duur</th>
                            <th class="py-3 px-3 text-right">Uitbetaalde Buit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($logs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-3 text-slate-400 text-[11px] whitespace-nowrap">
                                {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-flag text-amber-500 text-[10px]"></i>
                                    {{ $log->controlPoint ? $log->controlPoint->name : 'Controlepunt' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($log->character)
                                    <a href="{{ route('profile.show', $log->character->name) }}" class="font-extrabold text-slate-800 dark:text-slate-200 hover:text-purple-600 dark:hover:text-purple-400">
                                        {{ $log->character->name }}
                                    </a>
                                    @if($log->gang)
                                        <span class="text-[9px] font-black uppercase text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/80 px-1 py-0.2 rounded ml-1">
                                            [{{ $log->gang->tag }}]
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($log->overtakenBy)
                                    <a href="{{ route('profile.show', $log->overtakenBy->name) }}" class="font-extrabold text-amber-600 dark:text-amber-400 hover:underline">
                                        {{ $log->overtakenBy->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap font-bold text-slate-600 dark:text-slate-300">
                                {{ $log->duration_minutes }} min
                            </td>
                            <td class="py-3 px-3 text-right whitespace-nowrap">
                                <span class="font-black text-emerald-600 dark:text-emerald-400">+€{{ number_format($log->reward_cash, 0, ',', '.') }}</span>
                                <span class="font-black text-amber-500 ml-1.5">+{{ $log->reward_stars }} ⭐</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- SPELREGELS & HOW IT WORKS -->
    <div class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/50 text-xs text-amber-900 dark:text-amber-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center text-sm shrink-0 mt-0.5">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div>
                <strong class="font-black text-sm text-amber-950 dark:text-amber-100 block">Hoe werken Controlepunten?</strong>
                <p class="mt-0.5 text-amber-800 dark:text-amber-300 leading-relaxed">
                    1. Klik op <strong>Opeisen/Overnemen</strong> om de controle over een plek te grijpen (kost 2⚡ energie).<br>
                    2. Jij behoudt het controlepunt <em>totdat een andere speler het van jou afpakt</em>.<br>
                    3. Elke minuut telt mee: <strong>1 minuut = +€50 en +1 ⭐</strong> (10 min = €500 + 10⭐, 60 min = €3.000 + 60⭐, etc.).<br>
                    4. De buit stopt met tellen zodra iemand anders het overneemt. Op dat exacte moment word je <strong>direct uitbetaald</strong> en ontvang je een notificatie én postbericht!
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
