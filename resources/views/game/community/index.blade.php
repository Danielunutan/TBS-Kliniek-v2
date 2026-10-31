@extends('layouts.app')

@section('title', 'Community Hub & Volg-Beloningen | TBS Kliniek')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    claiming: null,
    async claimPlatform(platformKey, url, formId) {
        // Open the social media page in a new window/tab
        window.open(url, '_blank', 'noopener,noreferrer');

        // Submit the claim form
        this.claiming = platformKey;
        const form = document.getElementById(formId);
        if (form) {
            form.submit();
        }
    }
}">

    <!-- HERO HEADER CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm relative overflow-hidden space-y-6">
        <!-- Ambient Glow Accents -->
        <div class="absolute -top-20 -left-20 w-48 h-48 bg-purple-500/10 dark:bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -right-20 w-48 h-48 bg-amber-500/10 dark:bg-amber-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2 max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60 font-black text-[10px] uppercase tracking-wider shadow-2xs">
                    <i class="fa-solid fa-gift text-purple-600 dark:text-purple-400"></i>
                    <span>Community Hub & Beloningen</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    Volg Onze Socials & Claim Gratis Cash
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Blijf op de hoogte van de nieuwste updates, doe mee aan exclusieve giveaways en help TBS Kliniek groeien. Volg ons op onderstaande kanalen en ontvang per direct een gratis welkomstbeloning op je personage!
                </p>
            </div>

            <!-- Total Potential Rewards Showcase -->
            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-purple-50 via-indigo-50/50 to-amber-50/50 dark:from-purple-950/40 dark:via-slate-800/80 dark:to-amber-950/20 border border-purple-200 dark:border-purple-800/60 shrink-0 text-center sm:text-left space-y-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-purple-300/80 block">
                    Totaal Te Verdienen
                </span>
                <div class="flex items-center justify-center sm:justify-start gap-4">
                    <div>
                        <span class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono block leading-none">
                            € {{ number_format($totalPossibleCash, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-slate-400">Gratis Cash</span>
                    </div>
                    <div class="h-8 w-px bg-slate-200 dark:bg-purple-800/40"></div>
                    <div>
                        <span class="text-lg sm:text-xl font-black text-indigo-600 dark:text-indigo-400 font-mono block leading-none">
                            +{{ $totalPossibleRespect }}
                        </span>
                        <span class="text-[10px] text-slate-400">Respect</span>
                    </div>
                    <div class="h-8 w-px bg-slate-200 dark:bg-purple-800/40"></div>
                    <div>
                        <span class="text-lg sm:text-xl font-black text-amber-500 font-mono block leading-none">
                            +90
                        </span>
                        <span class="text-[10px] text-slate-400">Energie</span>
                    </div>
                </div>

                @if(Auth::check())
                <div class="pt-2 border-t border-purple-200/60 dark:border-purple-800/40">
                    <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                        <span class="text-slate-600 dark:text-slate-300">Jouw Voortgang</span>
                        <span class="text-purple-700 dark:text-purple-300 font-extrabold">{{ $claimedCount }} / {{ $totalPlatforms }} Geclaimd</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-purple-950/80 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-600 to-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ round(($claimedCount / max(1, $totalPlatforms)) * 100) }}%"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- PLATFORMS GRID (6 TILES) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($platforms as $key => $p)
        @php
            $isClaimed = isset($claims[$key]);
        @endphp
        <div class="bg-white dark:bg-slate-900 border {{ $isClaimed ? 'border-emerald-300/80 dark:border-emerald-800/60 bg-emerald-50/10' : 'border-slate-200 dark:border-slate-800' }} rounded-2xl p-5 shadow-xs flex flex-col justify-between space-y-4 relative overflow-hidden transition hover:shadow-md">
            
            <div class="space-y-3">
                <!-- Header with Icon & Name -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if($p['icon'] === 'hyves-h')
                            <div class="w-12 h-12 rounded-2xl bg-[#f36f21] text-white flex items-center justify-center font-black text-xl shadow-xs shrink-0">
                                H
                            </div>
                        @else
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-2xl shadow-xs shrink-0 {{ ($p['color'] ?? '') === '#000000' ? 'dark:!text-white' : '' }}" style="color: {{ $p['color'] }};">
                                <i class="{{ $p['icon'] }}"></i>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight">
                                {{ $p['name'] }}
                            </h3>
                            <span class="text-[11px] font-bold text-slate-400 block mt-0.5">
                                {{ $p['badge'] }}
                            </span>
                        </div>
                    </div>

                    @if($isClaimed)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 font-black text-[10px] uppercase tracking-wider shrink-0 shadow-2xs">
                            <i class="fa-solid fa-check text-xs"></i> Geclaimd
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    {{ $p['description'] }}
                </p>

                <!-- Rewards List Pills -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/50 space-y-1.5">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">
                        Beloning bij Volgen
                    </span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 font-extrabold text-[11px]">
                            <i class="fa-solid fa-money-bill-wave text-[10px]"></i> +€ {{ number_format($p['reward']['cash'], 0, ',', '.') }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-950/70 text-indigo-800 dark:text-indigo-300 font-extrabold text-[11px]">
                            <i class="fa-solid fa-fire text-[10px]"></i> +{{ $p['reward']['respect'] }} Respect
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 font-extrabold text-[11px]">
                            <i class="fa-solid fa-bolt text-[10px]"></i> +{{ $p['reward']['energy'] }} Energie
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Button Area -->
            <div class="pt-2">
                @if($isClaimed)
                    <div class="w-full py-2.5 px-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 font-bold text-xs flex items-center justify-center gap-2 select-none">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Beloning Ontvangen</span>
                    </div>
                @elseif(Auth::check())
                    <form id="claim-form-{{ $key }}" action="{{ route('community.claim', $key) }}" method="POST" class="w-full">
                        @csrf
                        <button type="button" 
                                @click="claimPlatform('{{ $key }}', '{{ $p['url'] }}', 'claim-form-{{ $key }}')"
                                :disabled="claiming === '{{ $key }}'"
                                class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition shadow-xs flex items-center justify-center gap-2 cursor-pointer active:scale-98">
                            <span x-show="claiming !== '{{ $key }}'" class="flex items-center gap-2">
                                <span>{{ $p['action_label'] }}</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                            </span>
                            <span x-show="claiming === '{{ $key }}'" style="display: none;" class="flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                <span>Bezig met verwerken...</span>
                            </span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" 
                       class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition shadow-xs flex items-center justify-center gap-2">
                        <span>Log in om te Claimen</span>
                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    </a>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    <!-- Extra Community Bonus & Discord Perks Information -->
    <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-purple-50 via-indigo-50/70 to-amber-50/40 dark:bg-gradient-to-r dark:from-purple-950/70 dark:via-indigo-950/80 dark:to-slate-900 border border-purple-200 dark:border-purple-800/50 text-slate-800 dark:text-white shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 border border-amber-300 dark:bg-amber-400 dark:text-slate-950 dark:border-transparent flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-900 dark:text-white">
                    TBS Kliniek Ambassadeur Bonus
                </h4>
                <p class="text-xs text-slate-600 dark:text-purple-200/90 leading-relaxed">
                    Volg je ons op alle 6 platforms? Dan maak je automatisch kans op de maandelijkse trekking van <strong>100 VIP Punten</strong> en exclusieve in-game titels!
                </p>
            </div>
        </div>

        <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-950 font-black text-xs transition shrink-0 flex items-center gap-2 shadow-xs cursor-pointer active:scale-95">
            <i class="fa-brands fa-discord text-white dark:text-indigo-600"></i>
            <span>Praat Mee op Discord</span>
        </a>
    </div>

</div>
@endsection
