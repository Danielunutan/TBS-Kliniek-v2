@extends('admin.layout')

@section('admin-content')
<div class="space-y-6" x-data="{ editingId: {{ (int) (session('editing_player_id') ?? 0) ?: 'null' }} }">

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-900 dark:text-white">
        <form method="GET" action="{{ route('admin.players') }}" class="w-full sm:w-auto flex items-center gap-2">
            <div class="relative flex-1 sm:w-72">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Zoek op patiëntnaam of email..." 
                       class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl pl-3.5 pr-8 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-400 dark:text-slate-500 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                Zoeken
            </button>
            @if(request('search') || request('filter'))
                <a href="{{ route('admin.players') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                    Wissen
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
            <a href="{{ route('admin.players') }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ !request('filter') ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700' }}">
                Alle ({{ $players->total() }})
            </a>
            <a href="{{ route('admin.players', ['filter' => 'staff']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('filter') === 'staff' ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700' }}">
                <i class="fa-solid fa-shield-halved text-indigo-400 mr-1"></i> Staff / Mod
            </a>
            <a href="{{ route('admin.players', ['filter' => 'vip']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('filter') === 'vip' ? 'bg-amber-500 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700' }}">
                <i class="fa-solid fa-crown text-amber-500 mr-1"></i> VIPs
            </a>
            <a href="{{ route('admin.players', ['filter' => 'prison']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('filter') === 'prison' ? 'bg-rose-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700' }}">
                <i class="fa-solid fa-lock text-rose-500 mr-1"></i> Isoleercel
            </a>
            <a href="{{ route('admin.players', ['filter' => 'banned']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('filter') === 'banned' ? 'bg-rose-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700' }}">
                <i class="fa-solid fa-ban mr-1 {{ request('filter') === 'banned' ? 'text-white' : 'text-rose-500' }}"></i> Verbannen
            </a>
            <a href="{{ route('admin.players', ['filter' => 'warned']) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('filter') === 'warned' ? 'bg-amber-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700' }}">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1"></i> Gewaarschuwd
            </a>
        </div>
    </div>

    <!-- Players Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden text-slate-900 dark:text-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                        <th class="py-3 px-4">PATIËNT</th>
                        <th class="py-3 px-3">FUNCTIE</th>
                        <th class="py-3 px-3">GANG</th>
                        <th class="py-3 px-3 text-right">CONTANT</th>
                        <th class="py-3 px-3 text-right">BANK</th>
                        <th class="py-3 px-3 text-right">STERREN</th>
                        <th class="py-3 px-3 text-right">VIP PUNTEN</th>
                        <th class="py-3 px-3 text-center">VIP STATUS</th>
                        <th class="py-3 px-3 text-center">CEL STATUS</th>
                        <th class="py-3 px-3 text-center">SANCTIES</th>
                        <th class="py-3 px-4 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                    @forelse($players as $player)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                <div>
                                    <div class="font-extrabold text-slate-900 dark:text-white text-xs">{{ $player->name }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $player->user?->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-3">
                            @if($player->user)
                                @php $pBadge = $player->user->role_badge; @endphp
                                <span class="{{ $pBadge['bg'] }} {{ $pBadge['text'] }} border {{ $pBadge['border'] }} text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md inline-flex items-center gap-1 shadow-2xs">
                                    <i class="fa-solid {{ $pBadge['icon'] }} text-[9px]"></i> {{ $pBadge['label'] }}
                                </span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 text-[10px]">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-slate-600 dark:text-slate-300">
                            {{ $player->gang?->name ?? 'Geen' }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            €{{ number_format($player->cash, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-sky-600 dark:text-sky-400">
                            €{{ number_format($player->bank, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-amber-500 dark:text-amber-400">
                            <i class="fa-solid fa-star text-amber-400 text-xs"></i> {{ number_format($player->stars, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-purple-600 dark:text-purple-400">
                            <i class="fa-solid fa-gem text-purple-500 text-xs"></i> {{ number_format($player->vip_points ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($player->isVip())
                                @php $vipBadge = $player->vip_badge; @endphp
                                @if($vipBadge)
                                    <span class="{{ $vipBadge['class'] }} text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1 border">
                                        <i class="fa-solid {{ $vipBadge['icon'] }}"></i> {{ $vipBadge['short_label'] }}
                                    </span>
                                @else
                                    <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fa-solid fa-crown text-amber-600 dark:text-amber-400"></i> VIP
                                    </span>
                                @endif
                                @if($player->vip_expires_at)
                                    <div class="text-[9px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">{{ $player->vip_expires_at->format('d-m-Y') }}</div>
                                @else
                                    <div class="text-[9px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">Permanent</div>
                                @endif
                            @else
                                <span class="text-slate-400 dark:text-slate-500 text-[10px]">Nee</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($player->is_in_prison)
                                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-rose-600 dark:text-rose-400"></i> Vast
                                </span>
                            @else
                                <span class="text-emerald-600 dark:text-emerald-400 text-[10px] font-bold">Vrij</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($player->user?->is_banned)
                                <span class="bg-rose-600 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-full inline-flex items-center gap-1 shadow-2xs">
                                    <i class="fa-solid fa-ban text-[8px]"></i> Verbannen
                                </span>
                            @elseif($player->user && $player->user->warnings->count() > 0)
                                <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 text-[9px] font-black uppercase px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-[8px]"></i> {{ $player->user->warnings->count() }}x
                                </span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600 text-[10px]">&mdash;</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button @click="editingId = editingId === {{ $player->id }} ? null : {{ $player->id }}"
                                    class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-600 hover:text-white dark:bg-purple-950/60 dark:text-purple-300 dark:hover:bg-purple-600 dark:hover:text-white text-purple-700 border border-purple-200/80 dark:border-purple-800/70 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Wijzig
                            </button>
                        </td>
                    </tr>

                    <!-- Expandable Edit Form Drawer -->
                    <tr x-show="editingId === {{ $player->id }}" x-cloak class="bg-purple-50/40 dark:bg-slate-950/60">
                        <td colspan="11" class="p-6 border-y border-purple-200 dark:border-slate-800">
                            <form action="{{ route('admin.player.update', $player) }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="original_updated_at" value="{{ $player->updated_at?->timestamp ?? 0 }}">
                                <input type="hidden" name="original_cash" value="{{ (int) $player->cash }}">
                                <input type="hidden" name="original_bank" value="{{ (int) $player->bank }}">
                                <input type="hidden" name="original_stars" value="{{ (int) $player->stars }}">
                                <input type="hidden" name="original_respect" value="{{ (float) $player->respect }}">
                                <input type="hidden" name="original_vip_points" value="{{ (int) ($player->vip_points ?? 0) }}">
                                <input type="hidden" name="original_health" value="{{ (int) $player->health }}">
                                <input type="hidden" name="original_energy" value="{{ (int) $player->energy }}">
                                <input type="hidden" name="original_role" value="{{ $player->user?->role ?? 'user' }}">
                                <input type="hidden" name="original_username" value="{{ $player->user?->name ?? '' }}">
                                <input type="hidden" name="original_character_name" value="{{ $player->name }}">
                                <input type="hidden" name="original_is_vip" value="{{ $player->isVip() ? 1 : 0 }}">
                                <div class="flex items-center justify-between border-b border-purple-200 dark:border-slate-800 pb-2">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                                        <i class="fa-solid fa-sliders text-purple-600 dark:text-purple-400"></i> Patiënt Aanpassen: {{ $player->name }}
                                    </h4>
                                </div>

                                <!-- Moderatie: Gebruikersnaam, Weergavenaam & Profielfoto Reset -->
                                <div class="bg-amber-50/70 dark:bg-amber-950/30 p-3.5 rounded-xl border border-amber-200 dark:border-amber-800/70 grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                                    <div>
                                        <label class="text-[10px] font-black text-amber-900 dark:text-amber-300 uppercase tracking-wider block flex items-center gap-1.5">
                                            <i class="fa-solid fa-user-gear text-amber-600 dark:text-amber-400"></i> Account Inlognaam (User)
                                        </label>
                                        <input type="text" name="username" value="{{ $player->user?->name }}" {{ $player->user ? 'required' : '' }} placeholder="{{ $player->user ? 'Inlognaam' : 'Geen account gekoppeld' }}"
                                               class="w-full bg-white dark:bg-slate-800 border border-amber-300 dark:border-amber-700 text-slate-900 dark:text-slate-100 rounded-xl px-2.5 py-1.5 text-xs font-bold focus:outline-none focus:ring-1 focus:ring-amber-500 mt-1">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-amber-900 dark:text-amber-300 uppercase tracking-wider block flex items-center gap-1.5">
                                            <i class="fa-solid fa-id-card text-amber-600 dark:text-amber-400"></i> Inmate Weergavenaam (Character)
                                        </label>
                                        <input type="text" name="character_name" value="{{ $player->name }}" required
                                               class="w-full bg-white dark:bg-slate-800 border border-amber-300 dark:border-amber-700 text-slate-900 dark:text-slate-100 rounded-xl px-2.5 py-1.5 text-xs font-bold focus:outline-none focus:ring-1 focus:ring-amber-500 mt-1">
                                    </div>
                                    <div class="flex items-center sm:pt-4">
                                        <label class="w-full flex items-center gap-2 p-2 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/70 text-rose-800 dark:text-rose-300 text-xs font-bold cursor-pointer hover:bg-rose-100 dark:hover:bg-rose-900/40 transition">
                                            <input type="checkbox" name="reset_avatar" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-image-portrait text-rose-600 dark:text-rose-400"></i>
                                                <span>Reset profielfoto naar default</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                    <!-- Rol / Functie Ranking -->
                                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                                        <label class="text-[10px] font-black text-purple-900 dark:text-purple-300 uppercase tracking-wider block flex items-center gap-1.5">
                                            <i class="fa-solid fa-ranking-star text-purple-600 dark:text-purple-400"></i> Functie & Rank
                                        </label>
                                        <div>
                                            <select name="role" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1.5 text-xs font-bold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-600">
                                                <option value="user" {{ ($player->user?->role ?? 'user') === 'user' ? 'selected' : '' }}>Lid (Speler)</option>
                                                <option value="moderator" {{ ($player->user?->role ?? '') === 'moderator' ? 'selected' : '' }}>Moderator (Mod)</option>
                                                <option value="admin" {{ ($player->user?->role ?? '') === 'admin' ? 'selected' : '' }}>Beheerder (Admin)</option>
                                                @if(Auth::user()?->isOwner())
                                                <option value="owner" {{ ($player->user?->role ?? '') === 'owner' ? 'selected' : '' }}>👑 Eigenaar</option>
                                                @endif
                                            </select>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 leading-snug">
                                                Moderators kunnen ongepaste berichten modereren & verwijderen.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- VIP Tier & Handmatige Toekenning -->
                                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 space-y-2 col-span-1 sm:col-span-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-[10px] font-black text-purple-900 dark:text-purple-300 uppercase tracking-wider block flex items-center gap-1.5">
                                                <i class="fa-solid fa-crown text-amber-500"></i> VIP Tier &amp; Handmatig Toekennen
                                            </label>
                                            <label class="flex items-center gap-1.5 cursor-pointer text-[11px] font-bold text-purple-800 dark:text-purple-300">
                                                <input type="checkbox" name="is_vip" value="1" {{ $player->isVip() ? 'checked' : '' }} class="rounded text-purple-600">
                                                <span>VIP Actief</span>
                                            </label>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">Kies VIP Tier</label>
                                                <select name="vip_tier" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                                    <option value="1" {{ $player->getSupporterTier() <= 1 ? 'selected' : '' }}>Tier 1: TBS V.I.P. Lid</option>
                                                    <option value="2" {{ $player->getSupporterTier() === 2 ? 'selected' : '' }}>Tier 2: Gangster Elite</option>
                                                    <option value="3" {{ $player->getSupporterTier() === 3 ? 'selected' : '' }}>Tier 3: Kliniek Baas</option>
                                                    <option value="4" {{ $player->getSupporterTier() === 4 ? 'selected' : '' }}>Tier 4: Lifetime V.I.P.</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">Actie / Looptijd</label>
                                                <select name="vip_action" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                                    <option value="no_change">Behoud huidige status</option>
                                                    <option value="add_days">Dagen toevoegen (+X)</option>
                                                    <option value="set_days">Instellen op X dagen vanaf nu</option>
                                                    <option value="lifetime">Permanent (Levenslang)</option>
                                                    <option value="revoke">Direct intrekken / Uitzetten</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 items-center">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">Aantal Dagen (X)</label>
                                                <input type="number" name="custom_vip_days" value="30" min="1" max="3650" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1 text-xs font-mono font-bold text-slate-900 dark:text-slate-100">
                                            </div>
                                            <div class="flex items-center pt-3">
                                                <label class="flex items-center gap-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                                                    <input type="checkbox" name="has_custom_name_color" value="1" {{ !empty($player->perks['has_custom_name_color']) ? 'checked' : '' }} class="rounded text-purple-600">
                                                    <span>Kleur Naam</span>
                                                </label>
                                            </div>
                                            <div class="flex items-center pt-3">
                                                <label class="flex items-center gap-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                                                    <input type="checkbox" name="has_sparkle_effect" value="1" {{ !empty($player->perks['has_sparkle_effect']) ? 'checked' : '' }} class="rounded text-purple-600">
                                                    <span>Sparkle Effect</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- VIP Points & Respect -->
                                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">VIP Punten</label>
                                            <input type="number" name="vip_points" value="{{ $player->vip_points ?? 0 }}" min="0" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1 text-xs font-mono font-bold text-slate-900 dark:text-slate-100">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">Respect</label>
                                            <input type="number" name="respect" value="{{ $player->respect ?? 0 }}" min="0" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1 text-xs font-mono font-bold text-slate-900 dark:text-slate-100">
                                        </div>
                                    </div>

                                    <!-- Cash & Bank -->
                                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">Contant Geld (€)</label>
                                            <input type="number" name="cash" value="{{ $player->cash ?? 0 }}" min="0" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1 text-xs font-mono font-bold text-slate-900 dark:text-slate-100">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">Bank Saldo (€)</label>
                                            <input type="number" name="bank" value="{{ $player->bank ?? 0 }}" min="0" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1 text-xs font-mono font-bold text-slate-900 dark:text-slate-100">
                                        </div>
                                    </div>

                                    <!-- Sterren, HP, Energie & Isoleercel -->
                                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">Sterren</label>
                                                <input type="number" name="stars" value="{{ $player->stars ?? 0 }}" min="0" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1 text-xs font-mono font-bold text-slate-900 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block">HP %</label>
                                                <input type="number" name="health" value="{{ $player->health ?? 100 }}" min="0" max="100" class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1 text-xs font-mono font-bold text-slate-900 dark:text-slate-100">
                                            </div>
                                        </div>
                                        <input type="hidden" name="energy" value="{{ $player->energy ?? 100 }}">
                                        @if($player->is_in_prison)
                                        <label class="flex items-center gap-1.5 pt-1 text-xs text-rose-700 dark:text-rose-400 font-bold cursor-pointer">
                                            <input type="checkbox" name="release_prison" value="1" class="rounded text-rose-600">
                                            <span>Direct vrijlaten uit cel</span>
                                        </label>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2">
                                    <label class="flex items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-400 font-bold cursor-pointer select-none" title="Overschrijf waarden zelfs als de speler in de tussentijd geld of stats heeft verdiend">
                                        <input type="checkbox" name="force_overwrite" value="1" class="rounded text-amber-600 focus:ring-amber-500">
                                        <span>Forceer overschrijven bij tussentijdse wijzigingen</span>
                                    </label>
                                    <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                                        <i class="fa-solid fa-floppy-disk mr-1"></i> Wijzigingen Opslaan
                                    </button>
                                </div>
                            </form>

                            <!-- Moderatie & Sancties Sectie (Waarschuwing & Verbanning) -->
                            @if($player->user)
                            <div class="mt-6 pt-5 border-t border-purple-200/80 dark:border-slate-800 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                
                                <!-- Waarschuwing Uitdelen -->
                                <div class="bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/70 rounded-xl p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-amber-200 dark:border-amber-800/70 pb-2">
                                        <h5 class="font-black text-amber-950 dark:text-amber-300 uppercase tracking-wide flex items-center gap-1.5">
                                            <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400"></i> Officiële Waarschuwing Geven
                                        </h5>
                                        <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 bg-amber-200/70 dark:bg-amber-900/60 px-2 py-0.5 rounded-full">
                                            {{ $player->user->warnings->count() }} eerdere waarschuwingen
                                        </span>
                                    </div>
                                    <form action="{{ route('admin.moderation.warn', $player->user) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="text-[10px] font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider block">Reden van de berisping:</label>
                                            <input type="text" name="reason" required placeholder="bijv. Ongepast taalgebruik op profiel of valsspelen..." 
                                                   class="w-full bg-white dark:bg-slate-800 border border-amber-300 dark:border-amber-700 text-slate-900 dark:text-slate-100 rounded-xl px-3 py-1.5 text-xs focus:ring-1 focus:ring-amber-500 mt-1">
                                        </div>
                                        <button type="submit" class="w-full px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            <span>Waarschuwing Versturen naar Patiënt</span>
                                        </button>
                                    </form>
                                </div>

                                <!-- Verbannen / Deactiveren -->
                                <div class="bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/70 rounded-xl p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-rose-200 dark:border-rose-800/70 pb-2">
                                        <h5 class="font-black text-rose-950 dark:text-rose-300 uppercase tracking-wide flex items-center gap-1.5">
                                            <i class="fa-solid fa-ban text-rose-600 dark:text-rose-400"></i> Speler Verbannen (Ban)
                                        </h5>
                                        @if($player->user->is_banned)
                                            <span class="text-[10px] font-black uppercase text-white bg-rose-600 px-2 py-0.5 rounded-full">
                                                NU VERBANNEN
                                            </span>
                                        @endif
                                    </div>

                                    @if($player->user->is_banned)
                                        <div class="space-y-2">
                                            <div class="p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-rose-200 dark:border-rose-800/70 text-xs">
                                                <p class="font-bold text-rose-900 dark:text-rose-300">Actieve ban tot: <span class="font-normal">{{ $player->user->banned_until ? $player->user->banned_until->diffForHumans() : 'Permanent' }}</span></p>
                                                <p class="text-[11px] text-slate-600 dark:text-slate-400 italic">"{{ $player->user->ban_reason }}"</p>
                                            </div>
                                            <form action="{{ route('admin.moderation.unban', $player->user) }}" method="POST"
                                                  data-confirm="Weet je zeker dat je de ban van {{ $player->name }} wilt opheffen?"
                                                  data-confirm-title="Verbanning Opheffen"
                                                  data-confirm-btn="Ja, Ban Opheffen"
                                                  data-confirm-type="warning"
                                                  data-confirm-icon="fa-lock-open">
                                                @csrf
                                                <button type="submit" class="w-full px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                                    <i class="fa-solid fa-lock-open"></i>
                                                    <span>Verbanning Opheffen (Unban)</span>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <form action="{{ route('admin.moderation.ban', $player->user) }}" method="POST" class="space-y-2"
                                              data-confirm="Weet je zeker dat je deze speler ({{ $player->name }}) wilt verbannen?"
                                              data-confirm-title="Speler Verbannen"
                                              data-confirm-btn="Ja, Verbannen"
                                              data-confirm-type="danger"
                                              data-confirm-icon="fa-gavel">
                                            @csrf
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <div>
                                                    <label class="text-[10px] font-bold text-rose-900 dark:text-rose-300 uppercase tracking-wider block">Duur van ban:</label>
                                                    <select name="duration" class="w-full bg-white dark:bg-slate-800 border border-rose-300 dark:border-rose-700 text-slate-900 dark:text-slate-100 rounded-xl px-2.5 py-1.5 text-xs font-bold focus:ring-1 focus:ring-rose-500 mt-1">
                                                        <option value="1_hour">1 Uur</option>
                                                        <option value="6_hours">6 Uur</option>
                                                        <option value="12_hours">12 Uur</option>
                                                        <option value="1_day" selected>1 Dag (24 uur)</option>
                                                        <option value="3_days">3 Dagen</option>
                                                        <option value="7_days">7 Dagen (1 week)</option>
                                                        <option value="14_days">14 Dagen</option>
                                                        <option value="30_days">30 Dagen (1 maand)</option>
                                                        <option value="permanent">🚨 Permanent (Levenslang)</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold text-rose-900 dark:text-rose-300 uppercase tracking-wider block">Reden van ban:</label>
                                                    <input type="text" name="reason" required placeholder="bijv. Herhaaldelijke overtredingen..." 
                                                           class="w-full bg-white dark:bg-slate-800 border border-rose-300 dark:border-rose-700 text-slate-900 dark:text-slate-100 rounded-xl px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-rose-500 mt-1">
                                                </div>
                                            </div>
                                            <button type="submit" class="w-full px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                                <i class="fa-solid fa-gavel"></i>
                                                <span>Speler Verbannen van Website</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">Geen spelers gevonden voor deze zoekopdracht.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
            {{ $players->links() }}
        </div>
    </div>

</div>
@endsection
