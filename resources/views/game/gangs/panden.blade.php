@extends('layouts.app')

@section('title', 'Bende Panden, Fabrieken & NPC Rekruten - ' . $gang->name . ' - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="{
    activeCategory: 'all',
    selectedProperty: null,
    showNpcModal: false
}">

    <!-- Top Hero Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-7 shadow-xs relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('gangs.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                        <i class="fa-solid fa-arrow-left"></i> Terug naar Bende
                    </a>
                    <span class="text-slate-300 dark:text-slate-700">•</span>
                    <a href="{{ route('marketplace.index', ['type' => 'property']) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                        <i class="fa-solid fa-shop"></i> Marktplaats Panden
                    </a>
                    <span class="text-slate-300 dark:text-slate-700">•</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-black text-[10px] uppercase tracking-wider">
                        [{{ $gang->tag }}] {{ $gang->name }}
                    </span>
                    @if($isOfficer)
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-extrabold text-[10px]">
                            <i class="fa-solid fa-crown mr-1"></i> Beheerder
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    Bende Panden & Vastgoed
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-2xl leading-relaxed">
                    Breid het territorium van je bende uit. Koop loodsen voor extra opslag en voertuigen, start winstgevende fabrieken en vestig luxe villa's om gespecialiseerde NPC bendeleden te huisvesten!
                </p>
            </div>

            <!-- Gang Treasury Box -->
            <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 sm:p-5 shrink-0 flex flex-col items-center lg:items-end justify-center">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Beschikbare Bendekas</span>
                <span class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">
                    € {{ number_format($gang->treasury, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Panden & NPC's worden gefinancierd via de bendekas
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Storage Capacity -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase text-slate-500">Collectieve Opslag</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                +{{ number_format($totalStorage, 0, ',', '.') }} <span class="text-xs text-slate-400 font-sans font-bold">slots</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Extra kluis- & buitcapaciteit</p>
        </div>

        <!-- Vehicle Slots -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase text-slate-500">Voertuig Stallingen</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-car"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                {{ number_format($totalVehicles, 0, ',', '.') }} <span class="text-xs text-slate-400 font-sans font-bold">wagens</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Voor heists en bende vervoer</p>
        </div>

        <!-- NPC Capacity -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase text-slate-500">NPC Bendeleden</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-user-ninja"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-purple-600 dark:text-purple-400">
                {{ $hiredNpcs->count() }} / {{ $totalNpcCap }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                @if($availableNpcSlots > 0)
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $availableNpcSlots }} vrije kamer(s) in villa</span>
                @else
                    Koop een villa voor kamers
                @endif
            </p>
        </div>

        <!-- Owned Properties -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase text-slate-500">In Bendebezit</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-city"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                {{ $ownedProperties->count() }} / {{ count($catalog) }} <span class="text-xs text-slate-400 font-sans font-bold">panden</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Actief kliniek vastgoed</p>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-800/80 rounded-2xl overflow-x-auto text-xs font-bold">
        <button type="button" @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-border-all"></i>
            <span>Alle Panden ({{ count($catalog) }})</span>
        </button>
        <button type="button" @click="activeCategory = 'storage'"
                :class="activeCategory === 'storage' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-warehouse text-amber-500"></i>
            <span>Opslag & Stallingen (Loodsen / Bunker)</span>
        </button>
        <button type="button" @click="activeCategory = 'production'"
                :class="activeCategory === 'production' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-industry text-emerald-500"></i>
            <span>Productie & Fabrieken</span>
        </button>
        <button type="button" @click="activeCategory = 'luxury'"
                :class="activeCategory === 'luxury' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-landmark-dome text-purple-500"></i>
            <span>Luxe Panden & Villa's (HQ)</span>
        </button>
        <button type="button" @click="activeCategory = 'npcs'"
                :class="activeCategory === 'npcs' ? 'bg-white dark:bg-slate-900 text-cyan-600 dark:text-cyan-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-users-gear text-cyan-500"></i>
            <span>NPC Rekruten ({{ $hiredNpcs->count() }})</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- PROPERTIES LISTING GRID                    -->
    <!-- ========================================== -->
    <div x-show="activeCategory !== 'npcs'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($catalog as $key => $prop)
        @php
            $owned = $ownedProperties->get($key);
            $isOwned = $owned !== null;
            $canAfford = (int) $gang->treasury >= $prop['price'];
        @endphp
        <div x-show="activeCategory === 'all' || activeCategory === '{{ $prop['category'] }}'"
             class="bg-white dark:bg-slate-900 border {{ $isOwned ? 'border-emerald-400/80 dark:border-emerald-500/80 ring-2 ring-emerald-400/20' : 'border-slate-200 dark:border-slate-800' }} rounded-2xl p-6 shadow-xs flex flex-col justify-between gap-5 relative overflow-hidden transition hover:shadow-md">

            @if($isOwned)
                <div class="absolute -right-12 top-6 bg-emerald-500 text-slate-950 font-black text-[10px] uppercase tracking-wider py-1 px-12 rotate-45 shadow-xs text-center pointer-events-none">
                    In Bezit ✓
                </div>
            @endif

            <div class="space-y-4">
                <!-- Header Icon & Category Badge -->
                <div class="flex items-start justify-between gap-3">
                    <div class="w-14 h-14 rounded-2xl {{ $isOwned ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200' }} border border-slate-200 dark:border-slate-700 flex items-center justify-center text-2xl shrink-0">
                        <i class="fa-solid {{ $prop['icon'] }}"></i>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            {{ $prop['category_label'] }}
                        </span>
                        <div class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                            € {{ number_format($prop['price'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Info Block -->
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">{{ $prop['tagline'] }}</span>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mt-0.5">{{ $prop['name'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        {{ $prop['description'] }}
                    </p>
                </div>

                <!-- Feature Pill -->
                <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 rounded-xl p-3 flex items-center gap-2.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-circle-check text-emerald-500 shrink-0"></i>
                    <span>{{ $prop['perks_summary'] }}</span>
                </div>

                <!-- ========================================== -->
                <!-- IF OWNED & PRODUCTION FACILITY             -->
                <!-- ========================================== -->
                @if($isOwned && $owned->isProduction())
                @php
                    $isReady = $owned->isReadyToClaim();
                    $isProducing = $owned->isProducing();
                    $secondsLeft = $owned->productionSecondsRemaining();
                    $rewards = $owned->getEffectiveRewards();
                    $assignedNpc = $hiredNpcs->firstWhere('assigned_property_id', $owned->id);
                @endphp
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between text-xs font-extrabold">
                        <span class="text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-gear fa-spin text-purple-500"></i> Productiestatus:
                        </span>
                        @if($isReady)
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 animate-pulse">Gereed om te claimen!</span>
                        @elseif($isProducing)
                            <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">In productie...</span>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">Inactief (Klaar voor start)</span>
                        @endif
                    </div>

                    <!-- Assigned Specialist Badge -->
                    <div class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 text-xs flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user-tie text-purple-600 dark:text-purple-400"></i>
                            <div>
                                <span class="font-extrabold text-purple-950 dark:text-purple-200 block text-[11px]">
                                    {{ $assignedNpc ? $assignedNpc->name : 'Geen NPC Specialist' }}
                                </span>
                                <span class="text-[10px] text-purple-700 dark:text-purple-300 block">
                                    {{ $assignedNpc ? $assignedNpc->role_title : 'Wijs een NPC toe voor +25% opbrengst' }}
                                </span>
                            </div>
                        </div>

                        @if($isOfficer && $hiredNpcs->count() > 0)
                        <form method="POST" action="{{ route('gangs.panden.assign-npc', $owned->id) }}" class="inline">
                            @csrf
                            <select name="npc_id" onchange="this.form.submit()" class="text-[10px] py-1 px-1.5 rounded-lg border border-purple-300 dark:border-purple-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold outline-none cursor-pointer">
                                <option value="">{{ $assignedNpc ? 'Verwijder NPC' : '+ Wijs NPC toe' }}</option>
                                @foreach($hiredNpcs as $npc)
                                    <option value="{{ $npc->id }}" {{ $assignedNpc && $assignedNpc->id === $npc->id ? 'selected' : '' }}>
                                        {{ $npc->name }} ({{ $npc->role_title }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        @endif
                    </div>

                    <!-- Production Timer or Rewards -->
                    @if($isProducing)
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl p-3 text-xs space-y-1.5"
                         x-data="{
                             left: {{ $secondsLeft }},
                             format() {
                                 if (this.left <= 0) return 'Gereed! (Ververs pagina)';
                                 const total = Math.floor(this.left);
                                 const h = Math.floor(total / 3600);
                                 const m = Math.floor((total % 3600) / 60);
                                 const s = Math.floor(total % 60);
                                 return `${h}u ${m < 10 ? '0' : ''}${m}m ${s < 10 ? '0' : ''}${s}s`;
                             },
                             init() {
                                 setInterval(() => { if (this.left > 0) this.left--; }, 1000);
                             }
                         }">
                        <div class="flex items-center justify-between text-blue-900 dark:text-blue-200 font-bold">
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-clock"></i> Resterende tijd:</span>
                            <span class="font-black font-mono text-sm" x-text="format()"></span>
                        </div>
                        <div class="w-full bg-blue-200 dark:bg-blue-900 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-600 h-1.5 rounded-full animate-pulse" style="width: 70%"></div>
                        </div>
                    </div>
                    @endif

                    <!-- Claim Button if Ready -->
                    @if($isReady)
                    <form method="POST" action="{{ route('gangs.panden.claim-production', $owned->id) }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2 cursor-pointer animate-bounce">
                            <i class="fa-solid fa-sack-dollar"></i>
                            <span>Claim Buit: €{{ number_format($rewards['cash'], 0, ',', '.') }} & +{{ $rewards['respect'] }} Respect</span>
                        </button>
                    </form>
                    @elseif(! $isProducing && $isOfficer)
                    <!-- Start Production Button -->
                    <form method="POST" action="{{ route('gangs.panden.start-production', $owned->id) }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-play"></i>
                            <span>Start Productiecyclus ({{ $owned->production_duration_minutes }} min)</span>
                        </button>
                    </form>
                    @endif
                </div>
                @endif
            </div>

            <!-- Action Button: Buy or Owned Badge -->
            <div class="pt-2">
                @if($isOwned)
                    <div class="w-full py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-black text-xs text-center border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Eigendom van [{{ $gang->tag }}]</span>
                    </div>
                @elseif($isOfficer)
                    <form method="POST" action="{{ route('gangs.panden.buy', $key) }}">
                        @csrf
                        <button type="submit"
                                @if(! $canAfford) disabled @endif
                                onclick="return confirm('Weet je zeker dat je {{ $prop['name'] }} wilt kopen voor €{{ number_format($prop['price'], 0, ',', '.') }} uit de bendekas?')"
                                class="w-full py-3 px-4 rounded-xl {{ $canAfford ? 'bg-purple-600 hover:bg-purple-700 text-white cursor-pointer shadow-xs active:scale-95' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed font-bold' }} font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-building-circle-check"></i>
                            @if($canAfford)
                                <span>Kopen voor € {{ number_format($prop['price'], 0, ',', '.') }}</span>
                            @else
                                <span>Te weinig in kas (€ {{ number_format($gang->treasury, 0, ',', '.') }})</span>
                            @endif
                        </button>
                    </form>
                @else
                    <div class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs text-center">
                        Alleen bendebeheerders kunnen kopen
                    </div>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    <!-- ========================================== -->
    <!-- NPC RECRUITS SECTION                       -->
    <!-- ========================================== -->
    <div x-show="activeCategory === 'npcs' || activeCategory === 'all'" class="space-y-4 pt-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-users-gear text-purple-600"></i>
                    <span>NPC Bendeleden & Specialisten</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Huur ervaren criminelen in om je fabrieken te runnen, opbrengsten te maximaliseren en bende respect te verhogen.
                </p>
            </div>
            <div>
                <span class="px-3 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800 text-xs font-black text-purple-700 dark:text-purple-300">
                    Capaciteit: {{ $hiredNpcs->count() }} / {{ $totalNpcCap }} ingehuurd
                </span>
            </div>
        </div>

        @if($totalNpcCap <= 0)
        <!-- Warning when no luxury villa or penthouse is owned -->
        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg mt-0.5"></i>
            <div class="text-xs text-amber-900 dark:text-amber-200 space-y-1">
                <span class="font-black block">Geen woonruimte voor NPC bendeleden!</span>
                <p class="text-amber-800 dark:text-amber-300">
                    Om NPC bendeleden aan te nemen moet je bende eerst een <strong>Luxe Sky-Penthouse (3 plekken)</strong> of een <strong>Zwaarbeveiligd Landgoed & Villa (8 plekken)</strong> bezitten.
                </p>
            </div>
        </div>
        @endif

        <!-- NPC Catalog Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($npcCatalog as $npcKey => $npcDef)
            @php
                $hiredInstances = $hiredNpcs->where('npc_key', $npcKey);
                $isHired = $hiredInstances->count() > 0;
                $canAffordNpc = (int) $gang->treasury >= $npcDef['cost'];
                $hasFreeSlot = $availableNpcSlots > 0;
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between gap-5 relative overflow-hidden transition hover:shadow-md">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800 flex items-center justify-center text-2xl shrink-0">
                            <i class="fa-solid {{ $npcDef['icon'] }}"></i>
                        </div>
                        <div class="text-right">
                            <span class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400 block">
                                € {{ number_format($npcDef['cost'], 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Eenmalige wervingskosten</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">{{ $npcDef['role_title'] }}</span>
                        <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mt-0.5">{{ $npcDef['name'] }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                            {{ $npcDef['description'] }}
                        </p>
                    </div>

                    <!-- Benefit pill -->
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl p-3 flex items-center gap-2.5 text-xs font-black text-emerald-800 dark:text-emerald-300">
                        <i class="fa-solid fa-sparkles text-emerald-500"></i>
                        <span>{{ $npcDef['benefit_label'] }}</span>
                    </div>

                    @if($isHired)
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300 pt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800">
                            <i class="fa-solid fa-check text-emerald-500"></i> {{ $hiredInstances->count() }}x in dienst van bende
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Hire Button -->
                <div class="pt-2">
                    @if($isOfficer)
                        <form method="POST" action="{{ route('gangs.panden.hire-npc', $npcKey) }}">
                            @csrf
                            <button type="submit"
                                    @if(! $hasFreeSlot || ! $canAffordNpc) disabled @endif
                                    onclick="return confirm('Weet je zeker dat je {{ $npcDef['name'] }} wilt aannemen voor €{{ number_format($npcDef['cost'], 0, ',', '.') }} uit de bendekas?')"
                                    class="w-full py-3 px-4 rounded-xl {{ $hasFreeSlot && $canAffordNpc ? 'bg-purple-600 hover:bg-purple-700 text-white cursor-pointer shadow-xs active:scale-95' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed font-bold' }} font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-user-plus"></i>
                                @if(! $hasFreeSlot)
                                    <span>Geen Villa Ruimte</span>
                                @elseif(! $canAffordNpc)
                                    <span>Te weinig in kas</span>
                                @else
                                    <span>Aannemen (€ {{ number_format($npcDef['cost'], 0, ',', '.') }})</span>
                                @endif
                            </button>
                        </form>
                    @else
                        <div class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs text-center">
                            Alleen bendebeheerders kunnen werven
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
