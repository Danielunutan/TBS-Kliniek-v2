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
<div class="p-4 rounded-2xl {{ $cpIsHolder ? 'bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/40' : 'bg-slate-900/80 border border-slate-800' }} shadow-sm mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl {{ $cpIsHolder ? 'bg-amber-500 text-white' : 'bg-slate-800 text-amber-500' }} flex items-center justify-center text-lg shrink-0 shadow-inner">
            <i class="fa-solid fa-flag"></i>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    Controlepunt: {{ $cpPoint->name }}
                </span>
                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase tracking-wider {{ $cpIsHolder ? 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700' : ($cpPoint->holder ? 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800') }}">
                    {{ $cpIsHolder ? 'Jouw Heerschappij' : ($cpPoint->holder ? 'Bezet' : 'Vrij') }}
                </span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                @if($cpPoint->holder)
                    In handen van <strong class="{{ $cpIsHolder ? 'text-amber-500' : 'text-slate-200' }}">{{ $cpPoint->holder->name }}</strong> ({{ $cpHeldMinutes }}m) &bull; Opgebouwd: <span class="text-emerald-500 font-bold">+€{{ number_format($cpPendingCash, 0, ',', '.') }}</span> & <span class="text-amber-500 font-bold">+{{ $cpPendingStars }} ⭐</span>
                @else
                    Nog door niemand geclaimd! Eis dit punt direct op voor +€50 en +1⭐ per minuut.
                @endif
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('control-points.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-layer-group text-[10px]"></i>
            <span>Alle Punten</span>
        </a>

        @if($cpIsHolder)
            <span class="px-3 py-2 rounded-xl bg-amber-500/20 text-amber-500 border border-amber-500/40 text-xs font-black uppercase flex items-center gap-1.5">
                <i class="fa-solid fa-crown text-[10px]"></i>
                <span>Jij bent de Baas</span>
            </span>
        @elseif($cpCharacter && $cpCharacter->isImprisoned())
            <span class="px-3 py-2 rounded-xl bg-slate-800 text-slate-500 text-xs font-bold">
                <i class="fa-solid fa-lock text-[10px]"></i> Isoleercel
            </span>
        @elseif($cpCharacter && $cpCharacter->energy < $cpPoint->energy_cost)
            <span class="px-3 py-2 rounded-xl bg-slate-800 text-slate-500 text-xs font-bold">
                <i class="fa-solid fa-bolt text-[10px]"></i> 2⚡ Nodig
            </span>
        @else
            <form method="POST" action="{{ route('control-points.capture', $cpPoint) }}">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-black uppercase tracking-wider transition shadow-sm shadow-amber-500/20 flex items-center gap-1.5 hover:scale-[1.02]">
                    <i class="fa-solid fa-flag text-[10px]"></i>
                    <span>{{ $cpPoint->holder ? 'Overnemen (2⚡)' : 'Opeisen (2⚡)' }}</span>
                </button>
            </form>
        @endif
    </div>
</div>
@endif
