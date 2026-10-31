<!-- MOBILE FULL SLIDE-OVER NAVIGATION DRAWER (Modern Desktop-Matching Categorized Experience) -->
<div x-show="mobileMenuOpen" x-cloak class="lg:hidden fixed inset-0 z-50 flex justify-end">
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileMenuOpen = false" 
         class="fixed inset-0 bg-slate-950/75"></div>

    <div x-show="mobileMenuOpen"
         role="dialog"
         aria-modal="true"
         x-transition:enter="transition ease-out duration-200 transform-gpu"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150 transform-gpu"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         x-data="window.tbsSidebarController('{{ $activeCategory ?? '' }}')"
         class="relative w-[86vw] max-w-sm bg-white dark:bg-slate-900 border-l border-slate-200/90 dark:border-slate-800 h-full shadow-2xl flex flex-col z-10 overflow-hidden text-slate-800 dark:text-slate-100">
        
        <!-- Drawer Header with Player Summary -->
        <div class="p-4 bg-white dark:bg-slate-900 text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-200/90 dark:border-slate-800 shrink-0">
            @php
                $drawerBorder = $character ? $character->getEquippedBorderInfo() : null;
            @endphp
            <a href="{{ route('profile.me') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 min-w-0 group">
                <div class="relative shrink-0 flex items-center justify-center">
                    <div class="w-11 h-11 rounded-full p-[2px] flex items-center justify-center overflow-hidden {{ $drawerBorder ? str_replace('ring-4', 'ring-2', $drawerBorder['ring']) : 'ring-2 ring-indigo-500/30' }}">
                        <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-full h-full rounded-full object-cover">
                    </div>
                    @if($drawerBorder)
                        <span class="absolute -bottom-1 -right-1 text-xs select-none drop-shadow leading-none">
                            {{ $drawerBorder['emoji'] }}
                        </span>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="font-black text-sm leading-tight truncate px-1 -mx-1 {{ $character->getNameCssClasses() }}" style="{{ $character->getNameInlineStyle() }}">{{ $character->name }}</div>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[10px] bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-extrabold px-1.5 py-0.5 rounded-lg border border-purple-200/80 dark:border-purple-800/70 uppercase">LVL {{ $character->level }}</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $character->title ?? 'Patiënt' }}</span>
                    </div>
                </div>
            </a>
            <button @click="mobileMenuOpen = false" class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition cursor-pointer shrink-0" aria-label="Sluit menu">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Scrollable Navigation Area -->
        <div class="flex-1 overflow-y-auto p-3 space-y-2.5 text-xs font-semibold scrollbar-none">
            
            <!-- ⚡ WEBAPP NAVIGATIE ACTIEBALK (Terug & Vernieuwen) -->
            <div class="grid grid-cols-2 gap-2">
                <button type="button" 
                        onclick="window.tbsGoBack()" 
                        class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold text-xs transition-all duration-150 cursor-pointer border border-slate-200/80 dark:border-slate-700 active:scale-95 shadow-2xs">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Pagina Terug</span>
                </button>
                <button type="button" 
                        onclick="window.tbsReloadPage(this)" 
                        class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold text-xs transition-all duration-150 cursor-pointer border border-slate-200/80 dark:border-slate-700 active:scale-95 shadow-2xs">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span>Vernieuwen</span>
                </button>
            </div>

            <!-- 🔍 DIRECT SEARCH / COMMAND PALETTE TRIGGER -->
            <div>
                <button type="button" 
                        @click="mobileMenuOpen = false; if(window.tbsOpenSpotlight){window.tbsOpenSpotlight();}"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-slate-50/90 hover:bg-purple-50/60 dark:bg-slate-800/60 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/70 transition-all duration-150 cursor-pointer shadow-2xs group text-left">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-magnifying-glass text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Zoek functie of pagina...</span>
                    </span>
                    <span class="text-[9px] font-black uppercase text-purple-700 dark:text-purple-300 bg-purple-100/80 dark:bg-purple-900/60 px-1.5 py-0.5 rounded-md border border-purple-200/60 dark:border-purple-800/50">Zoek</span>
                </button>
            </div>

            <!-- ⭐ SNELLE TOEGANG (Favorieten Grid + Alles Openen) -->
            <div class="p-2.5 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70" data-favorites-grid>
                <div class="flex items-center justify-between px-1 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <span class="flex items-center gap-1.5" title="Klik op het sterretje (☆) achter een menu-item om toe te voegen!">
                        <i class="fa-solid fa-star text-amber-400 text-xs"></i> Snelle Toegang
                    </span>
                    <button type="button" @click="expandAll()" class="text-[9px] font-extrabold text-purple-600 dark:text-purple-400 hover:underline cursor-pointer">
                        <span x-text="openCategories.length >= allCats.length ? 'Sluit menu\'s' : 'Alles openen'">Alles openen</span>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <template x-for="fav in favorites" :key="fav.url">
                        <a :href="fav.url" @click="mobileMenuOpen = false" data-no-fav
                           class="flex flex-col items-center justify-center p-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-purple-50/60 dark:hover:bg-purple-950/40 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-150 text-center group relative shadow-2xs">
                            <i :class="fav.icon" class="group-hover:scale-110 transition-transform text-sm mb-1"></i>
                            <span class="text-[10px] font-bold truncate w-full" x-text="fav.label"></span>
                        </a>
                    </template>
                </div>
            </div>

            <!-- Quick Top Row (Dashboard, Live Chat, Cinema) -->
            <div class="grid grid-cols-3 gap-1.5 pb-1">
                <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="flex flex-col items-center justify-center p-2 rounded-xl text-center transition-all duration-150 border {{ request()->routeIs('dashboard') ? 'bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70 font-black' : 'border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-slate-800/60 font-bold' }}">
                    <i class="fa-solid fa-gauge text-purple-600 dark:text-purple-400 mb-0.5 text-sm"></i>
                    <span class="text-[10px] truncate w-full">Overzicht</span>
                </a>
                <a href="{{ route('chat.index') }}" @click="mobileMenuOpen = false" class="relative flex flex-col items-center justify-center p-2 rounded-xl text-center transition-all duration-150 border {{ request()->routeIs('chat.*') ? 'bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/70 font-black' : 'border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-slate-800/60 font-bold' }}">
                    <i class="fa-solid fa-comments text-emerald-500 dark:text-emerald-400 mb-0.5 text-sm"></i>
                    <span class="text-[10px] truncate w-full">Live Chat</span>
                    @if($unreadChatCount > 0)
                        <span class="absolute top-1 right-1 bg-emerald-500 text-white text-[9px] font-extrabold px-1 rounded-full animate-pulse">{{ $unreadChatCount }}</span>
                    @endif
                </a>
                <a href="{{ route('cinema.index') }}" @click="mobileMenuOpen = false" class="flex flex-col items-center justify-center p-2 rounded-xl text-center transition-all duration-150 border {{ request()->routeIs('cinema.*') ? 'bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70 font-black' : 'border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-slate-800/60 font-bold' }}">
                    <i class="fa-solid fa-film text-purple-500 dark:text-purple-400 mb-0.5 text-sm"></i>
                    <span class="text-[10px] truncate w-full">Cinema 🍿</span>
                </a>
            </div>

            <!-- VIP Shop Feature Banner -->
            <a href="{{ route('vip.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 border border-amber-200/80 dark:border-amber-800/70 font-bold transition-all duration-150 hover:bg-amber-100/60 active:scale-95">
                <span class="flex items-center gap-2"><i class="fa-solid fa-crown text-amber-500"></i> VIP Shop & Punten</span>
                <span class="text-[9px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 bg-amber-200/80 dark:bg-amber-900/60 px-2 py-0.5 rounded-md border border-amber-300/80 dark:border-amber-700/60">Exclusief</span>
            </a>

            <!-- 🎃 EVENT: HALLOWEEN 2026 (Collapsible Accordion) -->
            <div class="rounded-xl border border-orange-500/20 bg-orange-500/5 overflow-hidden">
                <button @click="toggleCategory('event')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2.5 text-[11px] font-black uppercase tracking-wider text-orange-600 dark:text-orange-400 transition cursor-pointer">
                    <span class="flex items-center gap-2">
                        <span>🎃</span>
                        <span>Halloween Event</span>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[9px] font-bold text-amber-500 flex items-center gap-0.5">
                            <i class="fa-solid fa-seedling text-[9px]"></i> {{ number_format($character->pumpkin_seeds ?? 0, 0, ',', '.') }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('event') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'event') style="transform: rotate(180deg);" @endif></i>
                    </div>
                </button>
                <div x-show="isOpen('event')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'event') style="display: none;" @endif class="space-y-0.5 px-2 pb-2">
                    <a href="{{ route('halloween.quests') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('halloween.quests*') ? 'bg-orange-500/20 text-orange-700 dark:text-orange-300 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-orange-500/10' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-scroll text-orange-500 w-4"></i> Quests</span>
                        <span class="text-[9px] bg-orange-500 text-white font-black px-1.5 py-0.5 rounded uppercase">Quests</span>
                    </a>
                    <a href="{{ route('halloween.shop') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('halloween.shop*') ? 'bg-orange-500/20 text-orange-700 dark:text-orange-300 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-orange-500/10' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-store text-orange-500 w-4"></i> Halloween Shop</span>
                        <span class="text-[9px] bg-amber-500 text-white font-black px-1.5 py-0.5 rounded uppercase">Shop</span>
                    </a>
                </div>
            </div>

            <!-- 1. ALGEMEEN & INFO -->
            <div class="space-y-0.5">
                <button @click="toggleCategory('algemeen')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-sky-600 dark:hover:text-sky-300 hover:bg-sky-50/50 dark:hover:bg-sky-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-shield-halved"></i></span>
                        <span>Algemeen & Info</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('algemeen') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'algemeen') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="isOpen('algemeen')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'algemeen') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('news.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('news.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-newspaper w-4 text-center text-purple-600 dark:text-purple-400"></i>
                        <span>Kliniek Nieuws</span>
                    </a>
                    <a href="{{ route('newspaper.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('newspaper.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-file-lines w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Het Afdelingsblad</span>
                    </a>
                    <a href="{{ route('radio.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('radio.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-radio w-4 text-center text-purple-500 dark:text-purple-400"></i>
                            <span>TBS FM (Radio)</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.5 rounded border border-rose-200/60 dark:border-rose-900">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Live
                        </span>
                    </a>
                    <a href="{{ route('timeline.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('timeline.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-bullhorn w-4 text-center text-sky-500 dark:text-sky-400"></i>
                        <span>Tijdlijn (Feed)</span>
                    </a>
                    <a href="{{ route('friends.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('friends.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-user-group w-4 text-center text-pink-500 dark:text-pink-400"></i>
                        <span>Vrienden & BFF</span>
                    </a>
                    <a href="{{ route('members.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('members.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>Leden & Online</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span x-text="stats.onlineCount || {{ $onlineCount }}">{{ $onlineCount }}</span>
                        </span>
                    </a>
                    <a href="{{ route('leaderboard.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('leaderboard.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-trophy w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Ranglijsten</span>
                    </a>
                    <a href="{{ route('battle.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('battle.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-bolt w-4 text-center text-purple-600 dark:text-purple-400"></i>
                        <span>24 Uur Battle</span>
                    </a>
                    <a href="{{ route('prison') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('prison') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-dungeon w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Isoleercel</span>
                    </a>
                    <a href="{{ route('roadmap.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('roadmap.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-compass w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Toekomst & Roadmap</span>
                    </a>
                </div>
            </div>

            <!-- 2. BENDE & GANGS -->
            <div class="space-y-0.5">
                <button @click="toggleCategory('bende')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-300 hover:bg-purple-50/50 dark:hover:bg-purple-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-users"></i></span>
                        <span>Bendes</span>
                    </span>
                    <div class="flex items-center gap-1.5">
                        @if($character && $character->gang)
                            <span class="text-[9px] font-black uppercase text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/80 px-1.5 py-0.2 rounded border border-purple-200 dark:border-purple-800">
                                [{{ $character->gang->tag }}]
                            </span>
                        @endif
                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('bende') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'bende') style="transform: rotate(180deg);" @endif></i>
                    </div>
                </button>
                <div x-show="isOpen('bende')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'bende') style="display: none;" @endif class="space-y-0.5 pl-1">
                    @if($character && $character->gang)
                        <a href="{{ route('gangs.index', ['tab' => 'mijn_gang']) }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && (!request('tab') || request('tab') === 'mijn_gang') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-shield-halved w-4 text-center text-purple-600 dark:text-purple-400"></i>
                                <span>Mijn Bende</span>
                            </div>
                            <span class="text-[10px] font-extrabold text-purple-600 dark:text-purple-400">{{ $character->gang->name }}</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'alle_bendes']) }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'alle_bendes' ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-address-book w-4 text-center text-purple-500 dark:text-purple-400"></i>
                                <span>Alle Bendes</span>
                            </div>
                            <span class="text-[9px] font-extrabold uppercase text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">Lijst</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'leden']) }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'leden' ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-user-group w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                            <span>Bende Leden</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'oorlog']) }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'oorlog' ? 'nav-item-active bg-rose-50/90 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-crosshairs w-4 text-center text-rose-500 dark:text-rose-400"></i>
                                <span>Bende Oorlog</span>
                            </div>
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                        </a>
                        <a href="{{ route('gangs.panden') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.panden*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-building w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>Bende Panden</span>
                        </a>
                        <a href="{{ route('gangs.garage') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.garage*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-warehouse w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>Bende Garage</span>
                        </a>
                        <a href="{{ route('heists.index', ['tab' => 'gang']) }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('heists.*') && request('tab') === 'gang' ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-masks-theater w-4 text-center text-rose-500 dark:text-rose-400"></i>
                                <span>Bende Heists</span>
                            </div>
                            <span class="text-[9px] font-black uppercase text-amber-500 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20">Co-Op</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'ranglijst']) }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'ranglijst' ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-trophy w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Bende Ranglijst</span>
                        </a>
                        @if($character->gang->isOfficer($character))
                        <a href="{{ route('gangs.index', ['tab' => 'beheer']) }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'beheer' ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-users-gear w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>Bende Beheer</span>
                        </a>
                        @endif
                    @else
                        <a href="{{ route('gangs.index', ['tab' => 'alle_bendes']) }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && (!request('tab') || request('tab') === 'alle_bendes') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-address-book w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>Alle Bendes</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'ranglijst']) }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'ranglijst' ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-trophy w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Bende Ranglijst</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'oprichten']) }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'oprichten' ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-plus w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>Bende Oprichten</span>
                        </a>
                    @endif

                </div>
            </div>

            <!-- 3. VOORTGANG & GEZONDHEID -->
            <div class="space-y-0.5">
                <button @click="toggleCategory('voortgang')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-chart-line"></i></span>
                        <span>Voortgang & Gezondheid</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('voortgang') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'voortgang') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="isOpen('voortgang')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'voortgang') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('battlepass.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('battlepass.*') ? 'nav-item-active bg-amber-500/15 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 font-black border border-amber-500/40' : 'text-slate-700 dark:text-slate-300 hover:text-amber-600 hover:bg-amber-50/50 dark:hover:bg-amber-950/20' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-trophy w-4 text-center text-amber-500 animate-pulse"></i>
                            <span class="font-bold">Seizoen 1 Battle Pass</span>
                        </div>
                        @php $drawerBpUnclaimed = $character->getUnclaimedBattlePassCount(); @endphp
                        @if($drawerBpUnclaimed > 0)
                            <span class="text-[9px] font-black uppercase text-slate-950 bg-amber-400 px-2 py-0.5 rounded-full shadow-2xs animate-bounce">
                                {{ $drawerBpUnclaimed }} Klaar!
                            </span>
                        @else
                            <span class="text-[9px] font-black uppercase text-amber-900 dark:text-amber-200 bg-amber-400/30 px-2 py-0.5 rounded-full border border-amber-400/50">
                                150 Lvl
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('missions.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('missions.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-list-check w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Missies & Opdrachten</span>
                    </a>
                    <a href="{{ route('training.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('training.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-dumbbell w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>Krachttraining</span>
                    </a>
                    <a href="{{ route('treatment.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('treatment.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-brain w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Behandelaarsgesprek</span>
                    </a>
                    <a href="{{ route('infirmary.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('infirmary.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-hospital-user w-4 text-center text-teal-500 dark:text-teal-400"></i>
                        <span>Ziekenboeg & Pillen</span>
                    </a>
                    <a href="{{ route('daycare.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('daycare.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-person-digging w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Dagbesteding</span>
                    </a>
                </div>
            </div>

            <!-- 4. ILLEGALE ACTIES & MISDAAD -->
            <div class="space-y-0.5">
                <button @click="toggleCategory('misdaad')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-300 hover:bg-rose-50/50 dark:hover:bg-rose-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-handcuffs"></i></span>
                        <span>Illegale Acties</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('misdaad') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'misdaad') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="isOpen('misdaad')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'misdaad') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('hustle.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('hustle.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-hand-holding-dollar w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                        <span>Hosselen</span>
                    </a>
                    <a href="{{ route('smuggle.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('smuggle.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-capsules w-4 text-center text-teal-500 dark:text-teal-400"></i>
                        <span>Dealen & Smokkel</span>
                    </a>
                    <a href="{{ route('extort.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('extort.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-person-military-pointing w-4 text-center text-orange-500 dark:text-orange-400"></i>
                        <span>Afpersen</span>
                    </a>
                    <a href="{{ route('lockers.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('lockers.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-id-card w-4 text-center text-blue-500 dark:text-blue-400"></i>
                        <span>Kluisjes Kraken</span>
                    </a>
                    <a href="{{ route('droppings.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('droppings.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-plane-arrival w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                        <span>Tennisbal & Drones</span>
                    </a>
                    <a href="{{ route('tunnel.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('tunnel.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-person-digging w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Uitbraaktunnel</span>
                    </a>
                    <a href="{{ route('heists.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('heists.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-masks-theater w-4 text-center text-rose-500 dark:text-rose-400"></i>
                            <span>Grote Heists</span>
                        </div>
                        <span class="bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
                            Nieuw
                        </span>
                    </a>
                </div>
            </div>

            <!-- 5. BONUS & KLUIZEN -->
            <div class="space-y-0.5">
                <button @click="toggleCategory('bonus')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-300 hover:bg-amber-50/50 dark:hover:bg-amber-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-gift"></i></span>
                        <span>Bonus & Kluizen</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('bonus') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'bonus') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="isOpen('bonus')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'bonus') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('daily.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('daily.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-dharmachakra w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Rad van Fortuin</span>
                        </div>
                        @if($character && $character->canDailySpin())
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
                            Gratis
                        </span>
                        @endif
                    </a>
                    <a href="{{ route('vault.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('vault.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-vault w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>MedicijnKluis (Kraak)</span>
                    </a>
                    <a href="{{ route('packages.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('packages.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-box-open w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Koffers & Pakketjes</span>
                    </a>
                    <a href="{{ route('pickpocket.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('pickpocket.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-mask w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Zakkenrollen</span>
                    </a>
                    <a href="{{ route('hacking.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('hacking.*') ? 'nav-item-active bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-terminal w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>Kliniek Terminal</span>
                        </div>
                        @if($character && $character->canDailyHack())
                        <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
                            Hack
                        </span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- 6. MINIGAMES & SPECIALS -->
            <div class="space-y-0.5">
                <button @click="toggleCategory('specials')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-fuchsia-600 dark:hover:text-fuchsia-300 hover:bg-fuchsia-50/50 dark:hover:bg-fuchsia-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-fuchsia-100 dark:bg-fuchsia-950/60 text-fuchsia-600 dark:text-fuchsia-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-gamepad"></i></span>
                        <span>Minigames & Specials</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('specials') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'specials') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="isOpen('specials')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'specials') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('connect_four.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('connect_four.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chess-board w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>4 op 'n Rij (Arjan)</span>
                        </div>
                        <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            OOHPA
                        </span>
                    </a>
                    <a href="{{ route('true_crime.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('true_crime.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-fingerprint w-4 text-center text-purple-500 dark:text-purple-400"></i>
                            <span>True Crime (Caroline)</span>
                        </div>
                    </a>
                    <a href="{{ route('memory.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('memory.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-brain w-4 text-center text-pink-500 dark:text-pink-400"></i>
                            <span>Memory</span>
                        </div>
                        <span class="bg-gradient-to-r from-pink-500 to-rose-500 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-full tracking-wide shadow-xs">
                            Nieuw
                        </span>
                    </a>
                </div>
            </div>

            <!-- 7. GEWELD & STRIJD -->
            <div class="space-y-0.5">
                <button @click="toggleCategory('geweld')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-50/50 dark:hover:bg-red-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-fire"></i></span>
                        <span>Geweld & Strijd</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('geweld') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'geweld') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="isOpen('geweld')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'geweld') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('fight.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('fight.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-skull w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Respectlijst</span>
                    </a>
                    <a href="{{ route('theft.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('theft.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-briefcase w-4 text-center text-slate-500 dark:text-slate-400"></i>
                        <span>Klusjes & Diefstal</span>
                    </a>
                    <a href="{{ route('combat_training.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('combat_training.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-bullseye w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Vechtoefening</span>
                    </a>
                    <a href="{{ route('armwrestling.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('armwrestling.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-hand-back-fist w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Armpjedruk Arena</span>
                    </a>
                    <a href="{{ route('hitman.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('hitman.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-crosshairs w-4 text-center text-rose-600 dark:text-rose-400"></i>
                        <span>Liquidatie</span>
                    </a>
                    <a href="{{ route('bounties.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('bounties.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-skull-crossbones w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Kopgeld & Premies</span>
                    </a>
                    <a href="{{ route('informants.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('informants.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-user-secret w-4 text-center text-slate-500 dark:text-slate-400"></i>
                        <span>Informanten</span>
                    </a>
                    <a href="{{ route('isolation.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('isolation.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <span class="flex items-center gap-3 min-w-0">
                            <i class="fa-solid fa-ghost w-4 text-center text-purple-500 dark:text-purple-400"></i>
                            <span class="truncate">Vrijwillige Isolatie</span>
                        </span>
                        @if(isset($character) && $character && $character->isProtected())
                            <span x-data="{
                                    left: {{ (int) $character->getProtectionRemainingSeconds() }},
                                    format(s) {
                                        if (s <= 0) return '0m';
                                        const h = Math.floor(s / 3600);
                                        const m = Math.floor((s % 3600) / 60);
                                        const sec = Math.floor(s % 60);
                                        return h > 0 ? `${h}u ${m}m` : `${m}m ${sec}s`;
                                    },
                                    init() {
                                        setInterval(() => { if (this.left > 0) this.left--; }, 1000);
                                    }
                                }"
                                class="px-1.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 text-[9px] font-black font-mono tabular-nums shrink-0 flex items-center gap-1"
                                title="Beveiliging actief">
                                <i class="fa-solid fa-shield-halved text-[8px]"></i>
                                @php
                                    $drawerProtSecs = (int) $character->getProtectionRemainingSeconds();
                                    $drawerProtH = (int) floor($drawerProtSecs / 3600);
                                    $drawerProtM = (int) floor(($drawerProtSecs % 3600) / 60);
                                @endphp
                                <span x-text="format(left)">{{ $drawerProtH > 0 ? "{$drawerProtH}u {$drawerProtM}m" : "{$drawerProtM}m" }}</span>
                            </span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- 8. ECONOMIE & GOKKEN -->
            <div class="space-y-0.5">
                <button @click="toggleCategory('economie')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-coins"></i></span>
                        <span>Economie & Gokken</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('economie') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'economie') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="isOpen('economie')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'economie') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('bank.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('bank.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-building-columns w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                        <span>De Bank</span>
                    </a>
                    <a href="{{ route('travel.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('travel.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-signs-post w-4 text-center text-teal-500 dark:text-teal-400"></i>
                        <span>Verplaatsen</span>
                    </a>
                    <a href="{{ route('casino.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('casino.*', 'rps.*', 'highlow.*', 'numbergame.*', 'roulette.*', 'slots.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-dice w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Casino & Gokhal</span>
                    </a>
                    <a href="{{ route('lottery.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('lottery.*') ? 'nav-item-active bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-crown w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Afdelingsloterij</span>
                        </div>
                        <span class="bg-amber-400 text-slate-950 font-black text-[9px] px-1.5 py-0.5 rounded uppercase tracking-wider">Pot</span>
                    </a>
                    <a href="{{ route('shop.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('shop.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-cart-shopping w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Kliniek Winkel</span>
                    </a>
                    <a href="{{ route('garage.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('garage.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-warehouse w-4 text-center text-cyan-500 dark:text-cyan-400"></i>
                        <span>Smokkel Garage</span>
                    </a>
                    <a href="{{ route('perks.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('perks.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-award w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Karakter Perks</span>
                        </div>
                        <span class="bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            ⭐ Perks
                        </span>
                    </a>
                    <a href="{{ route('marketplace.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('marketplace.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-shop w-4 text-center text-purple-600 dark:text-purple-400"></i>
                        <span>Marktplaats</span>
                    </a>
                </div>
            </div>

            <!-- 9. HELPTOOL, PROFIEL & ACCOUNT -->
            <div class="space-y-0.5 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                <button @click="toggleCategory('account')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-300 hover:bg-purple-50/50 dark:hover:bg-purple-950/20 rounded-xl transition cursor-pointer group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-user-gear"></i></span>
                        <span>Helptool & Profiel</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="isOpen('account') ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'account') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="isOpen('account')" x-collapse x-cloak @if(($activeCategory ?? '') !== 'account') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('faq.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('faq.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-headset w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Helptool & FAQ</span>
                    </a>
                    <a href="{{ route('help.tickets.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('help.tickets.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-ticket w-4 text-center text-purple-500 dark:text-purple-400"></i>
                            <span>Support Tickets</span>
                        </div>
                        @php
                            $userOpenTicketsMob = Auth::check() ? Auth::user()->openSupportTicketsCount() : 0;
                        @endphp
                        @if($userOpenTicketsMob > 0)
                        <span class="text-[9px] bg-emerald-500 text-white font-black px-1.5 py-0.5 rounded uppercase">Reactie!</span>
                        @endif
                    </a>
                    <a href="{{ route('help.tickets.create') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('help.tickets.create') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-plus w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                        <span>Nieuw Ticket Aanmaken</span>
                    </a>
                    <a href="{{ route('profile.me') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('profile.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-id-badge w-4 text-center text-blue-500 dark:text-blue-400"></i>
                        <span>Mijn Profiel</span>
                    </a>
                    <a href="{{ route('vip.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('vip.*') ? 'nav-item-active bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold' : 'text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 hover:bg-amber-50/60 dark:hover:bg-amber-950/30' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-crown w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span class="font-extrabold">VIP Shop & Punten</span>
                        </div>
                    </a>
                    <a href="{{ route('shop.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('shop.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 font-bold' : 'text-purple-700 dark:text-purple-300 hover:text-purple-900 dark:hover:text-purple-200 hover:bg-purple-50/60 dark:hover:bg-purple-950/30' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-crown w-4 text-center text-amber-500"></i>
                            <span class="font-extrabold">VIP & Donaties</span>
                        </div>
                        <span class="text-[9px] bg-gradient-to-r from-amber-500 to-purple-600 text-white font-black px-1.5 py-0.5 rounded uppercase">Shop</span>
                    </a>
                    <a href="{{ route('settings.account') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-gear w-4 text-center text-slate-500 dark:text-slate-400"></i>
                        <span>Account Instellingen</span>
                    </a>
                    @if(Auth::check() && Auth::user()->isStaff())
                    <a href="{{ route('admin.dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg bg-purple-100/90 dark:bg-purple-950/70 text-purple-900 dark:text-purple-300 font-bold">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-shield-halved w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span class="font-black">Medewerkers Admin</span>
                        </div>
                        <span class="text-[9px] bg-purple-600 text-white font-black px-1.5 py-0.5 rounded uppercase">Staff</span>
                    </a>
                    @endif
                </div>
            </div>

        </div>

        <!-- Drawer Footer / App Install & Logout (No PWA Mention) -->
        <div class="p-3 border-t border-slate-200/90 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-900 shrink-0 space-y-2">
            <button type="button" @click="mobileMenuOpen = false; if(window.tbsOpenPwaModal){window.tbsOpenPwaModal();}" class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 transition-all duration-150 cursor-pointer border border-purple-200/80 dark:border-purple-800/70 active:scale-95 shadow-2xs">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen-button text-purple-600 dark:text-purple-400"></i>
                    <span>Download App</span>
                </div>
                <span class="text-[9px] bg-purple-600 text-white font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">App</span>
            </button>
            <form method="POST" action="{{ route('logout') }}"
                  data-confirm="Weet je zeker dat je wilt uitloggen uit de TBS Kliniek?"
                  data-confirm-title="Veilig Uitloggen"
                  data-confirm-btn="Ja, Uitloggen"
                  data-confirm-type="logout"
                  data-confirm-icon="fa-right-from-bracket">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl font-bold text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-transparent hover:border-rose-200/80 dark:hover:border-rose-900/60 transition-all duration-150 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Veilig Uitloggen</span>
                </button>
            </form>
        </div>

    </div>
</div>
