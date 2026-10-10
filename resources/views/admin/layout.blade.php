@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Admin Top Nav Bar -->
    <div class="bg-slate-900 border border-purple-500/30 rounded-2xl p-5 shadow-lg text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-purple-600/30 border border-purple-400/40 flex items-center justify-center text-xl text-purple-300">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight">Medewerkers Paneel</h1>
                        <span class="bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full">
                            STAFF V1
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Beheer van patiënten, VIP-toekenning, spelbalans, winkansen, kisten en de centrale kluis.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-slate-200 transition flex items-center gap-1.5 border border-white/10">
                    <i class="fa-solid fa-gamepad"></i> Terug naar Spel
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-2 pt-5 mt-5 border-t border-white/10 text-xs font-bold">
            <a href="{{ route('admin.dashboard') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie"></i> Overzicht
            </a>
            <a href="{{ route('admin.players') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.players') ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-users"></i> Spelers & VIP
            </a>
            <a href="{{ route('admin.gameplay') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.gameplay') ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-sliders"></i> Winkansen & Balans
            </a>
            <a href="{{ route('admin.chests') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.chests') ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-box-open"></i> Kisten & Loot
            </a>
            <a href="{{ route('admin.vault') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.vault') ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-vault"></i> Kluis & Codes
            </a>
        </div>
    </div>

    <!-- Admin Content Sub-View -->
    @yield('admin-content')

</div>
@endsection
