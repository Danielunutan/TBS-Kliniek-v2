@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">
                Gang - De Kantinebende
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Geld, handel en de lekkerste snacks. Samen heersen over de afdelingen.
            </p>
        </div>
        <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-900 font-black text-xs">
            [KB] Rang #1
        </span>
    </div>

    <!-- Gang Split View (Matches middle-right screen in mockup) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Gang Profile Card -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5 text-center">
            <!-- Gang Emblem -->
            <div class="flex justify-center">
                <div class="w-44 h-44 rounded-2xl overflow-hidden border-2 border-purple-600/60 shadow-lg relative bg-purple-950">
                    <img src="{{ asset('assets/gangs/kantinebende.jpg') }}" alt="De Kantinebende" class="w-full h-full object-cover">
                </div>
            </div>

            <div>
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">De Kantinebende</h2>
                <p class="text-xs font-bold text-purple-700 mt-0.5">Geld | Handel | Invloed</p>
            </div>

            <!-- Stats Table -->
            <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 space-y-2 text-xs text-left">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-semibold">Leider:</span>
                    <strong class="text-slate-900">Henkie de Frikandelkoning</strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-semibold">Leden:</span>
                    <strong class="text-slate-900">12</strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-semibold">Gangkas:</span>
                    <strong class="text-emerald-700 font-black">8.450 KC</strong>
                </div>
            </div>

            <button type="button" class="w-full bg-[#6d28d9] hover:bg-[#5b21b6] text-white font-black py-2.5 px-4 rounded-xl text-xs shadow-md transition cursor-pointer">
                Lid worden
            </button>
        </div>

        <!-- Right: Gangberichten / Chat (Matches middle-right mockup) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between min-h-[460px]">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                        Gangberichten
                    </h3>
                    <span class="text-[11px] font-bold text-slate-400">Live chat</span>
                </div>

                <!-- Chat Stream -->
                <div class="space-y-4">
                    <!-- Message 1: Henkie -->
                    <div class="flex items-start gap-3">
                        <img src="{{ asset('assets/characters/henkie.jpg') }}" alt="Henkie" class="w-9 h-9 rounded-full object-cover border border-purple-600 shrink-0">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-xs text-purple-900">Henkie</span>
                                <span class="text-[10px] text-slate-400 font-medium">12-04-2025 14:32</span>
                            </div>
                            <p class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100 mt-1">
                                Vandaag weer een mega missie! Iedereen online?
                            </p>
                        </div>
                    </div>

                    <!-- Message 2: BakkerieRianne -->
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center font-bold text-xs shrink-0">
                            BR
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-xs text-purple-900">BakkerieRianne</span>
                                <span class="text-[10px] text-slate-400 font-medium">12-04-2025 12:20</span>
                            </div>
                            <p class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100 mt-1">
                                Ik heb 500 KC gestort in de gangkas.
                            </p>
                        </div>
                    </div>

                    <!-- Message 3: FrikandelQueen -->
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs shrink-0">
                            FQ
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-xs text-purple-900">FrikandelQueen</span>
                                <span class="text-[10px] text-slate-400 font-medium">11-04-2025 21:10</span>
                            </div>
                            <p class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100 mt-1">
                                Nieuwe leden welkom!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Input Bar (Matches mockup) -->
            <div class="pt-4 mt-4 border-t border-slate-100">
                <form onsubmit="event.preventDefault();" class="flex items-center gap-2">
                    <input type="text" placeholder="Typ een bericht..." class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-purple-600">
                    <button type="submit" class="bg-[#6d28d9] hover:bg-[#5b21b6] text-white w-9 h-9 rounded-xl flex items-center justify-center text-xs shadow-xs transition cursor-pointer shrink-0">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Gang Navigation Tabs (Matches bottom tabs in mockup) -->
    <div class="bg-white p-3 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-around text-xs font-black text-slate-600 overflow-x-auto">
        <button class="py-2 px-4 rounded-xl text-purple-900 bg-purple-50 font-black">Leden</button>
        <button class="py-2 px-4 rounded-xl hover:text-purple-900 transition">Gangmissies</button>
        <button class="py-2 px-4 rounded-xl hover:text-purple-900 transition">Gangkas</button>
        <button class="py-2 px-4 rounded-xl hover:text-purple-900 transition">Upgrades</button>
        <button class="py-2 px-4 rounded-xl hover:text-purple-900 transition">Oorlogen</button>
    </div>
</div>
@endsection
