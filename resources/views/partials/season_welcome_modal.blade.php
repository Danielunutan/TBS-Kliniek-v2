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
class="fixed inset-0 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
style="display: none; z-index: 9999;">
    
    <!-- Solid Dark Backdrop (guaranteed opaque, cannot bleed through) -->
    <div class="fixed inset-0 transition-opacity" 
         @click="closeModal()"
         style="background-color: rgba(4, 2, 14, 0.92); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"></div>

    <!-- Solid Modal Box -->
    <div class="relative w-full max-w-xl rounded-3xl p-6 sm:p-8 text-white shadow-2xl overflow-hidden"
         style="background-color: #0e0724; border: 2px solid #f59e0b; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 35px rgba(245, 158, 11, 0.25);">
        
        <!-- Header -->
        <div class="relative text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-widest"
                 style="background-color: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.6); color: #fcd34d;">
                <i class="fa-solid fa-fire" style="color: #f59e0b;"></i> Seizoen 1 Officieel Geopend
            </div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight"
                style="color: #ffffff; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
                Welkom in het Nieuwe Seizoen!
            </h2>
            <p class="text-xs sm:text-sm max-w-md mx-auto leading-relaxed"
               style="color: #d8b4fe;">
                De deuren van de TBS Kliniek zijn heropend. Iedereen begint weer gelijk. Gebruik deze 3 officiële kluiscodes voor een vliegende start in Seizoen 1!
            </p>
        </div>

        <!-- 3 Codes List -->
        <div class="relative mt-6 space-y-3">
            
            <!-- Code 1 -->
            <div class="p-3.5 sm:p-4 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
                 style="background-color: #170d38; border: 1px solid rgba(168, 85, 247, 0.35);">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg tracking-wider" style="color: #fcd34d;">SEIZOEN1</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="background-color: #3b0764; color: #e9d5ff;">Starter</span>
                    </div>
                    <p class="text-xs" style="color: #e9d5ff;">
                        +€2.500 bank &bull; +1 Ster ⭐ &bull; +3 KluisMunten &bull; +2 Maskers
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('SEIZOEN1')" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl font-black text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-md"
                        style="background-color: #f59e0b; color: #020617;">
                    <i class="fa-solid" :class="copiedCode === 'SEIZOEN1' ? 'fa-check' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'SEIZOEN1' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

            <!-- Code 2 -->
            <div class="p-3.5 sm:p-4 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
                 style="background-color: #170d38; border: 1px solid rgba(168, 85, 247, 0.35);">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg tracking-wider" style="color: #fcd34d;">HERSTART2026</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="background-color: #064e3b; color: #a7f3d0;">Cash Booster</span>
                    </div>
                    <p class="text-xs" style="color: #e9d5ff;">
                        +€5.000 bank &bull; +5 KluisMunten &bull; +3 Maskers &bull; +10 Respect
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('HERSTART2026')" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl font-black text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-md"
                        style="background-color: #f59e0b; color: #020617;">
                    <i class="fa-solid" :class="copiedCode === 'HERSTART2026' ? 'fa-check' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'HERSTART2026' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

            <!-- Code 3 -->
            <div class="p-3.5 sm:p-4 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
                 style="background: linear-gradient(135deg, #220f4f 0%, #170d38 100%); border: 1px solid rgba(245, 158, 11, 0.6);">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-base sm:text-lg tracking-wider" style="color: #fcd34d;">TBSKLINIEK1</span>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-md uppercase" style="background-color: #f59e0b; color: #020617;">Mega Starter</span>
                    </div>
                    <p class="text-xs" style="color: #e9d5ff;">
                        +€10.000 bank &bull; +2 Sterren ⭐ &bull; +5 KluisMunten &bull; +5 Maskers
                    </p>
                </div>
                <button type="button" 
                        @click="copyCode('TBSKLINIEK1')" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl font-black text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-md"
                        style="background-color: #f59e0b; color: #020617;">
                    <i class="fa-solid" :class="copiedCode === 'TBSKLINIEK1' ? 'fa-check' : 'fa-copy'"></i>
                    <span x-text="copiedCode === 'TBSKLINIEK1' ? 'Gekopieerd!' : 'Kopieer Code'">Kopieer Code</span>
                </button>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="relative mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 pt-3"
             style="border-top: 1px solid rgba(168, 85, 247, 0.25);">
            <a href="{{ route('vault.index') }}" 
               @click="closeModal()" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-white font-black text-xs transition cursor-pointer flex items-center justify-center gap-2 shadow-lg"
               style="background: linear-gradient(to right, #9333ea, #4f46e5);">
                <i class="fa-solid fa-vault"></i> Naar de Kluis om In te Wisselen
            </a>
            
            <button type="button" 
                    @click="closeModal()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl font-extrabold text-xs transition cursor-pointer text-center"
                    style="background-color: rgba(255, 255, 255, 0.1); color: #d8b4fe;">
                Sluiten &amp; Spelen
            </button>
        </div>

    </div>
</div>
@endif
@endauth
