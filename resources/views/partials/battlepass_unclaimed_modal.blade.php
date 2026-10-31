@auth
@php
    $bpPopupChar = Auth::user()->character;
    $bpUnclaimedCount = ($bpPopupChar && !request()->routeIs('battlepass.*'))
        ? $bpPopupChar->getUnclaimedBattlePassCount()
        : 0;
@endphp

@if($bpPopupChar && $bpUnclaimedCount > 0)
<div x-data="{
        open: false,
        unclaimed: {{ $bpUnclaimedCount }},
        storageKey: 'tbs_bp_popup_dismissed_{{ $bpPopupChar->id }}',
        init() {
            try {
                const raw = sessionStorage.getItem(this.storageKey);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    // Only suppress if dismissed in the last 30 minutes and unclaimed count has not increased
                    if (parsed && parsed.count >= this.unclaimed && (Date.now() - parsed.time) < (30 * 60 * 1000)) {
                        return;
                    }
                }
            } catch (e) {}

            setTimeout(() => {
                this.open = true;
            }, 900);
        },
        dismiss() {
            this.open = false;
            try {
                sessionStorage.setItem(this.storageKey, JSON.stringify({
                    count: this.unclaimed,
                    time: Date.now()
                }));
            } catch (e) {}
        }
     }"
     x-show="open"
     x-cloak
     style="display: none; z-index: 9995;"
     class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">

    <!-- Dark Blurred Backdrop -->
    <div class="fixed inset-0 transition-opacity"
         @click="dismiss()"
         style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"></div>

    <!-- Modal Card (TBS Kliniek Battle Pass Stijl) -->
    <div class="relative w-full max-w-md rounded-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 text-slate-800 dark:text-slate-100 shadow-2xl modal-accelerated">

        <!-- Artwork Header -->
        <div class="relative h-32 overflow-hidden bg-slate-950 flex flex-col justify-between p-4 border-b border-purple-500/30">
            <div class="absolute inset-0 bg-cover bg-center opacity-45"
                 style="background-image: url('{{ asset('assets/illustrations/tbs_purple_hero.jpg') }}');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-purple-950/60 to-transparent"></div>

            <div class="relative z-10 flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/50 text-amber-300 text-[10px] font-black uppercase tracking-wider">
                    <i class="fa-solid fa-gift animate-bounce"></i>
                    <span>Ongeclaimde Battle Pass Buit!</span>
                </span>
                <button type="button"
                        @click="dismiss()"
                        class="w-7 h-7 rounded-full bg-slate-900/80 hover:bg-purple-800 text-slate-300 hover:text-white border border-purple-400/30 flex items-center justify-center text-xs transition cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="relative z-10 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-purple-500 to-fuchsia-600 border border-purple-200/50 flex items-center justify-center text-white text-lg font-black shadow-lg shrink-0">
                    <i class="fa-solid fa-trophy text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                        Er {{ $bpUnclaimedCount === 1 ? 'wacht 1 beloning' : "wachten {$bpUnclaimedCount} beloningen" }} op je!
                    </h3>
                    <p class="text-[11px] text-purple-200 font-medium">
                        TBS Kliniek Battle Pass &bull; Level {{ $bpPopupChar->level }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4">
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Je hebt op je huidige level (<strong class="text-slate-900 dark:text-white">Level {{ $bpPopupChar->level }}</strong>) nog <strong class="text-amber-600 dark:text-amber-400">{{ $bpUnclaimedCount }} ongeclaimde Battle Pass {{ $bpUnclaimedCount === 1 ? 'prijs' : 'prijzen' }}</strong> klaarliggen in je kluis! Haal direct je gratis <strong class="text-slate-900 dark:text-white">Geld, XP, Kracht, Respect, Codes en Sterren</strong> op.
            </p>

            <!-- Reward Preview Chips -->
            <div class="flex flex-wrap gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-700 dark:text-emerald-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-sack-dollar text-[10px]"></i> Cash
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-700 dark:text-indigo-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-bolt-lightning text-[10px]"></i> +XP
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-700 dark:text-rose-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-dumbbell text-[10px]"></i> +Kracht
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/60 border border-sky-200/80 dark:border-sky-800/70 text-sky-700 dark:text-sky-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-key text-[10px]"></i> Codes
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-star text-[10px]"></i> Sterren
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2 pt-1">
                <a href="{{ route('battlepass.index') }}"
                   class="btn-star-gold w-full py-2.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-gifts"></i>
                    <span>Open Battle Pass &amp; Claim ({{ $bpUnclaimedCount }})</span>
                </a>

                <div class="flex items-center gap-2">
                    @if(!$bpPopupChar->isVip() || $bpPopupChar->getSupporterTier() < 3)
                        <a href="{{ route('vip.index', ['tab' => 'pakketten']) }}"
                           class="flex-1 py-2 px-3 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/50 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300 text-[11px] font-black uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-xs transition-all duration-150 active:scale-95 cursor-pointer text-center">
                            <i class="fa-solid fa-crown text-amber-500"></i>
                            <span>VIP Lidmaatschap Winkel</span>
                        </a>
                    @endif
                    <button type="button"
                            @click="dismiss()"
                            class="py-2 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-[11px] font-bold shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                        Later
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endauth

