@auth
@if(Auth::user()->character)
@php
    $modalLotteryRound = \App\Models\LotteryRound::where('is_drawn', false)->orderByDesc('id')->first();
@endphp
<div x-data="{
    open: false,
    init() {
        if (!localStorage.getItem('tbs_lottery_discount_50_v1')) {
            setTimeout(() => {
                this.open = true;
            }, 500);
        }
    },
    closeModal() {
        localStorage.setItem('tbs_lottery_discount_50_v1', 'true');
        this.open = false;
    }
}" 
x-show="open" 
x-cloak
class="fixed inset-0 flex items-center justify-center p-3 pb-24 sm:p-4 lg:pb-4 overflow-y-auto"
style="display: none; z-index: 9999;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 transition-opacity" 
         @click="closeModal()"
         style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"></div>

    <!-- Solid Modal Box -->
    <div class="relative w-full max-w-lg rounded-2xl max-h-[88dvh] overflow-y-auto p-6 sm:p-8 bg-white dark:bg-slate-900 border-2 border-amber-400 dark:border-amber-500/80 text-slate-800 dark:text-slate-100 shadow-2xl space-y-5">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-rose-500 text-white shadow-xs animate-pulse">
                <i class="fa-solid fa-bolt"></i>
                <span>Tijdelijke Mega Loterij Actie</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                50% Korting op alle Loten!
            </h2>
            <p class="text-xs sm:text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                Grijp nu je kans op de megapot van de kliniek. Alle loterijloten zijn per direct <strong>gehalveerd in prijs</strong> én <strong>100% van de inleg</strong> stroomt rechtstreeks in de jackpot!
            </p>
        </div>

        @if($modalLotteryRound)
        <!-- Pot Highlight -->
        <div class="p-4 rounded-xl bg-amber-50/90 dark:bg-slate-800/80 border border-amber-300/80 dark:border-amber-500/60 text-center space-y-1">
            <span class="text-[10px] font-black uppercase tracking-widest text-amber-800 dark:text-amber-400">HUIDIGE JACKPOT POT</span>
            <div class="text-3xl font-black font-mono text-amber-600 dark:text-amber-400">
                €{{ number_format($modalLotteryRound->jackpot, 0, ',', '.') }}
            </div>
            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 block">
                <i class="fa-solid fa-arrow-trend-up mr-1"></i> Ieder verkocht lot verhoogt de pot met 100% van het bedrag!
            </span>
        </div>
        @endif

        <!-- Prijzen overzicht -->
        <div class="grid grid-cols-2 gap-3 text-center">
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <span class="text-[10px] font-black uppercase text-slate-400 block">Contant Lot</span>
                <span class="line-through text-slate-400 text-xs block">€10.000</span>
                <span class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">€5.000</span>
                <span class="text-[9px] font-black text-rose-500 uppercase block mt-0.5">50% KORTING</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <span class="text-[10px] font-black uppercase text-slate-400 block">VIP Sterren Lot</span>
                <span class="line-through text-slate-400 text-xs block">15 Sterren</span>
                <span class="text-base font-black text-amber-600 dark:text-amber-400 font-mono">8 Sterren</span>
                <span class="text-[9px] font-black text-rose-500 uppercase block mt-0.5">+€25.000 POT BONUS</span>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
            <a href="{{ route('lottery.index') }}" 
               @click="closeModal()"
               class="w-full sm:flex-1 py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs uppercase tracking-wider text-center transition active:scale-95 shadow-xs flex items-center justify-center gap-2 touch-manipulation">
                <i class="fa-solid fa-ticket"></i>
                <span>Direct Naar De Loterij</span>
            </a>
            <button type="button" 
                    @click="closeModal()"
                    class="w-full sm:w-auto py-3 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition active:scale-95 cursor-pointer touch-manipulation">
                Begrepen
            </button>
        </div>

    </div>

</div>
@endif
@endauth
