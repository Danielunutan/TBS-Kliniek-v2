@auth
@if(Auth::user()->character && \App\Services\EventHustleChallengeService::isActive())
@php
    $promoChar = Auth::user()->character;
    $promoMyEntries = \App\Models\EventHustleEntry::where('character_id', $promoChar->id)->count();
@endphp
<div x-data="{
    open: false,
    init() {
        try {
            if (!localStorage.getItem('tbs_hustle_challenge_promo_v1')) {
                setTimeout(() => {
                    this.open = true;
                }, 400);
            }
        } catch(e) {
            setTimeout(() => {
                this.open = true;
            }, 400);
        }
    },
    closeModal() {
        try {
            localStorage.setItem('tbs_hustle_challenge_promo_v1', 'true');
        } catch(e) {}
        this.open = false;
    }
}" 
x-show="open" 
x-cloak
class="fixed inset-0 flex items-center justify-center p-3 pb-24 sm:p-4 lg:pb-4 overflow-y-auto"
style="display: none; z-index: 99999;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 transition-opacity" 
         @click="closeModal()"
         style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"></div>

    <!-- Solid Modal Box (TBS Kliniek Clean Theme) -->
    <div class="relative w-full max-w-lg rounded-2xl max-h-[88dvh] overflow-y-auto p-6 sm:p-8 bg-white dark:bg-slate-900 border-2 border-emerald-500/80 dark:border-emerald-500 text-slate-800 dark:text-slate-100 shadow-2xl space-y-5">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-emerald-600 text-white shadow-xs">
                <i class="fa-solid fa-trophy text-amber-300"></i>
                <span>Tijdelijk Event &bull; Tot Vrijdag 23:59</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                De Grote Hossel Challenge!
            </h2>
            <p class="text-xs sm:text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                Laat zien wie de grootste strateeg en hosselaar van de TBS Kliniek is. Hossel mee, beklim de live ranglijst en win de gigantische hoofdprijs!
            </p>
        </div>

        <!-- Prize Pool Highlight Box -->
        <div class="p-5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-emerald-300 dark:border-emerald-600/70 text-center space-y-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-400">TOTALE HOOFDPRIJS VOOR DE #1</span>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-700/60 text-center">
                    <span class="text-[10px] font-black uppercase text-amber-700 dark:text-amber-400 block mb-0.5">Gouden Sterren</span>
                    <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">
                        ⭐ 750
                    </span>
                </div>
                <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-700/60 text-center">
                    <span class="text-[10px] font-black uppercase text-emerald-700 dark:text-emerald-400 block mb-0.5">Contant Bankgeld</span>
                    <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        €150M
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs font-bold pt-1 border-t border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                <span>Jouw score tot nu toe:</span>
                <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ number_format($promoMyEntries, 0, ',', '.') }} beurten</span>
            </div>
        </div>

        <!-- Spelregels / How to play -->
        <div class="space-y-2 rounded-xl bg-slate-50/70 dark:bg-slate-800/50 p-4 border border-slate-200 dark:border-slate-700/70 text-xs text-slate-600 dark:text-slate-300">
            <span class="text-[11px] font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 block">
                <i class="fa-solid fa-list-check text-emerald-500 mr-1.5"></i> Zo doe je mee:
            </span>
            <ul class="space-y-1.5 list-disc list-inside text-slate-600 dark:text-slate-300 pl-1">
                <li>Voer succesvolle zware klussen uit onder <strong>Hosselen</strong> (Hossel 4, 5 of 6).</li>
                <li>Iedere geslaagde zware hossel telt direct mee als +1 punt voor de ranglijst.</li>
                <li>De speler met de meeste voltooide beurten op <strong>vrijdag 13 november om 23:59:59</strong> wint de complete prijzenpot!</li>
            </ul>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row items-center gap-3 pt-1">
            <a href="{{ route('hustle.index') }}" 
               @click="closeModal()"
               class="w-full sm:flex-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider text-center transition active:scale-95 shadow-xs flex items-center justify-center gap-2 touch-manipulation">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span>Direct Naar Hosselen</span>
            </a>
            <a href="{{ route('news.show', 'de-grote-hossel-challenge-750-sterren-150m') }}" 
               @click="closeModal()"
               class="w-full sm:w-auto py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition active:scale-95 cursor-pointer touch-manipulation flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-ranking-star text-amber-500"></i>
                <span>Ranglijst</span>
            </a>
            <button type="button" 
                    @click="closeModal()"
                    class="w-full sm:w-auto py-3 px-4 rounded-xl bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold text-xs transition cursor-pointer touch-manipulation">
                Sluiten
            </button>
        </div>

    </div>

</div>
@endif
@endauth

