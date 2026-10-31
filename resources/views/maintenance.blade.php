<!DOCTYPE html>
<html lang="nl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBS Kliniek - Tijdelijk in Onderhoud</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        @keyframes pulse-slow {
            0%, 100% { transform: scale(1); opacity: 0.25; }
            50% { transform: scale(1.08); opacity: 0.4; }
        }
        .glow-circle {
            animation: pulse-slow 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-[#080512] text-slate-100 min-h-screen flex flex-col justify-between font-sans selection:bg-purple-600 selection:text-white relative overflow-x-hidden">

    <!-- Glowing Background Aura Gradients -->
    <div class="fixed top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[650px] h-[650px] bg-gradient-to-tr from-purple-700/20 via-indigo-600/20 to-transparent rounded-full blur-[120px] pointer-events-none glow-circle"></div>
    <div class="fixed -bottom-32 -right-32 w-96 h-96 bg-purple-900/15 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="fixed -top-32 -left-32 w-96 h-96 bg-indigo-950/20 rounded-full blur-[100px] pointer-events-none"></div>

    <!-- Top Simple Bar -->
    <header class="relative z-10 w-full max-w-5xl mx-auto px-6 py-6 flex items-center justify-between dark:bg-transparent">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-purple-600/30">
                <i class="fa-solid fa-hospital"></i>
            </div>
            <div>
                <span class="text-base font-black tracking-wider uppercase text-white font-heading">
                    TBS <span class="text-purple-400">KLINIEK</span>
                </span>
                <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 -mt-1">
                    FPC De Balk
                </span>
            </div>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-black">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            <span>Onderhoudsmodus Actief</span>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="relative z-10 w-full max-w-3xl mx-auto px-4 sm:px-6 py-8 my-auto">
        
        <div class="bg-gradient-to-b from-slate-900/90 via-slate-900/80 to-[#120a26]/90 backdrop-blur-xl border border-purple-500/30 rounded-3xl p-6 sm:p-10 shadow-2xl shadow-purple-950/50 text-center space-y-8">
            
            <!-- Animated Padlock Icon with Rings -->
            <div class="relative w-24 h-24 mx-auto flex items-center justify-center">
                <div class="absolute inset-0 bg-purple-600/20 rounded-full animate-ping pointer-events-none"></div>
                <div class="absolute inset-1 bg-gradient-to-br from-purple-600/40 to-indigo-600/40 rounded-full blur-md"></div>
                <div class="relative w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-600 via-indigo-600 to-slate-900 border border-purple-400/40 flex items-center justify-center text-3xl text-white shadow-xl">
                    <i class="fa-solid fa-lock text-purple-200"></i>
                </div>
            </div>

            <!-- Headings -->
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/15 border border-purple-400/30 text-purple-300 text-xs font-black uppercase tracking-wider">
                    <i class="fa-solid fa-wrench text-amber-400"></i>
                    <span>Grote Seizoensreset &amp; Balans Update</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-white font-heading tracking-tight leading-tight">
                    De Kliniekdeuren Zijn Even Gesloten!
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto leading-relaxed">
                    We voeren op dit moment een grote seizoensreset en update door aan alle afdelingen. Hierdoor krijgt iedereen straks weer een schone start en gelijke kansen op de Respectlijst.
                </p>
            </div>

            <!-- Expected Return Time Card & Live Countdown -->
            <div class="bg-purple-950/40 border border-purple-500/30 rounded-2xl p-5 sm:p-6 space-y-4 shadow-inner">
                <div class="flex items-center justify-center gap-2 text-xs font-black text-purple-300 uppercase tracking-widest">
                    <i class="fa-solid fa-clock text-amber-400"></i>
                    <span>Verwachte Heropening</span>
                </div>
                
                <div class="text-3xl sm:text-4xl font-black text-amber-400 font-heading tracking-tight">
                    Rond {{ $targetTime ?? '22:00' }} Uur Vanavond
                </div>

                <!-- Live Ticking Countdown to 22:00 -->
                <div id="countdown-box" class="grid grid-cols-3 gap-3 max-w-sm mx-auto pt-2">
                    <div class="bg-black/40 border border-purple-500/20 rounded-xl p-3">
                        <div id="count-hours" class="text-xl sm:text-2xl font-black text-white font-mono">01</div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Uur</div>
                    </div>
                    <div class="bg-black/40 border border-purple-500/20 rounded-xl p-3">
                        <div id="count-minutes" class="text-xl sm:text-2xl font-black text-white font-mono">30</div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Minuten</div>
                    </div>
                    <div class="bg-black/40 border border-purple-500/20 rounded-xl p-3">
                        <div id="count-seconds" class="text-xl sm:text-2xl font-black text-purple-400 font-mono">00</div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Seconden</div>
                    </div>
                </div>
            </div>

            <!-- Progress Steps Tracker -->
            <div class="space-y-2 text-left max-w-lg mx-auto bg-slate-950/50 p-4 rounded-2xl border border-slate-800">
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-800/80 flex items-center justify-between">
                    <span>Voortgang Onderhoud</span>
                    <span class="text-purple-400 font-bold">Stap 2 van 3</span>
                </div>

                <div class="space-y-2.5 pt-1 text-xs">
                    <div class="flex items-center gap-3 text-emerald-400 font-bold">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                        <span class="text-slate-200">Spelersdata &amp; veilige back-up veiliggesteld</span>
                    </div>
                    <div class="flex items-center gap-3 text-amber-400 font-bold">
                        <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                        <span class="text-slate-200">Nieuwe spelbalans en reset klaargezet</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-500 font-semibold">
                        <i class="fa-regular fa-circle text-sm"></i>
                        <span>Kliniekpoorten openen voor alle patiënten (om 22.00 uur)</span>
                    </div>
                </div>
            </div>

            <!-- Actions (Discord & Refresh) -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="https://discord.gg/V7bXG6e9aQ" target="_blank" rel="noopener noreferrer"
                   class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#5865F2] hover:bg-[#4752c4] active:scale-95 text-white font-black text-xs transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20 cursor-pointer">
                    <i class="fa-brands fa-discord text-base"></i>
                    <span>Volg Live Updates op Discord</span>
                </a>

                <button type="button" onclick="window.location.reload()"
                        class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-200 hover:text-white font-extrabold text-xs transition flex items-center justify-center gap-2 border border-slate-700 cursor-pointer">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span>Ververs Pagina</span>
                </button>
            </div>

        </div>

    </main>

    <!-- Footer with Secret Bypass Link for Daniel -->
    <footer class="relative z-10 w-full max-w-5xl mx-auto px-6 py-6 text-center text-xs text-slate-500 dark:bg-transparent">
        <div>
            &copy; {{ date('Y') }} TBS Kliniek (FPC De Balk). Alle rechten voorbehouden.
        </div>
        <div class="mt-1 text-[11px] text-slate-600">
            <a href="/?bypass=daniel" class="hover:text-purple-400 transition underline underline-offset-4 opacity-50 hover:opacity-100">
                Beheerder Toegang
            </a>
        </div>
    </footer>

    <!-- Countdown Javascript -->
    <script>
        function updateMaintenanceCountdown() {
            const now = new Date();
            const target = new Date();
            target.setHours(22, 0, 0, 0);

            let diffMs = target.getTime() - now.getTime();
            if (diffMs <= 0) {
                // Past 22:00, show 00:00:00 or auto-reload check
                document.getElementById('count-hours').innerText = '00';
                document.getElementById('count-minutes').innerText = '00';
                document.getElementById('count-seconds').innerText = '00';
                return;
            }

            const hours = Math.floor(diffMs / (1000 * 60 * 60));
            diffMs %= (1000 * 60 * 60);
            const minutes = Math.floor(diffMs / (1000 * 60));
            diffMs %= (1000 * 60);
            const seconds = Math.floor(diffMs / 1000);

            document.getElementById('count-hours').innerText = String(hours).padStart(2, '0');
            document.getElementById('count-minutes').innerText = String(minutes).padStart(2, '0');
            document.getElementById('count-seconds').innerText = String(seconds).padStart(2, '0');
        }

        updateMaintenanceCountdown();
        setInterval(updateMaintenanceCountdown, 1000);
    </script>

</body>
</html>
