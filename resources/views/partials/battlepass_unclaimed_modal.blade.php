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
         style="background-color: rgba(8, 4, 20, 0.85); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"></div>

    <!-- Modal Card (Paarse TBS Kliniek Battle Pass Stijl) -->
    <div class="relative w-full max-w-md rounded-3xl overflow-hidden text-white border-2 border-purple-400/60 shadow-2xl modal-accelerated"
         style="background: linear-gradient(145deg, #160933 0%, #0d0520 100%); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.9), 0 0 35px rgba(168, 85, 247, 0.35);">

        <!-- Artwork Header -->
        <div class="relative h-32 overflow-hidden bg-slate-950 flex flex-col justify-between p-4 border-b border-purple-500/30">
            <div class="absolute inset-0 bg-cover bg-center opacity-45"
                 style="background-image: url('{{ asset('assets/illustrations/tbs_purple_hero.jpg') }}');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#160933] via-purple-950/60 to-transparent"></div>

            <div class="relative z-10 flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-400/50 text-amber-300 text-[10px] font-black uppercase tracking-wider">
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
            <p class="text-xs text-purple-100/90 leading-relaxed">
                Je hebt op je huidige level (<strong class="text-white">Level {{ $bpPopupChar->level }}</strong>) nog <strong class="text-amber-300">{{ $bpUnclaimedCount }} ongeclaimde Battle Pass {{ $bpUnclaimedCount === 1 ? 'prijs' : 'prijzen' }}</strong> klaarliggen in je kluis! Haal direct je gratis <strong class="text-white">Geld, XP, Kracht, Respect, Kluiscodex en Sterren</strong> op.
            </p>

            <!-- Reward Preview Chips -->
            <div class="flex flex-wrap gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/15 border border-emerald-500/35 text-emerald-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-sack-dollar text-[10px]"></i> Cash
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-500/15 border border-indigo-500/35 text-indigo-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-bolt-lightning text-[10px]"></i> +XP
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-orange-500/15 border border-orange-500/35 text-orange-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-dumbbell text-[10px]"></i> +Kracht
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-yellow-500/15 border border-yellow-500/35 text-yellow-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-key text-[10px]"></i> Kluiscodex
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/15 border border-amber-500/35 text-amber-300 text-[11px] font-extrabold">
                    <i class="fa-solid fa-star text-[10px]"></i> Sterren
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2 pt-1">
                <a href="{{ route('battlepass.index') }}"
                   class="btn-star-gold w-full py-2.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="fa-solid fa-gifts"></i>
                    <span>Open Battle Pass &amp; Claim ({{ $bpUnclaimedCount }})</span>
                </a>

                <div class="flex items-center gap-2">
                    @if(!$bpPopupChar->isVip() || $bpPopupChar->getSupporterTier() < 3)
                        <a href="{{ route('vip.index', ['tab' => 'pakketten']) }}"
                           class="flex-1 py-2 px-3 rounded-xl bg-purple-800/60 hover:bg-purple-700/70 border border-purple-400/40 text-purple-100 text-[11px] font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition cursor-pointer text-center">
                            <i class="fa-solid fa-crown text-amber-400"></i>
                            <span>VIP Lidmaatschap Winkel</span>
                        </a>
                    @endif
                    <button type="button"
                            @click="dismiss()"
                            class="py-2 px-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-[11px] font-bold transition cursor-pointer">
                        Later
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endauth
