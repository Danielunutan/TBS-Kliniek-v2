@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Admin Top Nav Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs text-slate-900 dark:text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-100 dark:border-purple-800/70 flex items-center justify-center text-xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">Medewerkers Paneel</h1>
                        <span class="bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full">
                            STAFF
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Beheer van patiënten, VIP-toekenning, spelbalans, winkansen, kisten en de centrale kluis.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-gamepad text-slate-500 dark:text-slate-400"></i> Terug naar Spel
                </a>
            </div>
        </div>

        <!-- Navigation Tabs (Collapsible on Mobile for Clean UX, Always Visible on sm+) -->
        <div x-data="{ adminNavOpen: false }" class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800">
            <button type="button" 
                    @click="adminNavOpen = !adminNavOpen" 
                    class="sm:hidden w-full mb-2.5 px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200/80 dark:border-slate-700 font-black text-xs flex items-center justify-between cursor-pointer active:scale-98 transition">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-purple-600 dark:text-purple-400"></i>
                    <span>Admin Secties &amp; Navigatie</span>
                </span>
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="adminNavOpen ? 'rotate-180' : ''"></i>
            </button>
            <div :class="adminNavOpen ? 'flex' : 'hidden sm:flex'" class="flex-wrap items-center gap-2 text-xs font-bold">
            <a href="{{ route('admin.dashboard') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-chart-pie"></i> Overzicht
            </a>
            <a href="{{ route('admin.players') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.players') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-users"></i> Spelers & VIP
            </a>
            <a href="{{ route('admin.gameplay') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.gameplay') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-sliders"></i> Winkansen & Balans
            </a>
            <a href="{{ route('admin.chests') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.chests') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-box-open"></i> Kisten & Loot
            </a>
            <a href="{{ route('admin.vault') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.vault') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-vault"></i> Kluis & Codes
            </a>
            <a href="{{ route('admin.referrals') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.referrals*') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-user-plus"></i> Referrals & VIP Beloningen
            </a>
            <a href="{{ route('admin.buymeacoffee.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.buymeacoffee*') ? 'bg-amber-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-mug-hot {{ request()->routeIs('admin.buymeacoffee*') ? 'text-white' : 'text-amber-500' }}"></i> Buy Me a Coffee
                @php
                    $unmatchedBmcCount = \Illuminate\Support\Facades\Schema::hasTable('buymeacoffee_orders') ? \App\Models\BuyMeACoffeeOrder::where('status', 'unmatched')->count() : 0;
                @endphp
                @if($unmatchedBmcCount > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.buymeacoffee*') ? 'bg-white dark:bg-white/20 text-amber-700 dark:text-white' : 'bg-amber-500 text-white animate-pulse' }}">
                    {{ $unmatchedBmcCount }} nieuw
                </span>
                @endif
            </a>
            <a href="{{ route('admin.news.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.news.*') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-newspaper"></i> Nieuws Artikelen
            </a>
            <a href="{{ route('admin.tickets.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.tickets.*') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-headset"></i> Support Tickets
                @php
                    $pendingAdminTicketsCount = \Illuminate\Support\Facades\Schema::hasTable('support_tickets') ? \App\Models\SupportTicket::whereIn('status', ['open', 'in_behandeling'])->count() : 0;
                @endphp
                @if($pendingAdminTicketsCount > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.tickets.*') ? 'bg-white dark:bg-white/20 text-purple-700 dark:text-white' : 'bg-rose-500 text-white' }}">
                    {{ $pendingAdminTicketsCount }}
                </span>
                @endif
            </a>
            <a href="{{ route('admin.discord_shield.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.discord_shield.*') ? 'bg-indigo-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-brands fa-discord text-indigo-400"></i> Discord Shield
            </a>
            <a href="{{ route('admin.moderation.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.moderation.*') ? 'bg-rose-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-gavel {{ request()->routeIs('admin.moderation.*') ? 'text-white' : 'text-rose-500' }}"></i> Moderatie & Sancties
                @php
                    $pendingReportsBadge = \Illuminate\Support\Facades\Schema::hasTable('reports') ? \App\Models\Report::where('status', 'pending')->count() : 0;
                @endphp
                @if($pendingReportsBadge > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.moderation.*') ? 'bg-white dark:bg-white/20 text-rose-700 dark:text-white' : 'bg-rose-500 text-white animate-pulse' }}">
                    {{ $pendingReportsBadge }} nieuw
                </span>
                @endif
            </a>
            <a href="{{ route('admin.audit_log.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.audit_log.*') ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-shield-virus {{ request()->routeIs('admin.audit_log.*') ? 'text-white' : 'text-purple-600' }}"></i> Audit Log &amp; Risico
                @php
                    $highRiskCountBadge = \Illuminate\Support\Facades\Schema::hasTable('audit_logs') ? \App\Models\AuditLog::where('risk_level', 'hoog')->count() : 0;
                @endphp
                @if($highRiskCountBadge > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.audit_log.*') ? 'bg-white dark:bg-white/20 text-rose-700 dark:text-white' : 'bg-rose-600 text-white animate-pulse' }}">
                    {{ $highRiskCountBadge }} alert
                </span>
                @endif
            </a>
            @if(auth()->user()?->isOwner())
            <a href="{{ route('admin.mail.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.mail.*') ? 'bg-amber-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-crown {{ request()->routeIs('admin.mail.*') ? 'text-amber-200' : 'text-amber-500' }}"></i> Mailbox &amp; Brevo E-mail
            </a>
            @endif
            </div>
        </div>
    </div>

    <!-- Admin Content Sub-View -->
    @yield('admin-content')

</div>
@endsection
