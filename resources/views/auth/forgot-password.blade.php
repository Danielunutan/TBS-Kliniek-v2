<!DOCTYPE html>
<html lang="nl" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wachtwoord Vergeten | TBS Kliniek</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-100 antialiased font-sans flex items-center justify-center p-4 relative overflow-hidden bg-slate-950">

    <!-- Ambient Purple Lights -->
    <div class="absolute top-1/4 -left-20 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-purple-900/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10 space-y-6">

        <!-- Logo & Branding -->
        <div class="text-center space-y-2">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('assets/logo.svg') }}" alt="TBS Kliniek Logo" class="w-12 h-12 object-contain drop-shadow">
                <div class="text-left">
                    <span class="block text-xl font-black tracking-tight text-white">TBS KLINIEK</span>
                    <span class="block text-[10px] font-extrabold uppercase tracking-widest text-purple-400">HERSTEL & BEVEILIGING</span>
                </div>
            </a>
            <h1 class="text-xl font-extrabold text-white mt-4">Wachtwoord Vergeten?</h1>
            <p class="text-xs text-slate-400">
                Geen paniek. Vul het e-mailadres van je account in en we sturen je via Brevo Mail direct een veilige link om je wachtwoord opnieuw in te stellen.
            </p>
        </div>

        <!-- Card Container -->
        <div class="bg-slate-900/90 border border-purple-900/50 rounded-2xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl space-y-5">
            @if(session('status'))
                <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-bold space-y-1">
                    @foreach($errors->all() as $err)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-extrabold uppercase tracking-wider text-slate-300 block mb-1.5">
                        Jouw E-mailadres
                    </label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="bijv. patient@tbskliniek.nl"
                               class="w-full bg-slate-950/80 border border-slate-700 focus:border-purple-500 rounded-xl px-4 py-2.5 text-xs font-bold text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-purple-500">
                        <i class="fa-solid fa-envelope absolute right-3.5 top-3 text-slate-500 text-xs"></i>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-purple-900/40 cursor-pointer">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> Verstuur Reset Link
                </button>
            </form>

            <div class="pt-3 border-t border-slate-800 text-center">
                <a href="{{ route('login') }}" class="text-xs font-bold text-purple-400 hover:text-purple-300 transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Terug naar Inloggen
                </a>
            </div>
        </div>

    </div>

</body>
</html>
