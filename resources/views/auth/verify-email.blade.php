<!DOCTYPE html>
<html lang="nl" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-mail Bevestigen | TBS Kliniek</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-100 antialiased font-sans flex items-center justify-center p-4 relative overflow-hidden bg-slate-950">

    <div class="absolute top-1/4 -left-20 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-purple-900/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10 space-y-6">

        <div class="text-center space-y-2">
            <div class="inline-flex items-center gap-3">
                <img src="{{ asset('assets/logo.png') }}" alt="TBS Kliniek Logo" class="w-14 h-14 object-contain drop-shadow">
                <div class="text-left">
                    <span class="block text-xl font-black tracking-tight text-white">TBS KLINIEK</span>
                    <span class="block text-[10px] font-extrabold uppercase tracking-widest text-purple-400">PATIËNTEN VERIFICATIE</span>
                </div>
            </div>
            <h1 class="text-xl font-extrabold text-white mt-4">Bevestig Je E-mailadres</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                Om toegang te krijgen tot de kliniek en het spel is e-mailbevestiging verplicht. We hebben zojuist een verificatielink verzonden naar:
            </p>
            <span class="inline-block bg-purple-900/40 text-purple-300 font-mono text-xs font-bold px-3 py-1 rounded-full border border-purple-500/30">
                {{ auth()->user()->email }}
            </span>
        </div>

        <div class="bg-slate-900/90 border border-purple-900/50 rounded-2xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl space-y-5">
            @if(session('status') == 'verification-link-sent')
                <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    <span>Er is een nieuwe verificatielink verstuurd naar je e-mailadres!</span>
                </div>
            @endif

            <p class="text-xs text-slate-300 text-center">
                Klik op de verificatieknop in je inbox om je account direct te activeren. Geen mail ontvangen of link verlopen?
            </p>

            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-purple-900/40 cursor-pointer">
                    <i class="fa-solid fa-rotate-right mr-1.5"></i> Verificatiemail Opnieuw Verzenden
                </button>
            </form>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                <a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition">
                    &laquo; Inloggen met ander account
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-rose-400 hover:text-rose-300 font-bold cursor-pointer">
                        Uitloggen
                    </button>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
