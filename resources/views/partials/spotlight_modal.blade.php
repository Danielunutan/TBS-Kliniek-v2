<!-- GLOBAL SPOTLIGHT COMMAND PALETTE & FAST SEARCH (CTRL + K / ⌘K) -->
<div x-data="tbsSpotlightModal()"
     x-cloak
     @keydown.window.prevent.ctrl.k="toggleModal()"
     @keydown.window.prevent.cmd.k="toggleModal()"
     @keydown.escape.window="closeModal()">

    <!-- Backdrop & Modal Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[80] flex items-start justify-center p-4 pb-24 sm:p-6 md:p-12 lg:pb-12 overflow-y-auto bg-slate-950/80"
         @click.self="closeModal()">

        <!-- Spotlight Dialog Card -->
        <div x-show="isOpen"
             role="dialog"
             aria-modal="true"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
             class="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden text-slate-800 dark:text-slate-100 my-auto transform-gpu will-change-transform">

            <!-- Search Header Bar -->
            <div class="relative flex items-center px-4 py-3.5 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-800/60">
                <i class="fa-solid fa-magnifying-glass text-purple-600 dark:text-purple-400 text-base mr-3 shrink-0"></i>
                <input type="text"
                       x-ref="searchInput"
                       x-model="query"
                       @input="selectedIndex = 0"
                       @keydown.down.prevent="navigateDown()"
                       @keydown.up.prevent="navigateUp()"
                       @keydown.enter.prevent="selectHighlighted()"
                       placeholder="Zoek actie, locatie, minigame of functie... (bijv. 'hosselen', 'bank', 'bende')"
                       class="w-full bg-transparent text-sm sm:text-base font-bold text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none">
                
                <!-- Clear Button -->
                <button x-show="query.length > 0" 
                        @click="query = ''; selectedIndex = 0; $refs.searchInput.focus()"
                        class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg transition-colors mr-2 cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>

                <!-- Keyboard Hint -->
                <kbd class="hidden sm:inline-flex items-center gap-1 text-[10px] font-mono font-bold text-slate-400 bg-white dark:bg-slate-800 px-2 py-1 rounded-md border border-slate-200 dark:border-slate-700 shadow-2xs">ESC</kbd>
            </div>

            <!-- Category Filter Pills -->
            <div class="flex items-center gap-1.5 px-4 py-2 border-b border-slate-200/80 dark:border-slate-800 overflow-x-auto scrollbar-none bg-white dark:bg-slate-900 text-xs">
                <template x-for="cat in categories" :key="cat.id">
                    <button type="button"
                            @click="activeCategory = cat.id; selectedIndex = 0"
                            :class="activeCategory === cat.id 
                                ? 'bg-purple-600 text-white shadow-xs font-black' 
                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-slate-100 border border-slate-200/80 dark:border-slate-700 font-bold'"
                            class="px-2.5 py-1 rounded-xl text-[11px] whitespace-nowrap transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-1.5">
                        <span x-html="cat.icon"></span>
                        <span x-text="cat.name"></span>
                    </button>
                </template>
            </div>

            <!-- Search Results List -->
            <div class="max-h-[60vh] sm:max-h-96 overflow-y-auto p-2 divide-y divide-slate-100 dark:divide-slate-800/80">
                <template x-if="filteredItems.length === 0">
                    <div class="py-12 text-center space-y-2">
                        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-purple-400 text-lg">
                            <i class="fa-solid fa-compass"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Geen functies gevonden</p>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto">
                            Geen resultaten voor '<span x-text="query" class="font-bold text-purple-600 dark:text-purple-400"></span>'. Probeer een ander trefwoord zoals 'hosselen', 'bank', of 'bende'.
                        </p>
                    </div>
                </template>

                <template x-for="(item, idx) in filteredItems" :key="item.name">
                    <div @click="goToItem(item)"
                         @mouseenter="selectedIndex = idx"
                         :class="selectedIndex === idx 
                             ? 'bg-purple-50/90 dark:bg-purple-950/60 text-purple-900 dark:text-purple-200 border-purple-200/80 dark:border-purple-800/70' 
                             : 'hover:bg-slate-50/80 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300 border-transparent'"
                         class="flex items-center justify-between p-2.5 rounded-xl border transition-all duration-150 cursor-pointer group">
                        
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Icon Badge -->
                            <div :class="item.bg || 'bg-slate-100 dark:bg-slate-800'"
                                 class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm shadow-2xs group-hover:scale-105 transition-transform">
                                <i :class="item.icon + ' ' + (item.color || 'text-slate-600')"></i>
                            </div>

                            <!-- Name & Category -->
                            <div class="min-w-0">
                                <div class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white truncate" x-text="item.name"></div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1.5 mt-0.5">
                                    <span class="font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400" x-text="item.category"></span>
                                    <span>&bull;</span>
                                    <span class="truncate" x-text="item.desc"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Pill / Action Arrow -->
                        <div class="flex items-center gap-2 shrink-0 ml-2">
                            <template x-if="item.badge">
                                <span :class="item.badgeClass || 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70'"
                                      class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md"
                                      x-text="item.badge"></span>
                            </template>
                            <i class="fa-solid fa-arrow-right text-xs opacity-60 sm:opacity-0 sm:group-hover:opacity-100 text-purple-600 dark:text-purple-400 transition-opacity"></i>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer Shortcuts Hint -->
            <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/60 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1"><kbd class="font-mono bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 text-[10px]">↑↓</kbd> Navigeren</span>
                    <span class="flex items-center gap-1"><kbd class="font-mono bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 text-[10px]">↵</kbd> Openen</span>
                    <span class="flex items-center gap-1"><kbd class="font-mono bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 text-[10px]">ESC</kbd> Sluiten</span>
                </div>
                <div class="hidden sm:block text-[10px] font-bold text-purple-600 dark:text-purple-400">
                    TBS Kliniek Snelle Navigatie
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tbsSpotlightModal', () => ({
        isOpen: false,
        query: '',
        selectedIndex: 0,
        activeCategory: 'alle',
        categories: [
            { id: 'alle', name: 'Alles', icon: '<i class="fa-solid fa-layer-group"></i>' },
            { id: 'misdaad', name: 'Misdaad', icon: '<i class="fa-solid fa-handcuffs text-rose-500"></i>' },
            { id: 'economie', name: 'Economie', icon: '<i class="fa-solid fa-coins text-emerald-500"></i>' },
            { id: 'bende', name: 'Bendes', icon: '<i class="fa-solid fa-users text-purple-500"></i>' },
            { id: 'geweld', name: 'Geweld', icon: '<i class="fa-solid fa-fire text-amber-500"></i>' },
            { id: 'voortgang', name: 'Voortgang', icon: '<i class="fa-solid fa-chart-line text-indigo-500"></i>' },
            { id: 'minigames', name: 'Minigames', icon: '<i class="fa-solid fa-gamepad text-fuchsia-500"></i>' },
            { id: 'bonus', name: 'Bonus & Kluis', icon: '<i class="fa-solid fa-gift text-purple-500"></i>' },
        ],
        items: [
            // Dashboard & Algemeen
            { name: 'Dashboard', icon: 'fa-solid fa-gauge', color: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-950/60', route: '{{ route("dashboard") }}', category: 'algemeen', desc: 'Jouw overzicht, patiënt status en snelle acties', keywords: 'overzicht home start dashboard' },
            { name: 'Live Chat & DMs', icon: 'fa-solid fa-comments', color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-950/60', route: '{{ route("chat.index") }}', category: 'algemeen', desc: 'Kliniek chatbox, bendechat en privéberichten', keywords: 'chat dms berichten praten praten live', badge: 'Live', badgeClass: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70' },
            { name: 'Cinema Watchparties', icon: 'fa-solid fa-film', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("cinema.index") }}', category: 'algemeen', desc: 'Samen video\'s kijken met andere patiënten', keywords: 'bioscoop film video youtube cinema' },
            { name: 'Kliniek Nieuws', icon: 'fa-solid fa-newspaper', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("news.index") }}', category: 'algemeen', desc: 'Officiële aankondigingen van de directie', keywords: 'nieuws updates artikelen mededeling' },
            { name: 'Het Afdelingsblad', icon: 'fa-solid fa-file-lines', color: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-950/60', route: '{{ route("newspaper.index") }}', category: 'algemeen', desc: 'De dagelijkse krant van de kliniek', keywords: 'krant nieuws papier afdeling' },
            { name: 'TBS FM (Radio)', icon: 'fa-solid fa-radio', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("radio.index") }}', category: 'algemeen', desc: 'Non-stop muziek streamen tijdens het spelen', keywords: 'radio muziek luisteren tbs fm zender', badge: 'Radio', badgeClass: 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' },
            { name: 'Tijdlijn (Feed)', icon: 'fa-solid fa-bullhorn', color: 'text-sky-600', bg: 'bg-sky-50 dark:bg-sky-950/60', route: '{{ route("timeline.index") }}', category: 'algemeen', desc: 'Openbare berichten van medepatiënten', keywords: 'tijdlijn feed status posts' },
            { name: 'Vrienden & BFF', icon: 'fa-solid fa-user-group', color: 'text-pink-600', bg: 'bg-pink-50 dark:bg-pink-950/60', route: '{{ route("friends.index") }}', category: 'algemeen', desc: 'Vriendenlijst, verzoeken en speciale BFF', keywords: 'vrienden bff relatie maten' },
            { name: 'Leden & Online', icon: 'fa-solid fa-users', color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-950/60', route: '{{ route("members.index") }}', category: 'algemeen', desc: 'Bekijk wie er nu online is in de kliniek', keywords: 'online leden spelers actief' },
            { name: 'Patiënten Ranglijsten', icon: 'fa-solid fa-trophy', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("leaderboard.index") }}', category: 'algemeen', desc: 'Top patiënten gerangschikt op respect & rijkdom', keywords: 'ranglijst top leaderboard stand' },
            { name: '24 Uur Battle', icon: 'fa-solid fa-bolt', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("battle.index") }}', category: 'algemeen', desc: 'Dagelijkse showdown met beloningen', keywords: 'battle 24 uur dagelijkse strijd' },
            { name: 'Isoleercel (Gevangenis)', icon: 'fa-solid fa-dungeon', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("prison") }}', category: 'algemeen', desc: 'Uitzitten, uitbreken of borg betalen', keywords: 'gevangenis bajes isoleercel cel ontsnappen straf' },
            { name: 'Toekomst & Roadmap', icon: 'fa-solid fa-compass', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("roadmap.index") }}', category: 'algemeen', desc: 'Stem op aankomende features voor TBS Kliniek', keywords: 'stemmen roadmap toekomst functies' },

            // Bendes
            { name: 'Mijn Bende', icon: 'fa-solid fa-shield-halved', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("gangs.index", ["tab" => "mijn_gang"]) }}', category: 'bende', desc: 'Factieoverzicht, kas en collectief uurloon', keywords: 'gang bende factie club kas uurloon' },
            { name: 'Bende Leden & Beheer', icon: 'fa-solid fa-users-gear', color: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-950/60', route: '{{ route("gangs.index", ["tab" => "beheer"]) }}', category: 'bende', desc: 'Ledenlijst, toelaten, promoties en donaties', keywords: 'beheer verzoeken accepteren leden bendeleden' },
            { name: 'Bende Oorlog', icon: 'fa-solid fa-crosshairs', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("gangs.index", ["tab" => "oorlog"]) }}', category: 'bende', desc: 'Aanvallen op rivaliserende bendes in de kliniek', keywords: 'oorlog gang war attack bende strijd', badge: 'PvP', badgeClass: 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' },
            { name: 'Bende Panden (Productie)', icon: 'fa-solid fa-building', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("gangs.panden") }}', category: 'bende', desc: 'Smokkelkiosken, opslag en NPC dealers', keywords: 'panden gebouwen productie kiosken bende' },
            { name: 'Bende Garage', icon: 'fa-solid fa-warehouse', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("gangs.garage") }}', category: 'bende', desc: 'Gezamenlijk bende wagenpark en slots', keywords: 'garage wagens autos bende voertuigen' },
            { name: 'Bende Heists (Co-Op)', icon: 'fa-solid fa-masks-theater', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("heists.index", ["tab" => "gang"]) }}', category: 'bende', desc: 'Samen met je bende grote overvallen plegen', keywords: 'heists overvallen roof co-op bende' },
            { name: 'Bende Ranglijst', icon: 'fa-solid fa-trophy', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("gangs.index", ["tab" => "ranglijst"]) }}', category: 'bende', desc: 'Top bendes van TBS Kliniek gerangschikt op respect', keywords: 'top bendes ranking ranglijst gangs' },

            // Voortgang
            { name: 'Depot (Inventaris)', icon: 'fa-solid fa-boxes-stacked', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("depot.index") }}', category: 'voortgang', desc: 'Jouw rugzak met wapens, items en medicijnen', keywords: 'items inventaris depot rugzak spullen' },
            { name: 'Missies & Opdrachten', icon: 'fa-solid fa-list-check', color: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-950/60', route: '{{ route("missions.index") }}', category: 'voortgang', desc: 'Voltooi hoofdstukken voor XP en grote beloningen', keywords: 'missies quests taken opdrachten' },
            { name: 'Krachttraining (Gym)', icon: 'fa-solid fa-dumbbell', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("training.index") }}', category: 'voortgang', desc: 'Train Kracht, Verdediging en Snelheid in de zaal', keywords: 'sportzaal gym trainen kracht defense speed' },
            { name: 'Behandelaarsgesprek', icon: 'fa-solid fa-brain', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("treatment.index") }}', category: 'voortgang', desc: 'Praat met de psychiater en verdien verlof', keywords: 'dokter psycholoog verlof therapie gesprek' },
            { name: 'Ziekenboeg & Pillen', icon: 'fa-solid fa-hospital-user', color: 'text-teal-600', bg: 'bg-teal-50 dark:bg-teal-950/60', route: '{{ route("infirmary.index") }}', category: 'voortgang', desc: 'Herstel je HP en neem medicijnen in', keywords: 'hp genezen healen dokter pillen ziekenhuis', badge: 'HP Herstel', badgeClass: 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800/70' },
            { name: 'Dagbesteding & Werk', icon: 'fa-solid fa-person-digging', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("daycare.index") }}', category: 'voortgang', desc: 'Werken in de kliniek voor eerlijk loon en XP', keywords: 'dagbesteding werk werken uurloon klusjes' },

            // Illegale Acties (Misdaad)
            { name: 'Hosselen', icon: 'fa-solid fa-hand-holding-dollar', color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-950/60', route: '{{ route("hustle.index") }}', category: 'misdaad', desc: 'Snel geld hosselen op de gangen van de kliniek', keywords: 'hossel hosselen geld verdienen zwart straat' },
            { name: 'Dealen & Smokkel', icon: 'fa-solid fa-capsules', color: 'text-teal-600', bg: 'bg-teal-50 dark:bg-teal-950/60', route: '{{ route("smuggle.index") }}', category: 'misdaad', desc: 'Smokkelwaar inkopen en verkopen met winst', keywords: 'smokkelen dealen handel wiet coke pillen' },
            { name: 'Afpersen', icon: 'fa-solid fa-person-military-pointing', color: 'text-orange-600', bg: 'bg-orange-50 dark:bg-orange-950/60', route: '{{ route("extort.index") }}', category: 'misdaad', desc: 'Pers personeel en bewakers af voor contanten', keywords: 'afpersen dreigen geld eisen bewaarders' },
            { name: 'Kluisjes Kraken', icon: 'fa-solid fa-id-card', color: 'text-blue-600', bg: 'bg-blue-50 dark:bg-blue-950/60', route: '{{ route("lockers.index") }}', category: 'misdaad', desc: 'Breek lockerkasten open en steel buit', keywords: 'kluisjes lockerkast openbreken pasjes buit' },
            { name: 'Tennisbal & Drones', icon: 'fa-solid fa-plane-arrival', color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-950/60', route: '{{ route("droppings.index") }}', category: 'misdaad', desc: 'Laat pakketjes over de kliniekmuur droppen', keywords: 'smokkeldropping drone tennisbal over muur' },
            { name: 'Uitbraaktunnel Graven', icon: 'fa-solid fa-person-digging', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("tunnel.index") }}', category: 'misdaad', desc: 'Graaf met een schep een geheime uitbraaktunnel', keywords: 'tunnel schep ontsnappen graven vrijheid' },
            { name: 'Grote Heists', icon: 'fa-solid fa-masks-theater', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("heists.index") }}', category: 'misdaad', desc: 'Plan geavanceerde overvallen met hoge opbrengsten', keywords: 'heists overval bankroof roof roofoverval', badge: 'Groot', badgeClass: 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' },

            // Bonus & Kluizen
            @if(\App\Http\Controllers\SponsorAdController::areAdsEnabled())
            { name: 'Sponsorkamer (Gratis Beloningen)', icon: 'fa-solid fa-tv', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("sponsor.index") }}', category: 'bonus', desc: 'Kijk korte sponsor-ads voor sterren, €50.000 cash, kogels, vesten en loten', keywords: 'sponsor sponsorkamer advertentie ads gratis sterren kogels vest loterij', badge: 'Gratis', badgeClass: 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70' },
            @endif
            { name: 'Rad van Fortuin (Daily)', icon: 'fa-solid fa-dharmachakra', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("daily.index") }}', category: 'bonus', desc: 'Draai dagelijks gratis aan het rad voor prijzen', keywords: 'rad spin draaien gratis beloning daily', badge: 'Daily', badgeClass: 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70' },
            { name: 'MedicijnKluis (Kraak de Code)', icon: 'fa-solid fa-vault', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("vault.index") }}', category: 'bonus', desc: 'Kraak de kluiscode en win de jackpot', keywords: 'kluis code raden jackpot medicijnen' },
            { name: 'Koffers & Pakketjes', icon: 'fa-solid fa-box-open', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("packages.index") }}', category: 'bonus', desc: 'Open mysterieuze koffers voor zeldzame loot', keywords: 'mystery box pakketjes koffers unboxen' },
            { name: 'Zakkenrollen', icon: 'fa-solid fa-mask', color: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-950/60', route: '{{ route("pickpocket.index") }}', category: 'bonus', desc: 'Rol zakken van artsen en verplegers', keywords: 'zakkenrollen stelen personeel zakken' },
            { name: 'Kliniek Terminal (Hack)', icon: 'fa-solid fa-terminal', color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-950/60', route: '{{ route("hacking.index") }}', category: 'bonus', desc: 'Kraak kliniek systemen voor geld en respect', keywords: 'hacken terminal computer codes hack' },

            { name: '4 op \'n Rij (OOHPA Arjan)', icon: 'fa-solid fa-chess-board', color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-950/60', route: '{{ route("connect_four.index") }}', category: 'minigames', desc: 'Klassiek bordspel tegen andere gevangenen', keywords: 'vier op een rij arjan bordspel multiplayer' },
            { name: 'True Crime (Caroline)', icon: 'fa-solid fa-fingerprint', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("true_crime.index") }}', category: 'minigames', desc: 'Los meer dan 500 waargebeurde moordzaken op', keywords: 'caroline true crime quiz moordzaken detective' },
            { name: 'Memory (Psychose Test)', icon: 'fa-solid fa-brain', color: 'text-pink-600', bg: 'bg-pink-50 dark:bg-pink-950/60', route: '{{ route("memory.index") }}', category: 'minigames', desc: 'Extreem geheugenspel: onthoud en match 8 paren binnen 60s', keywords: 'memory geheugen kaarten paren onthouden minigame psychose' },

            // Geweld & Strijd
            { name: 'Respectlijst (PVP Aanvallen)', icon: 'fa-solid fa-skull', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("fight.index") }}', category: 'geweld', desc: 'Val medepatiënten aan voor respect en geld', keywords: 'vechten pvp aanvallen slaan respectlijst' },
            { name: 'Klusjes & Diefstal', icon: 'fa-solid fa-briefcase', color: 'text-slate-600', bg: 'bg-slate-100 dark:bg-slate-800', route: '{{ route("theft.index") }}', category: 'geweld', desc: 'Kleine misdaden en overvallen voor contanten', keywords: 'diefstal stelen stelen klussen misdaad' },
            { name: 'Vechtoefening (Bokszak)', icon: 'fa-solid fa-bullseye', color: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-950/60', route: '{{ route("combat_training.index") }}', category: 'geweld', desc: 'Train je vechtstijl zonder risico op ziekenhuis', keywords: 'vechtoefening trainen vechten bokszak' },
            { name: 'Armpjedruk Arena', icon: 'fa-solid fa-hand-back-fist', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("armwrestling.index") }}', category: 'geweld', desc: 'Daag tegenstanders uit aan de armpjedruk-tafel', keywords: 'armdrukken kracht inzetten duel arm' },
            { name: 'Liquidatie (Hitman)', icon: 'fa-solid fa-crosshairs', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("hitman.index") }}', category: 'geweld', desc: 'Zet huurmoordenaars in op rivalen', keywords: 'huurmoordenaar vermoorden contract hitman' },
            { name: 'Kopgeld & Premies', icon: 'fa-solid fa-skull-crossbones', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("bounties.index") }}', category: 'geweld', desc: 'Zet kopgeld op spelers of claim actieve premies', keywords: 'premie bounty beloning op hoofd' },
            { name: 'Informanten', icon: 'fa-solid fa-user-secret', color: 'text-slate-600', bg: 'bg-slate-100 dark:bg-slate-800', route: '{{ route("informants.index") }}', category: 'geweld', desc: 'Koop geheime info over andere patiënten', keywords: 'klikker spion verrader info kopen' },
            { name: 'Vrijwillige Isolatie', icon: 'fa-solid fa-ghost', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("isolation.index") }}', category: 'geweld', desc: 'Tijdelijke bescherming tegen alle aanvallen', keywords: 'isolatie safezone schuilen veilig' },

            // Economie & Gokken
            { name: 'De Bank', icon: 'fa-solid fa-building-columns', color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-950/60', route: '{{ route("bank.index") }}', category: 'economie', desc: 'Stort zwart geld veilig en ontvang rente', keywords: 'bank storten opnemen rente spaarrekening geld' },
            { name: 'Verplaatsen (Locaties)', icon: 'fa-solid fa-signs-post', color: 'text-teal-600', bg: 'bg-teal-50 dark:bg-teal-950/60', route: '{{ route("travel.index") }}', category: 'economie', desc: 'Reis naar andere afdelingen en buitenplaatsen', keywords: 'reizen locaties vleugels verhuizen' },
            { name: 'Casino & Gokhal', icon: 'fa-solid fa-dice', color: 'text-rose-600', bg: 'bg-rose-50 dark:bg-rose-950/60', route: '{{ route("casino.index") }}', category: 'economie', desc: 'Roulette, Blackjack, Slots en Dobbelspellen', keywords: 'casino roulette slots blackjack gokken' },
            { name: 'Afdelingsloterij', icon: 'fa-solid fa-crown', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("lottery.index") }}', category: 'economie', desc: 'Koop loten en win de wekelijkse megapot', keywords: 'loterij loten trekking jackpot pot' },
            { name: 'Kliniek Winkel', icon: 'fa-solid fa-cart-shopping', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("shop.index") }}', category: 'economie', desc: 'Koop wapens, pansers, telefoons en eten', keywords: 'winkel shop kopen wapens uitrusting' },
            { name: 'Smokkel Garage', icon: 'fa-solid fa-warehouse', color: 'text-cyan-600', bg: 'bg-cyan-50 dark:bg-cyan-950/60', route: '{{ route("garage.index") }}', category: 'economie', desc: 'Koop, repareer en beheer je voertuigen', keywords: 'garage autos voertuigen wagens wagenpark' },
            { name: 'Karakter Perks', icon: 'fa-solid fa-award', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("perks.index") }}', category: 'economie', desc: 'Ontgrendel permanente passieve boosts met sterren', keywords: 'perks voordelen bonussen sterren upgrades' },
            { name: 'Marktplaats', icon: 'fa-solid fa-shop', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("marketplace.index") }}', category: 'economie', desc: 'Handel direct in items en zeldzame buit met spelers', keywords: 'marktplaats markt handel verkopen kopen veiling' },

            // Account & Support
            { name: 'VIP Donaties & Punten', icon: 'fa-solid fa-crown', color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("vip.index") }}', category: 'algemeen', desc: 'Support de server met een vrijwillige donatie en ontvang VIP voordelen', keywords: 'vip doneren donateur sterren perks donatie' },
            { name: 'VIP & Donaties Shop', icon: 'fa-solid fa-crown', color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-950/60', route: '{{ route("shop.index") }}', category: 'algemeen', desc: 'Steun de servers met een donatie en ontvang VIP privileges, sterren en koffers', keywords: 'shop vip donaties buymeacoffee pakketten sterren doneren' },
            { name: 'Helptool & FAQ', icon: 'fa-solid fa-headset', color: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-950/60', route: '{{ route("faq.index") }}', category: 'algemeen', desc: 'Veelgestelde vragen en speluitleg', keywords: 'help faq vragen gids handleiding' },
            { name: 'Support Tickets', icon: 'fa-solid fa-ticket', color: 'text-purple-600', bg: 'bg-purple-50 dark:bg-purple-950/60', route: '{{ route("help.tickets.index") }}', category: 'algemeen', desc: 'Neem contact op met het admin team', keywords: 'tickets support contact hulp melden' },
            { name: 'Mijn Profiel', icon: 'fa-solid fa-id-badge', color: 'text-blue-600', bg: 'bg-blue-50 dark:bg-blue-950/60', route: '{{ route("profile.me") }}', category: 'algemeen', desc: 'Bekijk en personaliseer je eigen patiëntenprofiel', keywords: 'profiel avatar bio titel gastenboek' },
            { name: 'Account Instellingen', icon: 'fa-solid fa-gear', color: 'text-slate-600', bg: 'bg-slate-100 dark:bg-slate-800', route: '{{ route("settings.account") }}', category: 'algemeen', desc: 'Wachtwoord, beveiliging en actieve sessies', keywords: 'instellingen account 2fa wachtwoord' },
        ],
        get filteredItems() {
            let result = this.items;
            if (this.activeCategory !== 'alle') {
                result = result.filter(i => i.category === this.activeCategory);
            }
            if (this.query.trim().length > 0) {
                const q = this.query.toLowerCase().trim();
                result = result.filter(i => {
                    return i.name.toLowerCase().includes(q) 
                        || i.desc.toLowerCase().includes(q) 
                        || (i.keywords && i.keywords.includes(q))
                        || i.category.includes(q);
                });
            }
            return result.slice(0, 15);
        },
        toggleModal() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    this.$refs.searchInput.focus();
                    this.selectedIndex = 0;
                });
            }
        },
        openModal() {
            this.isOpen = true;
            this.$nextTick(() => {
                this.$refs.searchInput.focus();
                this.selectedIndex = 0;
            });
        },
        closeModal() {
            this.isOpen = false;
            this.query = '';
        },
        navigateDown() {
            if (this.selectedIndex < this.filteredItems.length - 1) {
                this.selectedIndex++;
            }
        },
        navigateUp() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
            }
        },
        selectHighlighted() {
            if (this.filteredItems.length > 0 && this.filteredItems[this.selectedIndex]) {
                this.goToItem(this.filteredItems[this.selectedIndex]);
            }
        },
        goToItem(item) {
            this.closeModal();
            if (window.Turbo && typeof window.Turbo.visit === 'function') {
                window.Turbo.visit(item.route);
            } else {
                window.location.href = item.route;
            }
        },
        init() {
            window.tbsOpenSpotlight = () => this.openModal();
        }
    }));
});
</script>
