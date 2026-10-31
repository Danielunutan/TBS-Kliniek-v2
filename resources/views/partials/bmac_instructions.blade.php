@php
    $character = $character ?? (Auth::check() ? Auth::user()->character : null);
@endphp
@if($character)
<!-- DUIDELIJKE BUY ME A COFFEE INSTRUCTIES (Licht & Donker Modus Geoptimaliseerd) -->
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
        const toastFn = window.tbsToast || window.tbsShowToast;
        if (toastFn) {
            toastFn((type === 'name' ? 'Spelersnaam' : 'Koppelcode') + ' gekopieerd naar klembord!', 'success');
        }
        setTimeout(() => { this.copiedType = null; }, 3500);
    }
}" 
class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xs space-y-6">

    <!-- Top Headline Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-5">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 text-[10px] font-black uppercase tracking-wider">
                    <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400"></i>
                    Belangrijke Instructie voor Donaties &amp; Sterren
                </span>
            </div>
            <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">
                Hoe voeg je jouw spelersnaam toe op Buy Me a Coffee?
            </h3>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-medium max-w-2xl leading-relaxed">
                Volg deze 3 stappen zodat onze automatische koppeling direct weet naar welk account jouw Sterren, Koffers en VIP verstuurd moeten worden. Zo maak je nooit een vergissing!
            </p>
        </div>
        <div class="shrink-0">
            <a href="https://buymeacoffee.com/tbskliniek" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="px-4 py-2.5 rounded-xl btn-star-gold text-slate-900 dark:text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-mug-hot"></i>
                <span>Open Buy Me a Coffee</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- 3 Snelle Stappen Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Stap 1: Kopiëren -->
        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 flex flex-col justify-between space-y-3">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs font-black shrink-0">1</span>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Kopieer Jouw Gegevens</h4>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Klik op één van de knoppen hieronder om jouw spelersnaam of koppelcode direct te kopiëren.
                </p>
            </div>

            <div class="space-y-2 pt-2">
                <!-- Spelersnaam Knop -->
                <button type="button" 
                        @click="copyText(@js($character->name), 'name')"
                        class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-700 hover:border-purple-400 dark:hover:border-purple-600 text-left flex items-center justify-between text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-user text-purple-600 dark:text-purple-400 text-xs"></i>
                        <span class="truncate text-slate-900 dark:text-white font-mono font-black">{{ $character->name }}</span>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md shrink-0 border"
                          :class="copiedType === 'name' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70'">
                        <span x-show="copiedType === 'name'">✓ Gekopieerd!</span>
                        <span x-show="copiedType !== 'name'">Kopieer Naam</span>
                    </span>
                </button>

                <!-- Koppelcode Knop -->
                <button type="button" 
                        @click="copyText('TBS-{{ $character->id }}', 'code')"
                        class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-600 text-left flex items-center justify-between text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-key text-amber-500 text-xs"></i>
                        <span class="truncate text-slate-900 dark:text-white font-mono font-black">TBS-{{ $character->id }}</span>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md shrink-0 border"
                          :class="copiedType === 'code' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70'">
                        <span x-show="copiedType === 'code'">✓ Gekopieerd!</span>
                        <span x-show="copiedType !== 'code'">Kopieer Code</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Stap 2: Invulveld Demonstratie -->
        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 flex flex-col justify-between space-y-3 md:col-span-2">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs font-black shrink-0">2</span>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Zet in het BERICHT-veld (Say something nice) en NIET als naam!</h4>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Op Buy Me a Coffee zie je bij het afrekenen twee velden: <em>Name</em> en <strong>Say something nice / Message (Bericht)</strong>. Zet jouw spelersnaam of code <strong>altijd in het BERICHT-veld</strong>!
                </p>
            </div>

            <!-- Visueel Voorbeeld van het Invulveld (Mockup) -->
            <div class="bg-white dark:bg-slate-900 border-2 border-dashed border-amber-400/80 dark:border-amber-500/70 rounded-xl p-3.5 space-y-3 shadow-inner">
                <div class="space-y-1 opacity-75">
                    <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">1. Veld: Name (Vrij te kiezen)</label>
                    <div class="w-full bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Je eigen voornaam of nickname (bijv. Anoniem of Donateur)
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wide flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-right text-xs"></i> 2. Veld: Say something nice... (BERICHT)
                        </label>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 text-[10px] font-black">
                            ✓ HIER INVULLEN!
                        </span>
                    </div>
                    <div class="w-full bg-emerald-50/50 dark:bg-emerald-950/30 border-2 border-emerald-500 rounded-lg p-2.5 flex items-center justify-between gap-2 shadow-xs">
                        <span class="font-mono text-xs sm:text-sm font-black text-emerald-700 dark:text-emerald-300 truncate">
                            {{ $character->name }}
                        </span>
                        <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 shrink-0">
                            (of TBS-{{ $character->id }})
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-1.5 text-[11px] text-rose-700 dark:text-rose-300 font-bold bg-rose-50 dark:bg-rose-950/40 p-2.5 rounded-lg border border-rose-200/80 dark:border-rose-800/70">
                    <i class="fa-solid fa-triangle-exclamation text-xs mt-0.5 shrink-0"></i>
                    <span>LET OP: Zet het in het BERICHT en niet als naam! Onze automatische scanner leest het bericht-veld uit om jouw account direct te vinden.</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Stap 3 & Automatische Verwerking -->
    <div class="p-3.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-700 dark:text-purple-200">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-xs">
                <i class="fa-solid fa-bolt"></i>
            </span>
            <span class="leading-relaxed">
                <strong>Stap 3: Directe Activatie!</strong> Reken veilig af via iDEAL of creditcard. Ons systeem herkent jouw betaling automatisch en voegt jouw Sterren en VIP direct toe aan jouw account.
            </span>
        </div>
        <a href="https://buymeacoffee.com/tbskliniek" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="font-black text-purple-600 dark:text-purple-400 underline shrink-0 hover:text-purple-700 dark:hover:text-purple-300 transition">
            Ga direct naar Buy Me a Coffee &rarr;
        </a>
    </div>

</div>
@endif
