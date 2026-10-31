@auth
@if(Auth::user()->character)
<div x-data="{
    open: false,
    copiedCode: null,
    init() {
        if (!localStorage.getItem('tbs_season1_welcomed_v3')) {
            this.open = true;
        }
    },
    closeModal() {
        localStorage.setItem('tbs_season1_welcomed_v3', 'true');
        this.open = false;
    },
    copyCode(code) {
        navigator.clipboard.writeText(code);
        this.copiedCode = code;
        setTimeout(() => {
            if (this.copiedCode === code) this.copiedCode = null;
        }, 2500);
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
    <div class="relative w-full max-w-xl rounded-2xl max-h-[85dvh] overflow-y-auto p-6 sm:p-8 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 text-slate-800 dark:text-slate-100 shadow-2xl">
        
        <!-- Header -->
        <div class="relative text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                <i class="fa-solid fa-fire text-amber-500"></i>
                <span>Seizoen 1 Officieel Geopend</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                Welkom in het Nieuwe Seizoen!
            </h2>
            <p class="text-xs sm:text-sm max-w-md mx-auto leading-relaxed text-slate-600 dark:text-slate-300">
                De deuren van de TBS Kliniek zijn heropend. Iedereen begint weer gelijk. Gebruik deze 3 officiële kluiscodes voor een vliegende start in Seizoen 1!
            </p>
        </div>

        <!-- 3 Codes List -->
        <div class="relative mt-6 space-y-3">
            
            <!-- Code 1 -->
            <div class="p-3.5 sm:p-4 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg tracking-wider text-amber-600 dark:text-amber-400">SEIZOEN1</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">Starter</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        +€2.500 bank &bull; +1 Ster ⭐ &bull; +3 KluisMunten &bull; +2 Maskers
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('SEIZOEN1')" 
                        class="btn-star-gold w-full sm:w-auto px-4 py-2 rounded-xl font-black text-xs text-slate-900 dark:text-white transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-xs">
                    <i class="fa-solid" :class="copiedCode === 'SEIZOEN1' ? 'fa-check' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'SEIZOEN1' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

            <!-- Code 2 -->
            <div class="p-3.5 sm:p-4 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg tracking-wider text-amber-600 dark:text-amber-400">HERSTART2026</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70">Cash Booster</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        +€5.000 bank &bull; +5 KluisMunten &bull; +3 Maskers &bull; +10 Respect
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('HERSTART2026')" 
                        class="btn-star-gold w-full sm:w-auto px-4 py-2 rounded-xl font-black text-xs text-slate-900 dark:text-white transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-xs">
                    <i class="fa-solid" :class="copiedCode === 'HERSTART2026' ? 'fa-check' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'HERSTART2026' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

            <!-- Code 3 -->
            <div class="p-3.5 sm:p-4 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-amber-50/60 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700/70 shadow-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg tracking-wider text-amber-600 dark:text-amber-400">TBSKLINIEK1</span>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-md uppercase bg-amber-500 text-slate-950 border border-amber-400">Mega Starter</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        +€10.000 bank &bull; +2 Sterren ⭐ &bull; +5 KluisMunten &bull; +5 Maskers
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('TBSKLINIEK1')" 
                        class="btn-star-gold w-full sm:w-auto px-4 py-2 rounded-xl font-black text-xs text-slate-900 dark:text-white transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-xs">
                    <i class="fa-solid" :class="copiedCode === 'TBSKLINIEK1' ? 'fa-check' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'TBSKLINIEK1' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="relative mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-200/80 dark:border-slate-800">
            <a href="{{ route('vault.index') }}" 
               @click="closeModal()" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-2 shadow-xs">
                <i class="fa-solid fa-vault"></i>
                <span>Naar de Kluis om In te Wisselen</span>
            </a>
            
            <button type="button" 
                    @click="closeModal()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer text-center shadow-xs">
                Sluiten &amp; Spelen
            </button>
        </div>

    </div>
</div>
@endif
@endauth
