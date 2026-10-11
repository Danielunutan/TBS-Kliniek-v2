@extends('admin.layout')

@section('admin-content')
<div class="space-y-6" x-data="{ editingId: null }">

    <!-- Filters & Search Toolbar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.players') }}" class="w-full sm:w-auto flex items-center gap-2">
            <div class="relative flex-1 sm:w-72">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Zoek op patiëntnaam of email..." 
                       class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-purple-600 focus:bg-white">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-400 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition">
                Zoeken
            </button>
            @if(request('search') || request('filter'))
                <a href="{{ route('admin.players') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                    Wissen
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.players') }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ !request('filter') ? 'bg-purple-100 text-purple-700 font-extrabold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Alle ({{ $players->total() }})
            </a>
            <a href="{{ route('admin.players', ['filter' => 'staff']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request('filter') === 'staff' ? 'bg-indigo-100 text-indigo-800 font-extrabold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fa-solid fa-shield-halved text-indigo-600 mr-1"></i> Staff / Mod
            </a>
            <a href="{{ route('admin.players', ['filter' => 'vip']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request('filter') === 'vip' ? 'bg-amber-100 text-amber-800 font-extrabold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fa-solid fa-crown text-amber-500 mr-1"></i> VIPs
            </a>
            <a href="{{ route('admin.players', ['filter' => 'prison']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request('filter') === 'prison' ? 'bg-rose-100 text-rose-800 font-extrabold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fa-solid fa-lock text-rose-500 mr-1"></i> Isoleercel
            </a>
        </div>
    </div>

    <!-- Players Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                        <th class="py-3 px-4">PATIËNT</th>
                        <th class="py-3 px-3">FUNCTIE</th>
                        <th class="py-3 px-3">GANG</th>
                        <th class="py-3 px-3 text-right">CONTANT</th>
                        <th class="py-3 px-3 text-right">BANK</th>
                        <th class="py-3 px-3 text-right">STERREN</th>
                        <th class="py-3 px-3 text-right">VIP PUNTEN</th>
                        <th class="py-3 px-3 text-center">VIP STATUS</th>
                        <th class="py-3 px-3 text-center">CEL STATUS</th>
                        <th class="py-3 px-4 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($players as $player)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-200">
                                <div>
                                    <div class="font-extrabold text-slate-900 text-xs">{{ $player->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $player->user?->email ?? '-' }}</div>
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
                                <span class="text-slate-400 text-[10px]">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-slate-600">
                            {{ $player->gang?->name ?? 'Geen' }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-emerald-600">
                            €{{ number_format($player->cash, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-blue-600">
                            €{{ number_format($player->bank, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-amber-500">
                            <i class="fa-solid fa-star text-amber-400 text-xs"></i> {{ number_format($player->stars, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-purple-700">
                            <i class="fa-solid fa-gem text-purple-500 text-xs"></i> {{ number_format($player->vip_points ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($player->is_vip)
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-amber-200">
                                    <i class="fa-solid fa-crown text-amber-600"></i> VIP
                                </span>
                            @else
                                <span class="text-slate-400 text-[10px]">Nee</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($player->is_in_prison)
                                <span class="bg-rose-100 text-rose-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-rose-200">
                                    <i class="fa-solid fa-lock text-rose-600"></i> Vast
                                </span>
                            @else
                                <span class="text-emerald-600 text-[10px] font-bold">Vrij</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button @click="editingId = editingId === {{ $player->id }} ? null : {{ $player->id }}"
                                    class="px-3 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-600 hover:text-white text-purple-700 text-xs font-bold transition cursor-pointer">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Wijzig
                            </button>
                        </td>
                    </tr>

                    <!-- Expandable Edit Form Drawer -->
                    <tr x-show="editingId === {{ $player->id }}" x-cloak class="bg-purple-50/50">
                        <td colspan="10" class="p-6 border-y border-purple-200">
                            <form action="{{ route('admin.player.update', $player) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="flex items-center justify-between border-b border-purple-200 pb-2">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-purple-900 flex items-center gap-1.5">
                                        <i class="fa-solid fa-sliders text-purple-600"></i> Patiënt Aanpassen: {{ $player->name }}
                                    </h4>
                                    <button type="button" @click="editingId = null" class="text-xs text-slate-500 hover:text-slate-900 cursor-pointer">
                                        Sluiten &times;
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                    <!-- Rol / Functie Ranking -->
                                    <div class="bg-white p-3.5 rounded-xl border border-purple-200 space-y-2">
                                        <label class="text-[10px] font-black text-purple-900 uppercase tracking-wider block flex items-center gap-1.5">
                                            <i class="fa-solid fa-ranking-star text-purple-600"></i> Functie & Rank
                                        </label>
                                        <div>
                                            <select name="role" class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-xs font-bold text-slate-800 focus:bg-white focus:ring-1 focus:ring-purple-600">
                                                <option value="user" {{ ($player->user?->role ?? 'user') === 'user' ? 'selected' : '' }}>Lid (Speler)</option>
                                                <option value="moderator" {{ ($player->user?->role ?? '') === 'moderator' ? 'selected' : '' }}>Moderator (Mod)</option>
                                                <option value="admin" {{ ($player->user?->role ?? '') === 'admin' ? 'selected' : '' }}>Beheerder (Admin)</option>
                                                @if(Auth::user()->isOwner())
                                                <option value="owner" {{ ($player->user?->role ?? '') === 'owner' ? 'selected' : '' }}>👑 Eigenaar</option>
                                                @endif
                                            </select>
                                            <p class="text-[10px] text-slate-400 mt-1.5 leading-snug">
                                                Moderators kunnen ongepaste berichten modereren & verwijderen.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- VIP Toggle & Days -->
                                    <div class="bg-white p-3.5 rounded-xl border border-purple-200 space-y-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_vip" value="1" {{ $player->is_vip ? 'checked' : '' }} class="rounded text-purple-600 focus:ring-purple-500">
                                            <span class="text-xs font-bold text-slate-900">VIP Status Actief</span>
                                        </label>
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 block">Dagen toevoegen (0 = ongewijzigd)</label>
                                            <input type="number" name="vip_days" value="30" min="0" class="w-full bg-slate-50 border border-slate-300 rounded px-2.5 py-1 text-xs font-mono font-bold">
                                        </div>
                                    </div>

                                    <!-- VIP Points & Respect -->
                                    <div class="bg-white p-3.5 rounded-xl border border-purple-200 space-y-2">
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 block">VIP Punten</label>
                                            <input type="number" name="vip_points" value="{{ $player->vip_points ?? 0 }}" min="0" class="w-full bg-slate-50 border border-slate-300 rounded px-2.5 py-1 text-xs font-mono font-bold">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 block">Respect</label>
                                            <input type="number" name="respect" value="{{ $player->respect }}" min="0" class="w-full bg-slate-50 border border-slate-300 rounded px-2.5 py-1 text-xs font-mono font-bold">
                                        </div>
                                    </div>

                                    <!-- Cash & Bank -->
                                    <div class="bg-white p-3.5 rounded-xl border border-purple-200 space-y-2">
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 block">Contant Geld (€)</label>
                                            <input type="number" name="cash" value="{{ $player->cash }}" min="0" class="w-full bg-slate-50 border border-slate-300 rounded px-2.5 py-1 text-xs font-mono font-bold">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 block">Bank Saldo (€)</label>
                                            <input type="number" name="bank" value="{{ $player->bank }}" min="0" class="w-full bg-slate-50 border border-slate-300 rounded px-2.5 py-1 text-xs font-mono font-bold">
                                        </div>
                                    </div>

                                    <!-- Sterren, HP, Energie & Isoleercel -->
                                    <div class="bg-white p-3.5 rounded-xl border border-purple-200 space-y-2">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 block">Sterren</label>
                                                <input type="number" name="stars" value="{{ $player->stars }}" min="0" class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1 text-xs font-mono font-bold">
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 block">HP %</label>
                                                <input type="number" name="health" value="{{ $player->health }}" min="0" max="100" class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1 text-xs font-mono font-bold">
                                            </div>
                                        </div>
                                        <input type="hidden" name="energy" value="{{ $player->energy }}">
                                        @if($player->is_in_prison)
                                        <label class="flex items-center gap-1.5 pt-1 text-xs text-rose-700 font-bold cursor-pointer">
                                            <input type="checkbox" name="release_prison" value="1" class="rounded text-rose-600">
                                            <span>Direct vrijlaten uit cel</span>
                                        </label>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow">
                                        <i class="fa-solid fa-floppy-disk mr-1"></i> Wijzigingen Opslaan
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-400 text-xs">Geen spelers gevonden voor deze zoekopdracht.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 flex justify-end">
            {{ $players->links() }}
        </div>
    </div>

</div>
@endsection
