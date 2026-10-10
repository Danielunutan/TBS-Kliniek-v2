@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-dumbbell text-purple-700"></i>
                <span>De Sportzaal & Oefenruimte</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                Train je vaardigheden onder leiding van beruchte kliniekfiguren. Hogere eigenschappen verhogen je overlevingskansen bij gevechten, diefstallen en missies.
            </p>
        </div>
        <div class="bg-purple-950 text-white px-4 py-2 rounded-lg text-xs font-semibold border border-purple-800">
            <span>Contant Geld: <strong class="text-amber-400">{{ number_format($character->cash, 0, ',', '.') }} KC</strong></span>
        </div>
    </div>

    <!-- Training Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($stats as $key => $info)
        @php
            $val = $character->{$key};
            $cashCost = (int) max(20, round(15 + ($val * 3.5)));
            $canAfford = $character->cash >= $cashCost && $character->energy >= 10;
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-900">{{ $info['name'] }}</h2>
                        <span class="text-[11px] font-bold text-purple-800">
                            <i class="fa-solid fa-user-ninja mr-1"></i>Trainer: {{ $info['trainer'] }}
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-900 text-amber-300 font-black text-lg flex items-center justify-center shadow-inner">
                        {{ $val }}
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded border border-slate-100 min-h-[48px]">
                    {{ $info['desc'] }}
                </p>

                <!-- Costs -->
                <div class="flex items-center justify-between text-xs font-semibold pt-1">
                    <span class="text-yellow-700"><i class="fa-solid fa-bolt text-yellow-500 mr-1"></i>10 Energie</span>
                    <span class="text-amber-700"><i class="fa-solid fa-coins text-amber-500 mr-1"></i>{{ $cashCost }} KC</span>
                    <span class="text-purple-700 font-bold">+1 {{ $info['name'] }}</span>
                </div>
            </div>

            <!-- Train Button -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200">
                <form method="POST" action="{{ route('training.train') }}">
                    @csrf
                    <input type="hidden" name="stat" value="{{ $key }}">
                    <button type="submit" @if(! $canAfford || $character->isImprisoned()) disabled @endif class="w-full bg-[#3a156b] hover:bg-[#280c4f] disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-2 px-4 rounded text-xs shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-dumbbell"></i>
                        <span>Trainen (+1)</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Logs -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-xs font-black uppercase text-slate-800 tracking-wider flex items-center gap-2 mb-3">
            <i class="fa-solid fa-clock-rotate-left text-purple-700"></i>
            <span>Recente Trainingssessies</span>
        </h3>
        <div class="divide-y divide-slate-100 text-xs">
            @forelse($recentLogs as $log)
            <div class="py-2.5 flex items-center justify-between">
                <div>
                    <span class="font-bold text-slate-800">{{ $stats[$log->stat]['name'] ?? $log->stat }}</span> verhoogd met 
                    <span class="font-bold text-purple-700">+{{ $log->stat_gain }}</span> door workout met 
                    <span class="italic text-slate-600">{{ $log->trainer }}</span>.
                </div>
                <div class="text-[11px] text-slate-400">
                    {{ $log->created_at->diffForHumans() }}
                </div>
            </div>
            @empty
            <p class="text-slate-500 py-2">Nog geen trainingen gelogd. Tijd om die halters op te pakken!</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
