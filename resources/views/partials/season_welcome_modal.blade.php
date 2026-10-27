@auth
@if(Auth::user()->character)
<div x-data="{
    open: false,
    copiedCode: null,
    init() {
        if (!localStorage.getItem('tbs_season2_welcomed_v2')) {
            this.open = true;
        }
    },
    closeModal() {
        localStorage.setItem('tbs_season2_welcomed_v2', 'true');
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
class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity" @click="closeModal()"></div>

    <!-- Modal Box -->
    <div class="relative w-full max-w-xl bg-gradient-to-b from-[#180d33] via-[#0f0724] to-[#0a0418] border-2 border-amber-500/70 rounded-3xl p-6 sm:p-8 text-white shadow-2xl overflow-hidden z-10 animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Ambient Glow FX -->
        <div class="absolute -top-16 -left-16 w-48 h-48 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-52 h-52 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header -->
        <div class="relative z-10 text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/20 border border-amber-400/50 text-amber-300 text-xs font-black uppercase tracking-widest shadow-inner">
                <i class="fa-solid fa-fire text-amber-400"></i> Seizoen 2 Officieel Geopend
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-100 to-amber-400 tracking-tight">
                Welkom in het Nieuwe Seizoen!
            </h2>
            <p class="text-xs sm:text-sm text-purple-200/80 max-w-md mx-auto leading-relaxed">
                De deuren van de TBS Kliniek zijn heropend. Iedereen begint weer gelijk. Gebruik deze 3 officiële kluiscodes voor een vliegende start in Seizoen 2!
            </p>
        </div>

        <!-- 3 Codes List -->
        <div class="relative z-10 mt-6 space-y-3">
            
            <!-- Code 1 -->
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white/5 border border-purple-500/30 hover:border-amber-400/60 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 group">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg text-amber-300 tracking-wider">SEIZOEN2</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-purple-900/60 text-purple-200">Starter</span>
                    </div>
                    <p class="text-xs text-purple-200/80">
                        +€2.500 bank &bull; +1 Ster ⭐ &bull; +3 KluisMunten &bull; +2 Maskers
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('SEIZOEN2')" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-md">
                    <i class="fa-solid" :class="copiedCode === 'SEIZOEN2' ? 'fa-check text-emerald-950' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'SEIZOEN2' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

            <!-- Code 2 -->
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white/5 border border-purple-500/30 hover:border-amber-400/60 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 group">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg text-amber-300 tracking-wider">HERSTART2026</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-900/60 text-emerald-200">Cash Booster</span>
                    </div>
                    <p class="text-xs text-purple-200/80">
                        +€5.000 bank &bull; +5 KluisMunten &bull; +3 Maskers &bull; +10 Respect
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('HERSTART2026')" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-md">
                    <i class="fa-solid" :class="copiedCode === 'HERSTART2026' ? 'fa-check text-emerald-950' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'HERSTART2026' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

            <!-- Code 3 -->
            <div class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-purple-950/60 to-amber-950/40 border border-amber-500/50 hover:border-amber-400 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 group">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg text-amber-300 tracking-wider">TBSKLINIEK2</span>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-amber-400 text-slate-950 uppercase">Mega Starter</span>
                    </div>
                    <p class="text-xs text-purple-200/80">
                        +€10.000 bank &bull; +2 Sterren ⭐ &bull; +5 KluisMunten &bull; +5 Maskers
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('TBSKLINIEK2')" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-md">
                    <i class="fa-solid" :class="copiedCode === 'TBSKLINIEK2' ? 'fa-check text-emerald-950' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'TBSKLINIEK2' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="relative z-10 mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-purple-500/20">
            <a href="{{ route('vault.index') }}" 
               @click="closeModal()" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-xs transition cursor-pointer flex items-center justify-center gap-2 shadow-lg">
                <i class="fa-solid fa-vault"></i> Naar de Kluis om In te Wisselen
            </a>
            
            <button type="button" 
                    @click="closeModal()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-purple-200 font-extrabold text-xs transition cursor-pointer text-center">
                Sluiten &amp; Spelen
            </button>
        </div>

    </div>
</div>
@endif
@endauth
