@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <!-- Secret Vault Code Control Card -->
    <div class="bg-linear-to-r from-slate-950 via-purple-950 to-slate-900 border border-purple-800/40 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
            <div>
                <span class="inline-flex items-center gap-1.5 bg-amber-400/20 text-amber-300 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full border border-amber-400/30 mb-2">
                    <i class="fa-solid fa-key"></i> Centrale Kliniekkluiskraak
                </span>
                <h3 class="text-xl sm:text-2xl font-black">Huidige Geheime Kluiscode</h3>
                <p class="text-xs text-purple-200 mt-1 max-w-lg">
                    Dit is de actieve code die spelers moeten raden of ontcijferen in de Kluis-Kraak minigame.
                </p>
            </div>

            <div class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-xs">
                <div class="text-center">
                    <span class="text-[10px] font-extrabold uppercase text-slate-400 block">ACTIEVE CODE</span>
                    <span class="text-3xl sm:text-4xl font-mono font-black tracking-widest text-amber-400">
                        {{ $settings['vault_code'] }}
                    </span>
                </div>
                <form action="{{ route('admin.vault.randomize') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            title="Genereer direct een nieuwe willekeurige 4-cijferige code"
                            class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black uppercase tracking-wider transition cursor-pointer shadow-lg">
                        <i class="fa-solid fa-shuffle mr-1"></i> Nieuwe Code
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Vault Configuration Form -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-vault text-purple-600"></i> Kluis Jackpot & Hulpmiddelen
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                Stel handmatig de code in en bepaal de hoofdprijs die de kraker wint.
            </p>
        </div>

        <form action="{{ route('admin.vault.update') }}" method="POST" class="space-y-6 max-w-3xl">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 block mb-1">Handmatige Kluiscode</label>
                    <p class="text-[11px] text-slate-500 mb-2">Pas de code direct aan naar eigen wens.</p>
                    <input type="text" name="vault_code" value="{{ $settings['vault_code'] }}" maxlength="10" 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-black text-purple-700">
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 block mb-1">Kluis Hoofdprijs Cash (€)</label>
                    <p class="text-[11px] text-slate-500 mb-2">Contante jackpot voor het succesvol kraken.</p>
                    <input type="number" name="vault_reward_cash" value="{{ $settings['vault_reward_cash'] }}" min="0" 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-black text-emerald-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 block mb-1">KluisMunten (🔑)</label>
                    <input type="number" name="vault_reward_tokens" value="{{ $settings['vault_reward_tokens'] }}" min="0" 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-black text-purple-700">
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 block mb-1">Respect Bonus</label>
                    <input type="number" name="vault_reward_respect" value="{{ $settings['vault_reward_respect'] }}" min="0" 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-black text-purple-700">
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 block mb-1">Hint Prijs (€)</label>
                    <input type="number" name="vault_hint_cost" value="{{ $settings['vault_hint_cost'] }}" min="0" 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-black text-purple-700">
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 block mb-1">Jammer Prijs (€)</label>
                    <input type="number" name="vault_jammer_cost" value="{{ $settings['vault_jammer_cost'] }}" min="0" 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-black text-purple-700">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-purple-900/20">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Kluis Instellingen Opslaan
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Vault Codes List -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
        <h4 class="text-xs font-black uppercase text-slate-900 tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="fa-solid fa-history text-purple-600"></i> Uitgegeven & Ingewisselde Codes
        </h4>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-2.5 px-3">CODE</th>
                        <th class="py-2.5 px-3">PATIËNT</th>
                        <th class="py-2.5 px-3 text-right">CASH</th>
                        <th class="py-2.5 px-3 text-right">STERREN</th>
                        <th class="py-2.5 px-3 text-center">STATUS</th>
                        <th class="py-2.5 px-3 text-right">DATUM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentCodes as $code)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3 font-mono font-bold text-purple-700">{{ $code->code }}</td>
                        <td class="py-2.5 px-3 text-slate-900">{{ $code->character?->name ?? 'Algemeen' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-emerald-600 font-bold">€{{ number_format($code->reward_cash, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-amber-500 font-bold">⭐ {{ $code->reward_stars }}</td>
                        <td class="py-2.5 px-3 text-center">
                            @if($code->is_redeemed)
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-full">Ingewisseld</span>
                            @else
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full">Actief</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-right text-slate-400 text-[11px]">{{ $code->created_at->format('d-m-Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400 text-xs">Geen recente kluiscodes gevonden.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
