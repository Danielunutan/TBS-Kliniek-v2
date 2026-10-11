@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="tbsDailyRewards()">

    <!-- Hero Header -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 sm:p-7 overflow-hidden relative">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    <i class="fa-solid fa-dharmachakra animate-spin-slow"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md border border-amber-200 mb-1">
                        Dagelijkse Beloningen & Streaks
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Rad van Fortuin & Login Kalender
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Draai elke dag gratis aan het rad voor flinke buit en bouw je 7-daagse login streak op voor de Directeurskluis!
                    </p>
                </div>
            </div>

            <!-- Stats & Spin status -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Beschikbare Draaien</span>
                    <span class="text-base font-black text-purple-700" x-text="availableSpins">
                        {{ $availableSpins }}
                    </span>
                    <span class="text-[9px] font-bold text-slate-400 block">
                        {{ $character->isVip() ? 'VIP: 2x per dag' : 'Normaal: 1x per dag' }}
                    </span>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Actieve Streak</span>
                    <span class="text-base font-black text-amber-600">
                        {{ $currentStreak }} {{ $currentStreak === 1 ? 'dag' : 'dagen' }}
                    </span>
                    <span class="text-[9px] font-bold text-slate-400 block">
                        Doel: Dag 7 Kluis
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Wheel on Left, Streak on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: The Wheel of Fortune (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-6 flex flex-col items-center relative overflow-hidden">
                <div class="w-full flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center text-xs font-bold border border-purple-200">
                            <i class="fa-solid fa-gift"></i>
                        </span>
                        <h2 class="text-sm sm:text-base font-black text-slate-900">
                            Rad van de TBS Kliniek
                        </h2>
                    </div>
                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-full"
                          :class="availableSpins > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'">
                        <span x-text="availableSpins > 0 ? 'Klaar om te draaien!' : 'Vandaag verbruikt'"></span>
                    </span>
                </div>

                <!-- Wheel Canvas / Interactive Display -->
                <div class="relative my-4 flex items-center justify-center">
                    <!-- Flapper / Arrow at top -->
                    <div class="absolute -top-3 z-30 flex flex-col items-center pointer-events-none drop-shadow-md">
                        <div class="w-7 h-8 bg-amber-500 clip-arrow shadow-md flex items-center justify-center text-white text-xs pt-1 font-black">
                            <i class="fa-solid fa-caret-down text-base text-amber-900"></i>
                        </div>
                    </div>

                    <!-- Canvas Wheel -->
                    <div class="p-2 rounded-full bg-slate-900 shadow-xl border-4 border-slate-800">
                        <canvas id="dailyWheelCanvas" width="340" height="340" class="max-w-full rounded-full transition-transform duration-75"></canvas>
                    </div>

                    <!-- Center Pin / Logo -->
                    <div class="absolute z-20 w-16 h-16 rounded-full bg-slate-900 border-4 border-amber-400 text-amber-400 flex flex-col items-center justify-center shadow-lg pointer-events-none">
                        <i class="fa-solid fa-crosshairs text-base"></i>
                        <span class="text-[8px] font-black uppercase tracking-wider text-white">TBS</span>
                    </div>
                </div>

                <!-- Prize announcement box -->
                <div x-show="lastPrizeMessage" x-transition class="w-full mt-4 p-4 rounded-xl bg-purple-50 border border-purple-200 text-center">
                    <div class="text-xs font-bold text-purple-600 uppercase tracking-wider mb-0.5">Laatste Winst</div>
                    <div class="text-base font-black text-purple-900" x-text="lastPrizeMessage"></div>
                </div>

                <!-- Action Controls -->
                <div class="w-full max-w-sm mt-6 flex flex-col gap-2">
                    <button type="button" 
                            @click="spinWheel()" 
                            :disabled="spinning || availableSpins <= 0"
                            class="w-full py-3.5 px-6 rounded-xl font-black text-sm uppercase tracking-wider text-white shadow-md transition transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="availableSpins > 0 ? 'bg-gradient-to-r from-amber-500 via-purple-600 to-indigo-600 hover:from-amber-600 hover:to-indigo-700' : 'bg-slate-400'">
                        <span x-show="!spinning && availableSpins > 0">
                            <i class="fa-solid fa-play mr-1"></i> Gratis Draaien!
                        </span>
                        <span x-show="spinning">
                            <i class="fa-solid fa-spinner animate-spin mr-1"></i> Het rad draait...
                        </span>
                        <span x-show="!spinning && availableSpins <= 0">
                            <i class="fa-solid fa-clock mr-1"></i> Morgen weer beschikbaar
                        </span>
                    </button>
                    @if(!$character->isVip())
                    <p class="text-[11px] text-center text-slate-400 font-semibold mt-1">
                        <i class="fa-solid fa-crown text-amber-500"></i> VIP-leden krijgen <strong>2x draaien per dag</strong>! 
                        <a href="{{ route('vip.index') }}" class="text-purple-600 font-bold hover:underline">Word VIP</a>
                    </p>
                    @endif
                </div>

                <!-- Possible Prizes Legend -->
                <div class="w-full mt-6 pt-5 border-t border-slate-100">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-3 text-center">
                        Mogelijke Prijzen op het Rad
                    </span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach($sectors as $sec)
                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-2 text-left">
                            <span class="w-6 h-6 rounded-lg text-white flex items-center justify-center text-[10px] shrink-0" style="background-color: {{ $sec['color'] }}">
                                <i class="fa-solid {{ $sec['icon'] }}"></i>
                            </span>
                            <span class="text-[11px] font-black text-slate-800 truncate">{{ $sec['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: 7-Day Login Streak Calendar (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold border border-amber-200">
                                <i class="fa-solid fa-calendar-days"></i>
                            </span>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-slate-900">
                                    7-Dagen Login Kalender
                                </h2>
                                <span class="text-[10px] font-bold text-slate-400 block">
                                    Log elke dag in om je streak te behouden!
                                </span>
                            </div>
                        </div>
                        <span class="text-xs font-black text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                            Dag {{ $nextStreakDay }} van 7
                        </span>
                    </div>

                    <!-- 7 Days Grid -->
                    <div class="space-y-2.5">
                        @foreach($schedule as $dayNumber => $dayData)
                        @php
                            $isPast = $dayNumber < $nextStreakDay;
                            $isCurrent = $dayNumber === $nextStreakDay;
                            $isFuture = $dayNumber > $nextStreakDay;
                            $isFinalDay = ($dayNumber === 7);
                        @endphp
                        <div class="p-3.5 rounded-xl border transition flex items-center justify-between gap-3
                            {{ $isCurrent ? 'bg-gradient-to-r from-amber-50 via-purple-50/50 to-white border-2 border-purple-500 shadow-xs' : '' }}
                            {{ $isPast ? 'bg-slate-50 border-slate-200 text-slate-500 opacity-80' : '' }}
                            {{ $isFuture && !$isFinalDay ? 'bg-white border-slate-200 text-slate-700' : '' }}
                            {{ $isFinalDay && !$isCurrent ? 'bg-gradient-to-r from-amber-50 to-amber-100/50 border-amber-300' : '' }}">
                            
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Day Badge / Icon -->
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs shrink-0
                                    {{ $isCurrent ? 'bg-purple-600 text-white shadow-xs' : '' }}
                                    {{ $isPast ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : '' }}
                                    {{ $isFuture && !$isFinalDay ? 'bg-slate-100 text-slate-500' : '' }}
                                    {{ $isFinalDay ? 'bg-amber-500 text-white shadow-xs' : '' }}">
                                    @if($isPast)
                                        <i class="fa-solid fa-check"></i>
                                    @elseif($isFinalDay)
                                        <i class="fa-solid fa-gift"></i>
                                    @else
                                        <span>D{{ $dayNumber }}</span>
                                    @endif
                                </div>
                                
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-black {{ $isCurrent ? 'text-purple-900' : 'text-slate-900' }}">
                                            Dag {{ $dayNumber }} {{ $isFinalDay ? '★ Jackpot' : '' }}
                                        </span>
                                        @if($isCurrent && $canClaimStreak)
                                            <span class="inline-flex items-center text-[9px] font-black px-1.5 py-0.2 rounded bg-emerald-500 text-white uppercase tracking-wider animate-pulse">
                                                Klaar!
                                            </span>
                                        @elseif($isPast)
                                            <span class="text-[10px] text-emerald-600 font-bold">
                                                Geclaimd
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] font-extrabold text-slate-600 truncate mt-0.5">
                                        {{ $dayData['label'] }}
                                    </p>
                                </div>
                            </div>

                            <!-- Reward icon / value -->
                            <div class="text-right shrink-0">
                                <span class="text-xs font-black {{ $isCurrent ? 'text-purple-700' : 'text-slate-800' }}">
                                    +{{ number_format($dayData['cash'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Claim Button -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    @if($canClaimStreak)
                    <form action="{{ route('daily.claim-streak') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-6 rounded-xl font-black text-sm uppercase tracking-wider text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 shadow-md transition transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-gift"></i> Claim Dag {{ $nextStreakDay }} Beloning!
                        </button>
                    </form>
                    @else
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <div class="text-xs font-black text-slate-700 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-check-circle text-emerald-500"></i>
                            <span>Vandaag al geclaimd!</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                            Kom morgen terug om Dag {{ ($nextStreakDay % 7) + 1 }} te incasseren.
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>

<script>
function tbsDailyRewards() {
    return {
        spinning: false,
        availableSpins: {{ $availableSpins }},
        lastPrizeMessage: '',
        sectors: @json($sectors),
        currentAngle: 0,
        
        init() {
            this.drawWheel();
        },

        drawWheel() {
            const canvas = document.getElementById('dailyWheelCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const numSectors = this.sectors.length;
            const arc = (2 * Math.PI) / numSectors;
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const radius = centerX - 10;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw sectors
            for (let i = 0; i < numSectors; i++) {
                const angle = this.currentAngle + (i * arc);
                ctx.beginPath();
                ctx.fillStyle = this.sectors[i].color;
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, angle, angle + arc, false);
                ctx.lineTo(centerX, centerY);
                ctx.fill();

                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 2;
                ctx.stroke();

                // Draw Text & icon
                ctx.save();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 12px sans-serif';
                ctx.shadowColor = 'rgba(0,0,0,0.5)';
                ctx.shadowBlur = 4;
                ctx.translate(centerX, centerY);
                ctx.rotate(angle + arc / 2);
                ctx.textAlign = 'right';
                ctx.fillText(this.sectors[i].label, radius - 20, 4);
                ctx.restore();
            }
        },

        spinWheel() {
            if (this.spinning || this.availableSpins <= 0) return;
            this.spinning = true;

            fetch("{{ route('daily.spin') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Fout bij draaien');
                    this.spinning = false;
                    return;
                }

                const winningIndex = data.winning_index;
                const numSectors = this.sectors.length;
                const arc = (2 * Math.PI) / numSectors;

                // The arrow is at the top (angle: -PI/2 or 3*PI/2)
                // Calculate target angle so winning sector centers at top
                const targetSectorMiddle = winningIndex * arc + (arc / 2);
                const desiredFinalOffset = (3 * Math.PI / 2) - targetSectorMiddle;

                // Do 5 to 7 full extra revolutions
                const extraRevolutions = 6 * (2 * Math.PI);
                const totalTargetAngle = extraRevolutions + (desiredFinalOffset % (2 * Math.PI));

                const startAngle = this.currentAngle % (2 * Math.PI);
                const duration = 4000; // 4 seconds
                const startTime = performance.now();

                const animate = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease out cubic
                    const easeOut = 1 - Math.pow(1 - progress, 3);

                    this.currentAngle = startAngle + (totalTargetAngle - startAngle) * easeOut;
                    this.drawWheel();

                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    } else {
                        this.spinning = false;
                        this.availableSpins = data.available_spins;
                        this.lastPrizeMessage = data.message;
                        
                        // Show celebration
                        setTimeout(() => {
                            alert(data.message);
                            window.location.reload();
                        }, 500);
                    }
                };

                requestAnimationFrame(animate);
            })
            .catch(err => {
                console.error(err);
                this.spinning = false;
                alert('Er is een netwerkfout opgetreden. Probeer het opnieuw.');
            });
        }
    };
}
</script>

<style>
.clip-arrow {
    clip-path: polygon(50% 100%, 0 0, 100% 0);
}
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 20s linear infinite;
}
</style>
@endsection
