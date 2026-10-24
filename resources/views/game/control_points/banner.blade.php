@php
    $cpKey = $slug ?? $key ?? null;
    $cpPoint = $cpKey ? \App\Models\ControlPoint::where('key', $cpKey)->with(['holder.user', 'holder.gang'])->first() : null;
    $cpCharacter = auth()->user()?->character;
    $cpIsHolder = $cpPoint && $cpCharacter && $cpPoint->current_character_id === $cpCharacter->id;
    $cpHeldMinutes = $cpPoint ? $cpPoint->getHeldMinutes() : 0;
    $cpPendingCash = $cpPoint ? $cpPoint->getPendingCash() : 0;
    $cpPendingStars = $cpPoint ? $cpPoint->getPendingStars() : 0;
@endphp

@if($cpPoint)
<div class="relative overflow-hidden rounded-2xl border transition-all duration-300 mb-6 shadow-xs {{ $cpIsHolder ? 'bg-gradient-to-r from-amber-500/15 via-amber-500/5 to-white dark:to-slate-900 border-amber-400 dark:border-amber-500/60 shadow-amber-500/10' : ($cpPoint->holder ? 'bg-white dark:bg-slate-900/90 border-slate-200 dark:border-slate-800' : 'bg-gradient-to-r from-emerald-500/10 via-emerald-500/[0.03] to-white dark:to-slate-900 border-emerald-300 dark:border-emerald-700/60') }}">
    
    <!-- Ambient corner glow -->
    <div class="absolute -top-10 -right-10 w-36 h-36 {{ $cpIsHolder ? 'bg-amber-500/20' : ($cpPoint->holder ? 'bg-purple-500/10' : 'bg-emerald-500/15') }} rounded-full blur-2xl pointer-events-none"></div>

    <div class="p-4 sm:p-5 relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Left info section -->
        <div class="flex items-start sm:items-center gap-3.5 min-w-0">
            <!-- Icon box -->
            <div class="w-12 h-12 rounded-xl {{ $cpIsHolder ? 'bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 text-white ring-2 ring-amber-300 dark:ring-amber-400/50 shadow-md shadow-amber-500/30' : ($cpPoint->holder ? 'bg-gradient-to-br from-slate-800 to-slate-900 dark:from-slate-800 dark:to-slate-950 text-amber-400 border border-slate-700/60 dark:border-slate-700 shadow-md' : 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/25') }} flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid {{ $cpIsHolder ? 'fa-crown' : ($cpPoint->icon ?? 'fa-flag') }}"></i>
            </div>

            <!-- Title & meta description -->
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $cpIsHolder ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700' : ($cpPoint->holder ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700') }}">
                        <i class="fa-solid fa-flag text-[9px] {{ $cpIsHolder ? 'text-amber-500' : ($cpPoint->holder ? 'text-slate-400' : 'text-emerald-500') }}"></i>
                        King of the Hill
                    </span>
                    <span class="text-xs font-black text-slate-900 dark:text-white truncate">
                        {{ $cpPoint->name }}
                    </span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium hidden sm:inline">
                        ({{ $cpPoint->location_name }})
                    </span>
                </div>

                <!-- Holder & live buit summary -->
                <div class="text-xs text-slate-600 dark:text-slate-400 flex flex-wrap items-center gap-x-2 gap-y-1">
                    @if($cpIsHolder)
                        <span class="font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                            <i class="fa-solid fa-check-circle text-xs"></i> Jij heerst hier al {{ $cpHeldMinutes }} min.
                        </span>
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-black">
                            <i class="fa-solid fa-sack-dollar text-[11px]"></i> +€{{ number_format($cpPendingCash, 0, ',', '.') }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-amber-500 font-black">
                            <i class="fa-solid fa-star text-[11px]"></i> +{{ $cpPendingStars }} ⭐
                        </span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500">(+€{{ $cpPoint->cash_per_minute }} en +{{ $cpPoint->stars_per_minute }}⭐ per minuut)</span>
                    @elseif($cpPoint->holder)
                        <span class="flex items-center gap-1.5">
                            <span>Bezet door</span>
                            <a href="{{ route('profile.show', $cpPoint->holder->name) }}" class="font-black text-slate-900 dark:text-slate-100 hover:text-amber-500 dark:hover:text-amber-400 underline decoration-slate-300 dark:decoration-slate-700 underline-offset-2 transition">
                                {{ $cpPoint->holder->name }}
                            </a>
                            @if($cpPoint->holder->gang)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                    [{{ $cpPoint->holder->gang->tag }}]
                                </span>
                            @endif
                        </span>
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span>{{ $cpHeldMinutes }}m bezet</span>
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-black">
                            +€{{ number_format($cpPendingCash, 0, ',', '.') }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-amber-500 font-black">
                            +{{ $cpPendingStars }} ⭐
                        </span>
                    @else
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-sparkles text-xs"></i> Niemand bezit dit punt! Eis direct op en start met buit opbouwen.
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right action section -->
        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
            @if($cpIsHolder)
                <div class="px-3.5 py-2 rounded-xl bg-amber-100/80 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700/80 text-xs font-black uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-crown text-amber-500 text-sm animate-bounce"></i>
                    <span>Jij bent de Baas</span>
                </div>
            @elseif($cpCharacter && $cpCharacter->isImprisoned())
                <span class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold border border-slate-200 dark:border-slate-700 flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-rose-500"></i>
                    <span>Isoleercel</span>
                </span>
            @elseif($cpCharacter && $cpCharacter->energy < $cpPoint->energy_cost)
                <span class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold border border-slate-200 dark:border-slate-700 flex items-center gap-1.5">
                    <i class="fa-solid fa-bolt text-amber-500"></i>
                    <span>2 Energie Nodig</span>
                </span>
            @else
                <form method="POST" action="{{ route('control-points.capture', $cpPoint) }}" class="inline m-0">
                    @csrf
                    <button type="submit" 
                            class="group relative px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-600 to-amber-500 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-black uppercase tracking-wider shadow-sm shadow-amber-500/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-flag text-[11px] transition-transform group-hover:rotate-12"></i>
                        <span>{{ $cpPoint->holder ? 'Controlepunt Overnemen (-2⚡)' : 'Controlepunt Opeisen (-2⚡)' }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endif