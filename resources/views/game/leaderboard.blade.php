@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <span>Ranglijsten van de Kliniek</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                Wie zijn de meest gevreesde, gerespecteerde en rijkste patiënten van de TBS Kliniek?
            </p>
        </div>

        <!-- Type Selector -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg text-xs font-bold text-slate-600">
            <a href="{{ route('leaderboard.index', ['type' => 'level']) }}" class="px-3 py-1.5 rounded transition {{ $type === 'level' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Level & XP</a>
            <a href="{{ route('leaderboard.index', ['type' => 'respect']) }}" class="px-3 py-1.5 rounded transition {{ $type === 'respect' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Respect</a>
            <a href="{{ route('leaderboard.index', ['type' => 'wealth']) }}" class="px-3 py-1.5 rounded transition {{ $type === 'wealth' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Vermogen (KC)</a>
            <a href="{{ route('leaderboard.index', ['type' => 'crimes']) }}" class="px-3 py-1.5 rounded transition {{ $type === 'crimes' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Diefstallen</a>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase text-[10px] font-black tracking-wider text-slate-500">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Patiënt</th>
                        <th class="py-3 px-4">Gang</th>
                        <th class="py-3 px-4 text-center">Level</th>
                        <th class="py-3 px-4 text-right">
                            @if($type === 'respect') Respect
                            @elseif($type === 'wealth') Totale KC
                            @elseif($type === 'crimes') Diefstallen
                            @else Totale XP
                            @endif
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($topCharacters as $index => $c)
                    <tr class="hover:bg-slate-50 transition {{ $character->id === $c->id ? 'bg-purple-50/70 font-bold' : '' }}">
                        <td class="py-3 px-4 text-center font-black">
                            @if($index === 0)
                                <span class="w-6 h-6 rounded-full bg-amber-400 text-purple-950 inline-flex items-center justify-center font-black shadow-sm">1</span>
                            @elseif($index === 1)
                                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-800 inline-flex items-center justify-center font-black">2</span>
                            @elseif($index === 2)
                                <span class="w-6 h-6 rounded-full bg-amber-700 text-white inline-flex items-center justify-center font-black">3</span>
                            @else
                                <span class="text-slate-400">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <a href="{{ route('profile.show', $c->name) }}" class="font-black text-purple-900 hover:underline flex items-center gap-1.5">
                                <span>{{ $c->name }}</span>
                                @if($character->id === $c->id)
                                    <span class="text-[10px] px-1 py-0.2 rounded bg-purple-200 text-purple-900 font-bold">(Jij)</span>
                                @endif
                            </a>
                            <span class="text-[10px] text-slate-400 block">{{ $c->title }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @if($c->gang)
                                <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-900 font-bold text-[10px]">
                                    [{{ $c->gang->tag }}] {{ $c->gang->name }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Geen</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-black text-slate-900">
                            {{ $c->level }}
                        </td>
                        <td class="py-3 px-4 text-right font-black">
                            @if($type === 'respect')
                                <span class="text-indigo-900">{{ number_format($c->respect, 0, ',', '.') }}</span>
                            @elseif($type === 'wealth')
                                <span class="text-emerald-700">{{ number_format($c->cash + $c->bank, 0, ',', '.') }} KC</span>
                            @elseif($type === 'crimes')
                                <span class="text-rose-700">{{ $c->thefts_succeeded }}</span>
                            @else
                                <span class="text-purple-900">{{ number_format($c->total_xp, 0, ',', '.') }} XP</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
