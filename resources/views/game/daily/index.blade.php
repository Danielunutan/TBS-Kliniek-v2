@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="tbsDailyRewards()">

    <!-- Header Card (Clean Solid White TBS Styling) -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 sm:p-7 relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-900 text-amber-400 flex items-center justify-center text-2xl shadow-xs border border-slate-800 shrink-0">
                    <i class="fa-solid fa-dharmachakra"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md border border-amber-200 mb-1">
                        Dagelijkse Beloningen & 22:00 Inlog Bonus
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Rad van Fortuin & Inlog Bonus Kalender
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Draai elke dag gratis aan het rad voor kluiscodes, maskers, koffers en contanten!
                    </p>
                </div>
            </div>

            <!-- Stats & Spin status -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-center min-w-[110px]">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Draaien Vandaag</span>
                    <span class="text-base font-black text-purple-700" x-text="availableSpins">
                        {{ $availableSpins }}
                    </span>
                    <span class="text-[9px] font-bold text-slate-400 block">
                        {{ $character->isVip() ? 'VIP: 2x per dag' : 'Normaal: 1x per dag' }}
                    </span>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-center min-w-[110px]">
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
                          :class="availableSpins > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'">
                        <span x-text="availableSpins > 0 ? 'Klaar om te draaien!' : 'Vandaag verbruikt'"></span>
                    </span>
                </div>

                <!-- Wheel Canvas / Interactive Display -->
                <div class="relative my-3 flex items-center justify-center">
                    <!-- Flapper / Arrow at top -->
                    <div class="absolute -top-3 z-30 flex flex-col items-center pointer-events-none drop-shadow-md">
                        <div class="w-8 h-9 bg-amber-500 clip-arrow shadow-md flex items-center justify-center text-white text-xs pt-0.5 font-black border-t-2 border-amber-300">
                            <i class="fa-solid fa-caret-down text-lg text-amber-950"></i>
                        </div>
                    </div>

                    <!-- Canvas Wheel Outer Container -->
                    <div class="p-2 sm:p-2.5 rounded-full bg-slate-950 shadow-xl border-4 border-slate-800">
                        <canvas id="dailyWheelCanvas" width="380" height="380" class="max-w-[310px] sm:max-w-[350px] aspect-square rounded-full transition-transform duration-75"></canvas>
                    </div>

                    <!-- Center Pin / Logo -->
                    <div class="absolute z-20 w-16 h-16 rounded-full bg-slate-950 border-4 border-amber-400 text-amber-400 flex flex-col items-center justify-center shadow-lg pointer-events-none">
                        <i class="fa-solid fa-crosshairs text-base"></i>
                        <span class="text-[8px] font-black uppercase tracking-wider text-white">TBS</span>
                    </div>
                </div>

                <!-- Prize announcement box -->
                <div x-show="lastPrizeMessage" x-transition class="w-full mt-4 p-4 rounded-xl bg-purple-50 border border-purple-200 text-center">
                    <div class="text-xs font-bold text-purple-600 uppercase tracking-wider mb-0.5">Laatste Winst</div>
                    <div class="text-base font-black text-purple-900" x-text="lastPrizeMessage"></div>
                </div>

                <!-- Action Controls (Solid button, NO gradients) -->
                <div class="w-full max-w-sm mt-5 flex flex-col gap-2">
                    <button type="button" 
                            @click="spinWheel()" 
                            :disabled="spinning || availableSpins <= 0"
                            class="w-full py-3.5 px-6 rounded-xl font-black text-sm uppercase tracking-wider text-white shadow-xs transition active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="availableSpins > 0 ? 'bg-purple-700 hover:bg-purple-800' : 'bg-slate-400'">
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
                            <span class="w-7 h-7 rounded-lg text-white flex items-center justify-center text-xs shrink-0" style="background-color: {{ $sec['color'] }}">
                                <i class="fa-solid {{ $sec['icon'] }}"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="text-[11px] font-black text-slate-800 block truncate">{{ $sec['label'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: 7-Day Login Streak Calendar (5 cols) -->
        <div class="lg:col-span-5 space-y-4"
             x-data="{
                 active: {{ $isLoginBonusActive ? 'true' : 'false' }},
                 claimed: {{ $hasClaimedStreakToday ? 'true' : 'false' }},
                 canClaim: {{ $canClaimStreak ? 'true' : 'false' }},
                 secUntilStart: {{ $secondsUntilLoginBonus }},
                 secRemaining: {{ $secondsRemainingInLoginBonus }},
                 formatTimer(s) {
                     if (s <= 0) return '00:00';
                     const h = Math.floor(s / 3600);
                     const m = Math.floor((s % 3600) / 60);
                     const sec = s % 60;
                     if (h > 0) {
                         return `${h}u ${m < 10 ? '0' : ''}${m}m`;
                     }
                     return `${m < 10 ? '0' : ''}${m}:${sec < 10 ? '0' : ''}${sec}`;
                 },
                 init() {
                     setInterval(() => {
                         if (this.active) {
                             if (this.secRemaining > 0) {
                                 this.secRemaining--;
                             } else {
                                 this.active = false;
                                 this.canClaim = false;
                                 this.secUntilStart = 86400 - 1800;
                             }
                         } else {
                             if (this.secUntilStart > 0) {
                                 this.secUntilStart--;
                             } else {
                                 this.active = true;
                                 this.secRemaining = 1800;
                                 if (!this.claimed) {
                                     this.canClaim = true;
                                 }
                             }
                         }
                     }, 1000);
                 }
             }">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold border border-amber-200">
                                <i class="fa-solid fa-calendar-days"></i>
                            </span>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-slate-900 leading-tight">
                                    7-Dagen Login Kalender & Inlog Bonus
                                </h2>
                                <span class="text-[10px] font-bold text-amber-600">
                                    Elke avond 22:00 - 22:30
                                </span>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-400">
                            Dag {{ $nextStreakDay }} van 7
                        </span>
                    </div>

                    <!-- Days list -->
                    <div class="space-y-2.5">
                        @for($d = 1; $d <= 7; $d++)
                            @php
                                $item = $schedule[$d];
                                $isCompleted = ($currentStreak >= $d && !$canClaimStreak) || ($currentStreak >= $d && $d < $nextStreakDay);
                                $isCurrent = ($d === $nextStreakDay && $canClaimStreak);
                                $isFinalDay = ($d === 7);
                            @endphp

                            <div class="p-3 sm:p-3.5 rounded-xl border transition flex items-center justify-between gap-3
                                {{ $isCompleted ? 'bg-slate-50 border-slate-200 opacity-60' : '' }}
                                {{ $isCurrent ? 'bg-purple-50/50 border-2 border-purple-600 shadow-xs' : '' }}
                                {{ !$isCompleted && !$isCurrent && !$isFinalDay ? 'bg-white border-slate-200' : '' }}
                                {{ $isFinalDay && !$isCurrent && !$isCompleted ? 'bg-amber-50/40 border-2 border-amber-400' : '' }}">
                                
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs shrink-0
                                        {{ $isCompleted ? 'bg-emerald-600 text-white' : '' }}
                                        {{ $isCurrent ? 'bg-purple-700 text-white' : '' }}
                                        {{ !$isCompleted && !$isCurrent && !$isFinalDay ? 'bg-slate-100 text-slate-600' : '' }}
                                        {{ $isFinalDay && !$isCurrent && !$isCompleted ? 'bg-amber-500 text-white' : '' }}">
                                        @if($isCompleted)
                                            <i class="fa-solid fa-check"></i>
                                        @else
                                            D{{ $d }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-slate-900">
                                                Dag {{ $d }}
                                            </span>
                                            @if($isCompleted)
                                                <span class="text-[9px] font-black uppercase text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                                                    Geclaimd
                                                </span>
                                            @elseif($isCurrent)
                                                <span class="text-[9px] font-black uppercase text-purple-700 bg-purple-100 px-1.5 py-0.2 rounded border border-purple-200 animate-pulse">
                                                    Nu Claimbaar!
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] font-medium text-slate-500 mt-0.5">
                                            {{ $item['label'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-xs font-black text-slate-800 block">
                                        +€{{ number_format($item['cash'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Claim Button & Window Status -->
                <div class="mt-6 pt-4 border-t border-slate-100 space-y-3">
                    @if($canClaimStreak)
                    <form action="{{ route('daily.claim-streak') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-3.5 px-6 rounded-xl font-black text-sm uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-gift"></i> Claim Dag {{ $nextStreakDay }} Inlog Bonus!
                        </button>
                    </form>
                    <div class="text-center text-[11px] font-bold text-amber-600 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-fire text-amber-500 animate-pulse"></i>
                        <span>Inlog bonus is geopend tot 22:30! (Nog <strong x-text="formatTimer(secRemaining)"></strong>)</span>
                    </div>
                    @elseif($hasClaimedStreakToday)
                    <div class="w-full py-3 px-4 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 text-xs font-bold text-center flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span>Vandaag al geclaimd! Kom morgen tussen 22:00 en 22:30 terug voor Dag {{ $nextStreakDay === 7 ? 1 : $nextStreakDay + 1 }}.</span>
                    </div>
                    @else
                    <div class="w-full p-4 rounded-xl bg-amber-50/70 border border-amber-200 text-center space-y-2">
                        <div class="flex items-center justify-center gap-1.5 text-xs font-extrabold text-amber-900">
                            <i class="fa-regular fa-clock text-amber-600"></i>
                            <span>Inlog Bonus Venster: Elke avond van 22:00 tot 22:30</span>
                        </div>
                        <p class="text-[11px] text-amber-800">
                            De inlog bonus kan alleen worden geclaimd tijdens het avondvenster tussen 22:00 en 22:30.
                        </p>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-white border border-amber-300 text-xs font-black text-slate-900 shadow-xs">
                            <span class="text-amber-600">Opent over:</span>
                            <span x-text="formatTimer(secUntilStart)"></span>
                        </div>
                    </div>
                    <button type="button" disabled
                            class="w-full py-3 px-4 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-lock text-slate-400"></i>
                        <span>Beschikbaar tussen 22:00 en 22:30</span>
                    </button>
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
            const radius = centerX - 12;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // 1. Draw outer rim (Dark slate with gold rivets)
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius + 8, 0, 2 * Math.PI);
            ctx.fillStyle = '#0f172a';
            ctx.fill();
            ctx.strokeStyle = '#334155';
            ctx.lineWidth = 4;
            ctx.stroke();

            // 2. Draw 24 gold studs / rivets around the perimeter
            const numStuds = 24;
            for (let s = 0; s < numStuds; s++) {
                const studAngle = (2 * Math.PI / numStuds) * s;
                const studX = centerX + (radius + 2) * Math.cos(studAngle);
                const studY = centerY + (radius + 2) * Math.sin(studAngle);
                ctx.beginPath();
                ctx.arc(studX, studY, 3, 0, 2 * Math.PI);
                ctx.fillStyle = '#f59e0b';
                ctx.fill();
                ctx.strokeStyle = '#d97706';
                ctx.lineWidth = 1;
                ctx.stroke();
            }

            // 3. Draw wheel slices
            for (let i = 0; i < numSectors; i++) {
                const angle = this.currentAngle + (i * arc);
                ctx.beginPath();
                ctx.fillStyle = this.sectors[i].color;
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius - 4, angle, angle + arc, false);
                ctx.lineTo(centerX, centerY);
                ctx.fill();

                // Crisp divider line between wedges
                ctx.strokeStyle = '#f8fafc';
                ctx.lineWidth = 2.5;
                ctx.stroke();

                // Outer peg on divider
                const pegX = centerX + (radius - 5) * Math.cos(angle);
                const pegY = centerY + (radius - 5) * Math.sin(angle);
                ctx.beginPath();
                ctx.arc(pegX, pegY, 3, 0, 2 * Math.PI);
                ctx.fillStyle = '#f59e0b';
                ctx.fill();

                // 4. Draw Slice Text & Emoji / Icon
                ctx.save();
                ctx.translate(centerX, centerY);
                ctx.rotate(angle + arc / 2);

                ctx.textAlign = 'right';
                ctx.fillStyle = '#ffffff';
                ctx.font = '900 12px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                ctx.shadowColor = 'rgba(0, 0, 0, 0.9)';
                ctx.shadowBlur = 4;
                ctx.shadowOffsetX = 1;
                ctx.shadowOffsetY = 1;

                const textLabel = (this.sectors[i].emoji || '') + ' ' + (this.sectors[i].short || this.sectors[i].label);
                ctx.fillText(textLabel, radius - 24, 4);

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

                // Arrow is at the top (-PI/2 or 3*PI/2)
                const targetSectorMiddle = winningIndex * arc + (arc / 2);
                const desiredFinalOffset = (3 * Math.PI / 2) - targetSectorMiddle;

                // 6 full revolutions + offset
                const extraRevolutions = 6 * (2 * Math.PI);
                const totalTargetAngle = extraRevolutions + (desiredFinalOffset % (2 * Math.PI));

                const startAngle = this.currentAngle % (2 * Math.PI);
                const duration = 4000;
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
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
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
</style>
@endsection
