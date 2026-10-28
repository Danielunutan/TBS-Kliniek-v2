<!-- BUY ME A COFFEE SNELBANNER VOOR WINKEL ITEMS (Licht & Donker Modus) -->
<div x-data="{
    copiedType: null,
    copyText(text, type) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
        this.copiedType = type;
        if (window.tbsShowToast) {
            window.tbsShowToast((type === 'name' ? 'Spelersnaam' : 'Koppelcode') + ' gekopieerd!', 'success');
        }
        setTimeout(() => { this.copiedType = null; }, 3500);
    }
}" 
class="bg-white dark:bg-slate-900 border-2 border-amber-400 dark:border-amber-500/70 rounded-xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">

    <!-- Left info -->
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg shrink-0 border border-amber-300 dark:border-amber-800">
            <i class="fa-solid fa-mug-hot"></i>
        </div>
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 text-[10px] font-black uppercase tracking-wider">
                    Sterren of VIP Nodig?
                </span>
                <span class="text-xs font-black text-slate-900 dark:text-white">
                    Zet jouw naam of koppelcode altijd in het BERICHT-veld (Say something nice)!
                </span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                Vul bij het afrekenen jouw spelersnaam <strong class="text-purple-700 dark:text-purple-300 font-mono">{{ $character->name }}</strong> of code <strong class="text-amber-600 dark:text-amber-400 font-mono">TBS-{{ $character->id }}</strong> in het <strong>BERICHT-veld</strong> in (en niet als naam), zodat onze webhook dit direct automatisch herkent.
            </p>
        </div>
    </div>

    <!-- Right Quick Actions -->
    <div class="flex items-center gap-2 shrink-0 flex-wrap sm:flex-nowrap">
        <button type="button" 
                @click="copyText('{{ $character->name }}', 'name')"
                class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer border border-slate-300 dark:border-slate-700 shadow-2xs">
            <i class="fa-regular fa-copy text-purple-600 dark:text-purple-400 text-xs"></i>
            <span x-show="copiedType === 'name'" class="text-emerald-600 dark:text-emerald-400 font-black">✓ Gekopieerd!</span>
            <span x-show="copiedType !== 'name'">Kopieer Naam</span>
        </button>

        <button type="button" 
                @click="copyText('TBS-{{ $character->id }}', 'code')"
                class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer border border-slate-300 dark:border-slate-700 shadow-2xs">
            <i class="fa-solid fa-key text-amber-500 text-xs"></i>
            <span x-show="copiedType === 'code'" class="text-emerald-600 dark:text-emerald-400 font-black">✓ Gekopieerd!</span>
            <span x-show="copiedType !== 'code'">Kopieer Code</span>
        </button>

        <button type="button" 
                @click="setShopTab('donaties')" 
                class="px-3.5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer shrink-0">
            <i class="fa-solid fa-star text-xs"></i>
            <span>Bekijk Instructies &amp; Pakketten</span>
        </button>
    </div>

</div>
