@extends('layouts.app')

@section('title', 'Meldingen | TBS Kliniek')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div class="rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-bell" aria-hidden="true"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">Meldingen</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Blijf op de hoogte van wat er in de kliniek gebeurt.</p>
                <p class="text-xs font-bold text-purple-700 dark:text-purple-300 mt-2">{{ $unreadCount }} ongelezen</p>
            </div>
        </div>
        @if($unreadCount > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" data-no-async>
                @csrf
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black transition cursor-pointer">
                    <i class="fa-solid fa-check-double" aria-hidden="true"></i> Alles als gelezen markeren
                </button>
            </form>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden">
        @forelse($notifications as $notification)
            <article class="flex gap-3 p-4 sm:p-5 border-b last:border-b-0 border-slate-100 dark:border-slate-800 {{ $notification->is_read ? '' : 'bg-purple-50/60 dark:bg-purple-950/20' }}">
                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-purple-600 dark:text-purple-300 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bell text-xs" aria-hidden="true"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <h2 class="font-black text-sm text-slate-900 dark:text-white break-words">{{ $notification->title ?: 'Melding' }}</h2>
                        @unless($notification->is_read)
                            <span class="rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 px-2 py-0.5 text-[10px] font-black">Nieuw</span>
                        @endunless
                    </div>
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line break-words">{{ $notification->message }}</p>
                    @if($notification->created_at)
                        <time datetime="{{ $notification->created_at->toIso8601String() }}" class="block text-xs text-slate-500 dark:text-slate-400 mt-2">{{ $notification->created_at->format('d-m-Y H:i') }}</time>
                    @endif
                </div>
            </article>
        @empty
            <div class="px-5 py-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-500 flex items-center justify-center mx-auto mb-3"><i class="fa-regular fa-bell" aria-hidden="true"></i></div>
                <h2 class="font-black text-sm text-slate-900 dark:text-white">Je hebt nog geen meldingen</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Nieuwe gebeurtenissen in de kliniek verschijnen hier.</p>
            </div>
        @endforelse
        @if($notifications->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>
@endsection
