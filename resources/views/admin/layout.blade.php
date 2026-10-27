@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Admin Top Nav Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs text-slate-900">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-xl text-purple-600 shadow-2xs shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900">Medewerkers Paneel</h1>
                        <span class="bg-purple-100 text-purple-800 border border-purple-200 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full">
                            STAFF
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Beheer van patiënten, VIP-toekenning, spelbalans, winkansen, kisten en de centrale kluis.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-700 transition flex items-center gap-1.5 border border-slate-200">
                    <i class="fa-solid fa-gamepad text-slate-500"></i> Terug naar Spel
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-2 pt-5 mt-5 border-t border-slate-100 text-xs font-bold">
            <a href="{{ route('admin.dashboard') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-chart-pie"></i> Overzicht
            </a>
            <a href="{{ route('admin.players') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.players') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-users"></i> Spelers & VIP
            </a>
            <a href="{{ route('admin.gameplay') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.gameplay') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-sliders"></i> Winkansen & Balans
            </a>
            <a href="{{ route('admin.chests') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.chests') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-box-open"></i> Kisten & Loot
            </a>
            <a href="{{ route('admin.vault') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.vault') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-vault"></i> Kluis & Codes
            </a>
            <a href="{{ route('admin.referrals') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.referrals*') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-user-plus"></i> Referrals & VIP Beloningen
            </a>
            <a href="{{ route('admin.news.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.news.*') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-newspaper"></i> Nieuws Artikelen
            </a>
            <a href="{{ route('admin.tickets.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.tickets.*') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-headset"></i> Support Tickets
                @php
                    $pendingAdminTicketsCount = \App\Models\SupportTicket::whereIn('status', ['open', 'in_behandeling'])->count();
                @endphp
                @if($pendingAdminTicketsCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ request()->routeIs('admin.tickets.*') ? 'bg-white text-purple-700' : 'bg-rose-500 text-white' }}">
                    {{ $pendingAdminTicketsCount }}
                </span>
                @endif
            </a>
            <a href="{{ route('admin.discord_shield.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.discord_shield.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-brands fa-discord text-indigo-400"></i> Discord Shield
            </a>
            <a href="{{ route('admin.moderation.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.moderation.*') ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-gavel text-rose-500 {{ request()->routeIs('admin.moderation.*') ? 'text-white' : '' }}"></i> Moderatie & Sancties
                @php
                    $pendingReportsBadge = \App\Models\Report::where('status', 'pending')->count();
                @endphp
                @if($pendingReportsBadge > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ request()->routeIs('admin.moderation.*') ? 'bg-white text-rose-700' : 'bg-rose-500 text-white animate-pulse' }}">
                    {{ $pendingReportsBadge }} nieuw
                </span>
                @endif
            </a>
            <a href="{{ route('admin.audit_log.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.audit_log.*') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-shield-virus {{ request()->routeIs('admin.audit_log.*') ? 'text-white' : 'text-purple-600' }}"></i> Audit Log &amp; Risico
                @php
                    $highRiskCountBadge = \App\Models\AuditLog::where('risk_level', 'hoog')->count();
                @endphp
                @if($highRiskCountBadge > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ request()->routeIs('admin.audit_log.*') ? 'bg-white text-rose-700' : 'bg-rose-600 text-white animate-pulse' }}">
                    {{ $highRiskCountBadge }} alert
                </span>
                @endif
            </a>
            @if(auth()->user()?->isOwner())
            <a href="{{ route('admin.mail.index') }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.mail.*') ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-crown {{ request()->routeIs('admin.mail.*') ? 'text-amber-200' : 'text-amber-500' }}"></i> Mailbox &amp; Brevo E-mail
            </a>
            @endif
        </div>
    </div>

    <!-- Admin Content Sub-View -->
    @yield('admin-content')

</div>
@endsection
