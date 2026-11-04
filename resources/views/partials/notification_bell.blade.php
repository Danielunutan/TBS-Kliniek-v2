<a href="{{ route('notifications.index') }}"
   class="relative w-8 h-8 rounded-xl bg-slate-100 hover:bg-purple-100 dark:bg-slate-800 dark:hover:bg-purple-950/60 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-purple-600 dark:hover:text-purple-300 transition shrink-0"
   title="Bekijk meldingen"
   aria-label="Meldingen{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' ongelezen' : '' }}"
   :aria-label="stats.unreadNotifications > 0 ? 'Meldingen, ' + stats.unreadNotifications + ' ongelezen' : 'Meldingen'">
    <i class="fa-solid fa-bell text-xs" aria-hidden="true"></i>
    <span x-show="stats.unreadNotifications > 0" @if(!$unreadNotificationCount) style="display: none" @endif class="absolute -top-1.5 -right-1.5 min-w-4 h-4 px-1 rounded-full bg-rose-600 text-white text-[9px] font-black leading-4 text-center ring-2 ring-white dark:ring-slate-900" x-text="stats.unreadNotifications > 99 ? '99+' : stats.unreadNotifications">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
</a>
