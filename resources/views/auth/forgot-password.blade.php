<!DOCTYPE html>
<html lang="nl" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wachtwoord Vergeten | TBS Kliniek</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        (function() {
            const saved = localStorage.getItem('tbs_theme');
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans flex items-center justify-center p-4 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100">

    <div class="max-w-md w-full space-y-6">

        <!-- Logo & Branding -->
        <div class="text-center space-y-3">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek Logo" class="w-14 h-14 object-contain drop-shadow-md">
                <div class="text-left">
                    <span class="block text-xl font-black tracking-tight text-slate-900 dark:text-white">TBS KLINIEK</span>
                    <span class="block text-[10px] font-extrabold uppercase tracking-widest text-purple-600 dark:text-purple-400">HERSTEL & BEVEILIGING</span>
                </div>
            </a>
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white mt-4">Wachtwoord Vergeten?</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                Geen paniek. Vul het e-mailadres van je account in en we sturen je direct een veilige link om je wachtwoord opnieuw in te stellen.
            </p>
        </div>

        <!-- Card Container -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs space-y-5">
            @if(session('status'))
                <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-700 dark:text-rose-300 text-xs font-bold space-y-1">
                    @foreach($errors->all() as $err)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 block mb-1.5">
                        Jouw E-mailadres
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <i class="fa-solid fa-envelope text-xs"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="bijv. patient@tbskliniek.nl"
                               class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Verstuur Reset Link</span>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-center">
                <a href="{{ route('login') }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Terug naar Inloggen
                </a>
            </div>
        </div>

    </div>

</body>
</html>
