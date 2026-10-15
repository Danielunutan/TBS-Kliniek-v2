@extends('layouts.app')

@section('title', 'Patiëntendossier Voltooien - TBS Kliniek (16+)')

@section('content')
<div class="max-w-4xl mx-auto py-4 sm:py-8 space-y-6" x-data="onboardingWizard()">

    <!-- TOP HEADER & PROGRESS BAR -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-black px-3 py-1 rounded-full border border-purple-200 mb-2">
                    <i class="fa-solid fa-hospital-user text-purple-600"></i> Intake & Registratie (16+)
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Voltooi je Patiëntendossier
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
                    Welkom bij de TBS Kliniek! Voor je de afdeling op mag, dien je jouw officiële gebruikersnaam, roepnaam, geboortedatum en profielfoto in te stellen.
                </p>
            </div>

            <!-- STEP INDICATOR BADGES -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="step = 1" :class="step === 1 ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="w-9 h-9 rounded-xl font-black text-xs transition flex items-center justify-center">1</button>
                <div class="w-4 h-0.5 bg-slate-200"></div>
                <button type="button" @click="if(validateStep1()) step = 2" :class="step === 2 ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="w-9 h-9 rounded-xl font-black text-xs transition flex items-center justify-center">2</button>
                <div class="w-4 h-0.5 bg-slate-200"></div>
                <button type="button" @click="if(validateStep1() && validateStep2()) step = 3" :class="step === 3 ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="w-9 h-9 rounded-xl font-black text-xs transition flex items-center justify-center">3</button>
            </div>
        </div>

        <!-- PROGRESS BAR TRACK -->
        <div class="w-full bg-slate-100 rounded-full h-2 mt-6 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-amber-500 h-full rounded-full transition-all duration-500"
                 :style="'width: ' + ((step / 3) * 100) + '%;'"></div>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form action="{{ route('onboarding.complete') }}" method="POST" id="onboardingForm" class="space-y-6">
        @csrf

        <!-- ========================================== -->
        <!-- STAP 1: IDENTITEIT & 16+ LEEFTIJDSCHECK -->
        <!-- ========================================== -->
        <div x-show="step === 1" x-transition.opacity class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <span class="text-[10px] font-black uppercase tracking-wider text-purple-600">Stap 1 van 3</span>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Kies je In-Game Identiteit & Leeftijd</h2>
                <p class="text-xs text-slate-500 mt-0.5">Je gebruikersnaam is uniek en kan niet zomaar worden gewijzigd. Je weergavenaam is hoe mede-patiënten jou zien.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- 1. GEBRUIKERSNAAM (USERNAME) - STRICT ALPHANUMERIC -->
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Gebruikersnaam <span class="text-purple-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">@</span>
                        <input type="text" name="username" x-model="username" @input="debounceCheckUsername()" required
                               class="w-full bg-slate-50 border rounded-2xl pl-8 pr-10 py-3 text-xs font-bold text-slate-900 focus:outline-none transition"
                               :class="usernameStatus === 'available' ? 'border-emerald-500 focus:ring-2 focus:ring-emerald-400 bg-emerald-50/20' : (usernameStatus === 'taken' ? 'border-rose-500 focus:ring-2 focus:ring-rose-400 bg-rose-50/20' : 'border-slate-300 focus:ring-2 focus:ring-purple-600')">
                        
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                            <i x-show="usernameLoading" class="fa-solid fa-spinner animate-spin text-purple-600 text-xs"></i>
                            <i x-show="!usernameLoading && usernameStatus === 'available'" class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                            <i x-show="!usernameLoading && usernameStatus === 'taken'" class="fa-solid fa-circle-xmark text-rose-500 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-[11px] leading-tight" :class="usernameStatus === 'available' ? 'text-emerald-700 font-bold' : (usernameStatus === 'taken' ? 'text-rose-600 font-bold' : 'text-slate-400')">
                        <span x-text="usernameMessage || 'Alleen kleine letters (a-z), cijfers (0-9), underscores (_) en streepjes (-). Geen spaties of rare tekens.'"></span>
                    </p>
                </div>

                <!-- 2. WEERGAVENAAM (DISPLAY NAME) -->
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Weergavenaam / Roepnaam <span class="text-purple-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400"><i class="fa-solid fa-signature"></i></span>
                        <input type="text" name="display_name" x-model="displayName" required maxlength="30"
                               class="w-full bg-slate-50 border border-slate-300 rounded-2xl pl-10 pr-4 py-3 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white transition">
                    </div>
                    <p class="text-[11px] text-slate-400">
                        Hoe je naam op de afdeling getoond wordt (mag spaties, leestekens of speciale styling bevatten).
                    </p>
                </div>

            </div>

            <!-- 3. GEBOORTEDATUM & 16+ AGE CHECK -->
            <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200/90 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 border border-amber-200 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-amber-950 flex items-center gap-2">
                            <span>Leeftijdscontrole (16+)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-200/80 text-amber-900 border border-amber-300">Wettelijk Vereist</span>
                        </h4>
                        <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                            TBS Kliniek bevat misdaad-, casino- en fictieve geweldsthema's. Volgens de Nederlandse AVG-wetgeving en ons kliniekreglement dien je <strong>minimaal 16 jaar oud</strong> te zijn om een account te beheren.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-amber-200/60">
                    <div>
                        <label class="block text-xs font-extrabold text-amber-950 mb-1">Jouw Geboortedatum:</label>
                        <input type="date" name="birthdate" x-model="birthdate" @change="calculateAge()" required
                               max="{{ now()->subYears(16)->format('Y-m-d') }}" min="{{ now()->subYears(100)->format('Y-m-d') }}"
                               class="w-full bg-white border border-amber-300 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div class="flex items-center">
                        <div x-show="calculatedAge !== null" class="text-xs font-bold" :class="calculatedAge >= 16 ? 'text-emerald-700' : 'text-rose-700'">
                            <span x-show="calculatedAge >= 16">
                                <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Je bent <strong><span x-text="calculatedAge"></span> jaar oud</strong> (Toegelaten tot TBS Kliniek).
                            </span>
                            <span x-show="calculatedAge < 16">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1"></i> Helaas: je bent <strong x-text="calculatedAge"></strong> jaar oud. Toegang vereist 16+.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEXT BUTTON -->
            <div class="flex justify-end pt-2">
                <button type="button" @click="if(validateStep1()) step = 2" 
                        class="py-3 px-6 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-2 cursor-pointer">
                    <span>Volgende: Kies Foto & Avatar</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- STAP 2: FOTO KIEZEN & CROP-RASTER TOOL -->
        <!-- ========================================== -->
        <div x-show="step === 2" x-transition.opacity class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-600">Stap 2 van 3</span>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Kies of Upload Jouw Patiëntfoto</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kies een gevangenis-avatar of upload je eigen foto en snijd hem bij met onze interactieve crop-tool.</p>
                </div>

                <!-- UPLOAD OWN PHOTO TRIGGER BUTTON -->
                <button type="button" @click="openCropModal()" 
                        class="py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-xs transition flex items-center gap-2 shrink-0 cursor-pointer">
                    <i class="fa-solid fa-camera text-purple-400"></i>
                    <span>Upload Eigen Foto (Crop)</span>
                </button>
            </div>

            <!-- HIDDEN AVATAR INPUT -->
            <input type="hidden" name="avatar" :value="selectedAvatar">

            <!-- CURRENTLY SELECTED AVATAR PREVIEW -->
            <div class="p-4 rounded-2xl bg-purple-50/50 border border-purple-200 flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-purple-500 shadow-sm shrink-0 bg-slate-100">
                    <img :src="selectedAvatarUrl" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-700">Geselecteerde Profielfoto</span>
                    <h4 class="text-sm font-black text-slate-900" x-text="selectedAvatarLabel"></h4>
                    <p class="text-[11px] text-slate-500">Deze foto verschijnt op je profiel, ranglijst en berichten.</p>
                </div>
            </div>

            <!-- AVATAR PRESET GALLERY GRID -->
            <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-masks-theater text-purple-600"></i> Standaard TBS Avatars:
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    @foreach($avatars as $preset)
                    <div @click="selectPreset('{{ asset($preset['path']) }}', '{{ $preset['path'] }}', '{{ $preset['label'] }}')"
                         class="cursor-pointer rounded-2xl p-2 border-2 transition-all flex flex-col items-center gap-2 group relative overflow-hidden text-center"
                         :class="selectedAvatar === '{{ $preset['path'] }}' ? 'border-purple-600 bg-purple-50/50 shadow-xs ring-2 ring-purple-400' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <div class="w-full aspect-square rounded-xl overflow-hidden bg-slate-100 shadow-2xs">
                            <img src="{{ asset($preset['path']) }}" alt="{{ $preset['label'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <span class="text-[11px] font-extrabold text-slate-800 line-clamp-1">{{ $preset['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- BUTTONS PREV & NEXT -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="step = 1" class="py-2.5 px-5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs transition cursor-pointer">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Terug
                </button>
                <button type="button" @click="step = 3" class="py-3 px-6 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-2 cursor-pointer">
                    <span>Volgende: Profiel & Startrol</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- STAP 3: PROFIELKLEUR, BIO & SPECIALITEIT -->
        <!-- ========================================== -->
        <div x-show="step === 3" x-transition.opacity class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <span class="text-[10px] font-black uppercase tracking-wider text-purple-600">Stap 3 van 3</span>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Kies je Bende-Accent & Startspecialiteit</h2>
                <p class="text-xs text-slate-500 mt-0.5">Geef je profiel een eigen stijl en kies jouw favoriete criminele vaardigheid om mee te starten.</p>
            </div>

            <!-- 1. COLOR SELECTION -->
            <div class="space-y-3">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                    Jouw Profiel Accentkleur:
                </label>
                <input type="hidden" name="profile_color" :value="selectedColor">
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
                    @foreach($colors as $c)
                    <div @click="selectedColor = '{{ $c['id'] }}'"
                         class="cursor-pointer p-2.5 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 text-center"
                         :class="selectedColor === '{{ $c['id'] }}' ? 'border-purple-600 bg-purple-50/40 shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                        <div class="w-8 h-8 rounded-full {{ $c['bg'] }} shadow-xs border-2 border-white flex items-center justify-center text-white text-xs">
                            <i x-show="selectedColor === '{{ $c['id'] }}'" class="fa-solid fa-check"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-800">{{ $c['name'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 2. SPECIALTY SELECTION (3 CARDS) -->
            <div class="space-y-3">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                    Wat is jouw specialiteit in de kliniek? <span class="text-purple-600">*</span>
                </label>
                <input type="hidden" name="specialty" :value="selectedSpecialty">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Hosselaar -->
                    <div @click="selectedSpecialty = 'hossel'"
                         class="cursor-pointer p-4 rounded-2xl border-2 transition-all space-y-2 relative overflow-hidden"
                         :class="selectedSpecialty === 'hossel' ? 'border-amber-500 bg-amber-50/40 shadow-xs ring-2 ring-amber-300' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900">De Hosselaar</h4>
                        <p class="text-[11px] text-slate-500 leading-tight">Focus op handel, sigaretten en snelle cash op de binnenplaats.</p>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800">
                            +€500 Startgeld & +5 Charisma
                        </span>
                    </div>

                    <!-- Vechter -->
                    <div @click="selectedSpecialty = 'vecht'"
                         class="cursor-pointer p-4 rounded-2xl border-2 transition-all space-y-2 relative overflow-hidden"
                         :class="selectedSpecialty === 'vecht' ? 'border-rose-500 bg-rose-50/40 shadow-xs ring-2 ring-rose-300' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-hand-fist"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900">De Vechter</h4>
                        <p class="text-[11px] text-slate-500 leading-tight">Gespierd en meedogenloos in gevechten op de luchtplaats.</p>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-800">
                            +5 Kracht & +2 Verdediging
                        </span>
                    </div>

                    <!-- Meesterbrein -->
                    <div @click="selectedSpecialty = 'brein'"
                         class="cursor-pointer p-4 rounded-2xl border-2 transition-all space-y-2 relative overflow-hidden"
                         :class="selectedSpecialty === 'brein' ? 'border-purple-600 bg-purple-50/40 shadow-xs ring-2 ring-purple-300' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-brain"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900">Het Meesterbrein</h4>
                        <p class="text-[11px] text-slate-500 leading-tight">Strategisch kluizen kraken, bende-aansturing en ontsnappingsplannen.</p>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase bg-purple-100 text-purple-800">
                            +5 Intelligentie & +2 Discipline
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3. BIO / DOSSIER -->
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                    Dossier Notitie / Bio (Optioneel):
                </label>
                <textarea name="bio" rows="3" maxlength="300" placeholder="Waarom zit je in de kliniek? Wat is jouw reputatie?"
                          class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3.5 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white transition"></textarea>
                <span class="text-[11px] text-slate-400">Maximaal 300 tekens.</span>
            </div>

            <!-- SUBMIT BUTTONS -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="step = 2" class="py-2.5 px-5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs transition cursor-pointer">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Terug
                </button>
                <button type="submit" 
                        class="py-3.5 px-8 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-purple-600/25 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-hospital-user text-sm"></i>
                    <span>Voltooi Dossier & Betreed de Kliniek!</span>
                </button>
            </div>
        </div>

    </form>

    <!-- ======================================================== -->
    <!-- INTERACTIEVE CROP MODAL MET RASTER VOOR EIGEN FOTO       -->
    <!-- ======================================================== -->
    <div x-show="cropModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm animate-fade-in">
        <div @click.away="closeCropModal()" class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-crop-simple"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Foto Uitsnijden (Crop Raster)</h3>
                        <p class="text-[11px] text-slate-400">Sleep en zoom voor de perfecte profieluitsnede.</p>
                    </div>
                </div>
                <button type="button" @click="closeCropModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <!-- FILE PICKER AREA (IF NO IMAGE YET) -->
            <div x-show="!imageLoaded" class="border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center space-y-3 bg-slate-50 hover:bg-slate-100 transition cursor-pointer" @click="$refs.fileInput.click()">
                <div class="w-14 h-14 mx-auto rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div>
                    <span class="text-xs font-black text-slate-800 block">Klik om een foto te uploaden</span>
                    <span class="text-[11px] text-slate-400">JPG, PNG of WebP (max 5MB)</span>
                </div>
                <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept="image/*" class="hidden">
            </div>

            <!-- INTERACTIVE CANVAS WITH GRID OVERLAY (WHEN IMAGE LOADED) -->
            <div x-show="imageLoaded" class="space-y-4">
                <div class="relative w-full aspect-square bg-slate-950 rounded-2xl overflow-hidden flex items-center justify-center border border-slate-800 select-none">
                    <canvas x-ref="cropCanvas" width="360" height="360" class="cursor-grab active:cursor-grabbing w-full h-full"
                            @mousedown="startDrag($event)" @mousemove="onDrag($event)" @mouseup="endDrag()" @mouseleave="endDrag()"
                            @touchstart="startTouch($event)" @touchmove="onTouch($event)" @touchend="endDrag()"></canvas>

                    <!-- GRID OVERLAY (RULE OF THIRDS / RASTER) -->
                    <div class="absolute inset-0 pointer-events-none grid grid-cols-3 grid-rows-3 border border-white/40">
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-b border-white/20"></div>
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-r border-b border-white/20"></div>
                        <div class="border-b border-white/20"></div>
                        <div class="border-r border-white/20"></div>
                        <div class="border-r border-white/20"></div>
                        <div></div>
                    </div>

                    <!-- ROUND GUIDE PREVIEW OVERLAY -->
                    <div class="absolute inset-0 pointer-events-none rounded-full border-2 border-purple-500/80 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]"></div>
                </div>

                <!-- CONTROLS: ZOOM SLIDER -->
                <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <i class="fa-solid fa-magnifying-glass-minus text-slate-400 text-xs"></i>
                    <input type="range" min="0.5" max="3" step="0.05" x-model="zoom" @input="redrawCanvas()"
                           class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-purple-600">
                    <i class="fa-solid fa-magnifying-glass-plus text-slate-400 text-xs"></i>
                    <button type="button" @click="rotateImage()" class="px-2 py-1 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition" title="90 graden draaien">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>

                <!-- MODAL BUTTONS -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="$refs.fileInput.click()" class="text-xs text-purple-600 font-bold hover:underline cursor-pointer">
                        Andere foto kiezen
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="closeCropModal()" class="py-2 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition cursor-pointer">
                            Annuleren
                        </button>
                        <button type="button" @click="applyCrop()" :disabled="uploadingCrop"
                                class="py-2 px-5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i x-show="uploadingCrop" class="fa-solid fa-spinner animate-spin"></i>
                            <span x-text="uploadingCrop ? 'Opslaan...' : 'Uitsnede Toepassen'"></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
