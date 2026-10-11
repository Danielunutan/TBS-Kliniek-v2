<!DOCTYPE html>
<html lang="nl" class="h-full bg-slate-50">
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
<body class="h-full antialiased font-sans flex items-center justify-center p-4 bg-slate-50">

    <div class="max-w-md w-full space-y-6">

        <!-- Logo & Branding -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center gap-3">
                <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek Logo" class="w-14 h-14 object-contain drop-shadow-md">
                <div class="text-left">
                    <span class="block text-xl font-black tracking-tight text-slate-900">TBS KLINIEK</span>
                    <span class="block text-[10px] font-extrabold uppercase tracking-widest text-purple-600">PATIËNTEN VERIFICATIE</span>
                </div>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-4">Bevestig Je E-mailadres</h1>
            <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto">
                Om toegang te krijgen tot de kliniek en het spel is e-mailbevestiging verplicht. We hebben zojuist een verificatielink verzonden naar:
            </p>
            <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 font-mono text-xs font-bold px-3.5 py-1.5 rounded-lg border border-indigo-200">
                <i class="fa-solid fa-at text-indigo-400 text-[10px]"></i>
                {{ auth()->user()->email }}
            </span>
        </div>

        <!-- Card Container -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm space-y-5">
            @if(session('status') == 'verification-link-sent')
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>Er is een nieuwe verificatielink verstuurd naar je e-mailadres!</span>
                </div>
            @endif

            <div class="text-center space-y-1">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mx-auto border border-amber-100">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <p class="text-xs text-slate-500 pt-2">
                    Klik op de verificatieknop in je inbox om je account direct te activeren. Geen mail ontvangen of link verlopen?
                </p>
            </div>

            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                    <span>Verificatiemail Opnieuw Verzenden</span>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-900 font-semibold transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Ander account
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-rose-600 hover:text-rose-700 font-bold cursor-pointer transition">
                        <i class="fa-solid fa-right-from-bracket text-[10px] mr-0.5"></i> Uitloggen
                    </button>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
