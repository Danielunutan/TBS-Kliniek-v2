@extends('layouts.app')

@section('title', 'E-mail Verifiëren met Code • TBS Kliniek')

@section('content')
<div class="max-w-xl mx-auto my-6 sm:my-10 space-y-6">

    <!-- Top Hero Header -->
    <div class="text-center space-y-3">
        <div class="inline-flex items-center gap-3">
            <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek Logo" class="w-14 h-14 object-contain drop-shadow-md">
            <div class="text-left">
                <span class="block text-2xl font-black tracking-tight text-slate-900 dark:text-white font-['Outfit']">TBS KLINIEK</span>
                <span class="block text-[10px] font-extrabold uppercase tracking-widest text-purple-600 dark:text-purple-400">PATIËNTEN VERIFICATIE</span>
            </div>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-3 font-['Outfit']">
            Voer je Verificatiecode in
        </h1>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed max-w-md mx-auto">
            We hebben een beveiligde 6-cijferige verificatiecode gestuurd naar jouw e-mailadres:
        </p>
        <div>
            <span class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 font-mono text-xs sm:text-sm font-bold px-4 py-1.5 rounded-full border border-purple-200 dark:border-purple-800 shadow-xs">
                <i class="fa-solid fa-envelope text-purple-500 text-xs"></i>
                {{ auth()->user()->email ?? $user->email }}
            </span>
        </div>
    </div>

    <!-- Main Card Container (Default Light, Dark Mode Compatible) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-purple-900/40 rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-purple-950/50 space-y-6">

        @if(session('status') === 'verification-code-sent' || session('status') === 'verification-link-sent')
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-500/50 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-semibold flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base shrink-0"></i>
                <span>Er is zojuist een nieuwe 6-cijferige verificatiecode verzonden! Deze code is 30 minuten geldig.</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-500/50 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-semibold flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-base shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->has('code'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-500/50 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-semibold flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-base shrink-0"></i>
                <span>{{ $errors->first('code') }}</span>
            </div>
        @endif

        <!-- Code Entry Form -->
        <form action="{{ route('verification.code') }}" method="POST" class="space-y-5">
            @csrf

            <div class="space-y-2 text-center">
                <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-purple-300">
                    6-Cijferige Verificatiecode
                </label>
                <div class="relative max-w-sm mx-auto">
                    <input type="text"
                           id="code"
                           name="code"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           placeholder="123456"
                           required
                           autofocus
                           value="{{ old('code') }}"
                           class="w-full text-center font-mono text-3xl sm:text-4xl tracking-[0.4em] sm:tracking-[0.5em] font-black py-4 px-3 rounded-2xl bg-slate-50 dark:!bg-slate-950 border-2 border-purple-300 dark:!border-purple-600 text-purple-950 dark:!text-amber-400 placeholder:text-slate-400 dark:placeholder:text-slate-700 focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:!bg-slate-900 focus:ring-4 focus:ring-purple-500/20 transition shadow-inner">
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 pt-1">
                    Vul de 6 cijfers uit je e-mail in.
                </p>
            </div>

            <!-- Expiration Countdown & Info -->
            <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-xs">
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold">
                    <i class="fa-regular fa-clock"></i>
                    <span>30 minuten geldig</span>
                </div>
                @if(isset($expiresAt) && $expiresAt)
                    <div id="countdown-box" class="text-slate-600 dark:text-slate-300 font-mono font-bold" data-expires="{{ $expiresAt->timestamp }}">
                        Verloopt over: <span id="countdown-timer" class="text-purple-600 dark:text-purple-300 font-black">--:--</span>
                    </div>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full py-4 rounded-2xl bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600 hover:from-purple-500 hover:via-indigo-500 hover:to-purple-500 text-white font-extrabold text-sm uppercase tracking-wider transition duration-200 shadow-md shadow-purple-600/30 cursor-pointer flex items-center justify-center gap-2.5 transform active:scale-[0.98]">
                <i class="fa-solid fa-check-double text-amber-300 text-base"></i>
                <span>Verifieer Code &amp; Start Spel</span>
            </button>
        </form>

        <!-- Resend Form -->
        <div class="pt-4 border-t border-slate-200 dark:border-slate-800/80 text-center space-y-3">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                Geen e-mail ontvangen of is de 30 minuten verstreken?
            </p>
            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 text-xs font-bold text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 transition py-2 px-4 rounded-xl bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 dark:hover:bg-purple-950/80 border border-purple-200 dark:border-purple-800 cursor-pointer">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                    <span>Nieuwe 6-cijferige code aanvragen</span>
                </button>
            </form>
        </div>

        <!-- Helpful Hints -->
        <div class="bg-purple-50/50 dark:bg-slate-950/40 rounded-2xl p-4 border border-purple-100 dark:border-slate-800/60 space-y-2 text-xs text-slate-600 dark:text-slate-400">
            <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400"></i>
                <span>Tips bij het ontvangen van de e-mail:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400 pl-1">
                <li>Controleer ook je <strong class="text-slate-800 dark:text-slate-200">Spam of Ongewenste Postvak</strong>.</li>
                <li>De mail bevat direct jouw code en een knop om direct terug te keren naar deze pagina.</li>
                <li>Code verlopen? Klik hierboven om direct een verse code te ontvangen.</li>
            </ul>
        </div>

        <!-- Bottom Actions -->
        <div class="pt-3 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between text-xs">
            <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white font-semibold transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Ander account
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 font-bold cursor-pointer transition inline-flex items-center gap-1">
                    <i class="fa-solid fa-right-from-bracket text-[10px]"></i> Uitloggen
                </button>
            </form>
        </div>

    </div>

</div>

<!-- Live 30-Minute Expiration Countdown Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countdownBox = document.getElementById('countdown-box');
        const countdownTimer = document.getElementById('countdown-timer');
        if (!countdownBox || !countdownTimer) return;

        const expiresTimestamp = parseInt(countdownBox.dataset.expires, 10);
        if (!expiresTimestamp) return;

        function updateTimer() {
            const now = Math.floor(Date.now() / 1000);
            const diff = expiresTimestamp - now;

            if (diff <= 0) {
                countdownTimer.innerText = 'Verlopen';
                countdownTimer.classList.remove('text-purple-600', 'dark:text-purple-300');
                countdownTimer.classList.add('text-rose-600', 'dark:text-rose-400');
                return;
            }

            const minutes = Math.floor(diff / 60);
            const seconds = diff % 60;
            countdownTimer.innerText = 
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }

        updateTimer();
        setInterval(updateTimer, 1000);

        // Auto format input to numeric only
        const codeInput = document.getElementById('code');
        if (codeInput) {
            codeInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });
        }
    });
</script>
@endsection