function onboardingWizard() {
    return {
        step: 1,
        username: '{{ old('username', $suggestedUsername) }}',
        displayName: '{{ old('display_name', $suggestedDisplayName) }}',
        birthdate: '{{ old('birthdate', $character?->birthdate?->format('Y-m-d') ?? '') }}',
        calculatedAge: null,
        usernameStatus: 'initial',
        usernameMessage: '',
        usernameLoading: false,
        checkTimeout: null,
        selectedAvatar: '{{ old('avatar', $character?->avatar ?: 'assets/characters/avatar_male_1.jpg') }}',
        selectedAvatarUrl: '{{ asset($character?->avatar ?: 'assets/characters/avatar_male_1.jpg') }}',
        selectedAvatarLabel: 'Standaard Avatar',
        selectedColor: '{{ old('profile_color', $character?->profile_color ?: 'purple') }}',
        selectedSpecialty: 'hossel',
        
        // Crop Modal State
        cropModalOpen: false,
        imageLoaded: false,
        uploadingCrop: false,
        rawImage: null,
        zoom: 1,
        rotation: 0,
        offsetX: 0,
        offsetY: 0,
        isDragging: false,
        startX: 0,
        startY: 0,

        init() {
            if (this.birthdate) {
                this.calculateAge();
            }
            if (this.username) {
                this.debounceCheckUsername();
            }
        },

        debounceCheckUsername() {
            clearTimeout(this.checkTimeout);
            this.usernameStatus = 'loading';
            this.usernameLoading = true;

            this.checkTimeout = setTimeout(() => {
                fetch('{{ route('onboarding.check-username') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ username: this.username })
                })
                .then(r => r.json())
                .then(data => {
                    this.usernameLoading = false;
                    this.usernameStatus = data.available ? 'available' : 'taken';
                    this.usernameMessage = data.message;
                })
                .catch(() => {
                    this.usernameLoading = false;
                    this.usernameStatus = 'initial';
                });
            }, 300);
        },

        calculateAge() {
            if (!this.birthdate) {
                this.calculatedAge = null;
                return;
            }
            const dob = new Date(this.birthdate);
            const diff = Date.now() - dob.getTime();
            const ageDate = new Date(diff);
            this.calculatedAge = Math.abs(ageDate.getUTCFullYear() - 1970);
        },

        validateStep1() {
            if (this.usernameStatus === 'taken' || !this.username) {
                alert('Kies eerst een geldige en beschikbare gebruikersnaam!');
                return false;
            }
            if (!this.displayName || this.displayName.trim().length < 2) {
                alert('Vul een geldige weergavenaam in van minimaal 2 tekens!');
                return false;
            }
            if (!this.birthdate) {
                alert('Vul je geboortedatum in om door te gaan.');
                return false;
            }
            if (this.calculatedAge !== null && this.calculatedAge < 16) {
                alert('Je moet minimaal 16 jaar oud zijn om te kunnen spelen in de TBS Kliniek!');
                return false;
            }
            return true;
        },

        validateStep2() {
            return !!this.selectedAvatar;
        },

        selectPreset(url, path, label) {
            this.selectedAvatarUrl = url;
            this.selectedAvatar = path;
            this.selectedAvatarLabel = label;
        },

        openCropModal() {
            this.cropModalOpen = true;
            this.imageLoaded = false;
            this.rawImage = null;
            this.zoom = 1;
            this.rotation = 0;
            this.offsetX = 0;
            this.offsetY = 0;
        },

        closeCropModal() {
            this.cropModalOpen = false;
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (evt) => {
                const img = new Image();
                img.onload = () => {
                    this.rawImage = img;
                    this.imageLoaded = true;
                    this.zoom = 1;
                    this.rotation = 0;
                    this.offsetX = 0;
                    this.offsetY = 0;
                    this.$nextTick(() => this.redrawCanvas());
                };
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        },

        startDrag(e) {
            this.isDragging = true;
            this.startX = e.clientX - this.offsetX;
            this.startY = e.clientY - this.offsetY;
        },

        onDrag(e) {
            if (!this.isDragging) return;
            this.offsetX = e.clientX - this.startX;
            this.offsetY = e.clientY - this.startY;
            this.redrawCanvas();
        },

        startTouch(e) {
            if (e.touches.length === 1) {
                this.isDragging = true;
                this.startX = e.touches[0].clientX - this.offsetX;
                this.startY = e.touches[0].clientY - this.offsetY;
            }
        },

        onTouch(e) {
            if (!this.isDragging || e.touches.length !== 1) return;
            this.offsetX = e.touches[0].clientX - this.startX;
            this.offsetY = e.touches[0].clientY - this.startY;
            this.redrawCanvas();
        },

        endDrag() {
            this.isDragging = false;
        },

        rotateImage() {
            this.rotation = (this.rotation + 90) % 360;
            this.redrawCanvas();
        },

        redrawCanvas() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas || !this.rawImage) return;
            const ctx = canvas.getContext('2d');
            const w = canvas.width;
            const h = canvas.height;

            ctx.clearRect(0, 0, w, h);
            ctx.save();
            ctx.translate(w / 2 + this.offsetX, h / 2 + this.offsetY);
            ctx.rotate((this.rotation * Math.PI) / 180);
            ctx.scale(this.zoom, this.zoom);

            const scale = Math.max(w / this.rawImage.width, h / this.rawImage.height);
            const drawW = this.rawImage.width * scale;
            const drawH = this.rawImage.height * scale;

            ctx.drawImage(this.rawImage, -drawW / 2, -drawH / 2, drawW, drawH);
            ctx.restore();
        },

        applyCrop() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas) return;
            this.uploadingCrop = true;

            const base64 = canvas.toDataURL('image/jpeg', 0.9);

            fetch('{{ route('onboarding.upload-avatar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ image: base64 })
            })
            .then(r => r.json())
            .then(data => {
                this.uploadingCrop = false;
                if (data.success) {
                    this.selectedAvatar = data.relative_path;
                    this.selectedAvatarUrl = data.url;
                    this.selectedAvatarLabel = 'Aangepaste Foto (Bijgesneden)';
                    this.closeCropModal();
                } else {
                    alert(data.message || 'Fout bij opslaan van foto.');
                }
            })
            .catch(() => {
                this.uploadingCrop = false;
                alert('Er is een netwerkfout opgetreden bij het uploaden.');
            });
        }
    };
}
</script>
@endsection
