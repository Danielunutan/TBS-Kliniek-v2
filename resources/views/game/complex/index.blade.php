<!DOCTYPE html>
<html lang="nl" class="h-full bg-slate-950 text-slate-100 select-none">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>TBS Complex &bull; 2D Virtuele Wereld</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}?v=3">
    
    <!-- Tailwind & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        * {
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            -webkit-user-select: none;
        }
        body {
            overflow: hidden;
            touch-action: none;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        canvas {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            image-rendering: pixelated;
        }
        .chat-bubble {
            animation: bubbleFloat 5s forwards cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes bubbleFloat {
            0% { opacity: 0; transform: translateY(10px) scale(0.9); }
            10% { opacity: 1; transform: translateY(0px) scale(1); }
            80% { opacity: 1; transform: translateY(-10px) scale(1); }
            100% { opacity: 0; transform: translateY(-25px) scale(0.95); pointer-events: none; }
        }
    </style>
</head>
<body class="h-full w-full flex flex-col bg-slate-950 overflow-hidden" x-data="tbsComplexClient()">

    <!-- TOP HEADER BAR (Habbo style dark steel) -->
    <header class="h-12 bg-slate-900 border-b border-slate-800 px-3 sm:px-4 flex items-center justify-between z-30 shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 hover:text-white transition" title="Terug naar TBS Kliniek Dashboard">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-black text-xs sm:text-sm tracking-wider uppercase text-white" x-text="currentRoom.name"></span>
            </div>
            <span class="hidden md:inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-purple-950/80 text-purple-300 border border-purple-800/80">
                <i class="fa-solid fa-users text-[9px] mr-1"></i> <span x-text="roomOccupants"></span> online
            </span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Room Navigator Button -->
            <button type="button" @click="toggleNavigator()" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center gap-1.5 transition active:scale-95 shadow-xs cursor-pointer">
                <i class="fa-solid fa-compass"></i>
                <span class="hidden sm:inline">Navigator</span>
            </button>

            <!-- Build / Furniture Mode -->
            <button type="button" @click="toggleInventory()" :class="inventoryOpen ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-200 hover:bg-slate-700'" class="px-3 py-1.5 rounded-lg font-black text-xs uppercase tracking-wider flex items-center gap-1.5 transition active:scale-95 cursor-pointer">
                <i class="fa-solid fa-couch"></i>
                <span class="hidden sm:inline">Meubels</span>
            </button>

            <!-- Zoom Controls -->
            <div class="flex items-center bg-slate-800 rounded-lg p-0.5 border border-slate-700">
                <button type="button" @click="zoomOut()" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-700 rounded transition text-xs">
                    <i class="fa-solid fa-minus"></i>
                </button>
                <button type="button" @click="zoomIn()" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-700 rounded transition text-xs">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- MAIN GAME CANVAS STAGE -->
    <main class="relative flex-1 w-full h-full overflow-hidden bg-slate-950 cursor-crosshair" id="canvas-container">
        <!-- HTML5 Canvas for the Isometric World -->
        <canvas id="gameCanvas" class="absolute inset-0 block w-full h-full"></canvas>

        <!-- FLOATING CHAT BUBBLES CONTAINER OVER CANVAS -->
        <div id="speech-bubbles" class="pointer-events-none absolute inset-0 overflow-hidden z-20"></div>

        <!-- TILE HOVER INFO TOOLTIP -->
        <div x-show="hoveredTile" x-cloak class="pointer-events-none absolute top-3 left-3 bg-slate-900/90 backdrop-blur-xs border border-slate-700/80 rounded-lg px-2.5 py-1 text-[11px] font-mono font-bold text-slate-300 z-10 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full" :class="hoveredTile?.walkable ? 'bg-emerald-400' : 'bg-rose-500'"></span>
            <span>Tegel: [<span x-text="hoveredTile?.x"></span>, <span x-text="hoveredTile?.y"></span>]</span>
            <span x-show="hoveredTile?.item" class="text-amber-400" x-text="'&bull; ' + hoveredTile?.item?.name"></span>
        </div>

        <!-- QUICK ACTION CONTROLS ON RIGHT -->
        <div class="absolute right-3 top-3 flex flex-col gap-2 z-10">
            <button type="button" @click="avatarWave()" class="w-10 h-10 rounded-xl bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 text-amber-400 flex items-center justify-center text-sm shadow-lg transition active:scale-95" title="Zwaaien">
                <i class="fa-solid fa-hand"></i>
            </button>
            <button type="button" @click="avatarToggleSit()" class="w-10 h-10 rounded-xl bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 text-sky-400 flex items-center justify-center text-sm shadow-lg transition active:scale-95" title="Zitten / Opstaan">
                <i class="fa-solid fa-chair"></i>
            </button>
            <button type="button" @click="resetCamera()" class="w-10 h-10 rounded-xl bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 text-slate-300 flex items-center justify-center text-sm shadow-lg transition active:scale-95" title="Camera Resetten">
                <i class="fa-solid fa-arrows-to-dot"></i>
            </button>
        </div>
    </main>

    <!-- INVENTORY / FURNITURE DRAWER (When opened) -->
    <div x-show="inventoryOpen" x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform translate-y-full opacity-0"
         x-transition:enter-end="transform translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform translate-y-0 opacity-100"
         x-transition:leave-end="transform translate-y-full opacity-0"
         class="absolute bottom-16 inset-x-0 mx-auto max-w-2xl bg-slate-900/95 backdrop-blur-md border border-slate-700 rounded-t-2xl p-4 shadow-2xl z-30 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-couch text-purple-400"></i>
                <h3 class="text-xs font-black uppercase tracking-wider text-white">Mijn Spullen &bull; Meubels Plaatsen</h3>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] text-slate-400">Selecteer meubel & klik op een vrije tegel</span>
                <button type="button" @click="inventoryOpen = false" class="text-slate-400 hover:text-white p-1 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Furniture List -->
        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 max-h-48 overflow-y-auto pr-1">
            <template x-for="item in catalogItems" :key="item.id">
                <div @click="selectItemToPlace(item)" 
                     :class="selectedFurniToPlace?.id === item.id ? 'border-amber-400 bg-amber-500/20 ring-2 ring-amber-400' : 'border-slate-800 bg-slate-800/80 hover:border-slate-600'"
                     class="p-2 rounded-xl border flex flex-col items-center justify-center gap-1 cursor-pointer transition active:scale-95 text-center">
                    <i :class="item.icon" class="text-xl my-1" :style="'color: ' + item.color"></i>
                    <span class="text-[10px] font-bold text-slate-200 truncate w-full" x-text="item.name"></span>
                </div>
            </template>
        </div>

        <div x-show="selectedFurniToPlace" class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs">
            <span class="text-amber-400 font-bold" x-text="'Geselecteerd: ' + selectedFurniToPlace?.name"></span>
            <div class="flex items-center gap-2">
                <button type="button" @click="rotateSelectedFurni()" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-1">
                    <i class="fa-solid fa-rotate-right text-[10px]"></i> Draaien
                </button>
                <button type="button" @click="cancelPlacing()" class="px-2.5 py-1 rounded-lg bg-rose-900/60 hover:bg-rose-800 text-rose-200 text-xs font-bold">
                    Annuleren
                </button>
            </div>
        </div>
    </div>

    <!-- HABBO NAVIGATOR MODAL -->
    <div x-show="navigatorOpen" x-cloak 
         class="fixed inset-0 flex items-center justify-center p-3 z-50 overflow-y-auto"
         style="background-color: rgba(15, 23, 42, 0.8); backdrop-filter: blur(6px);">
        <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl p-5 space-y-4"
             @click.outside="navigatorOpen = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-compass text-amber-500 text-base"></i>
                    <h3 class="text-sm font-black uppercase tracking-wider text-white">De Kliniek Navigator</h3>
                </div>
                <button type="button" @click="navigatorOpen = false" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Navigator Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
                <button type="button" @click="navTab = 'my'" :class="navTab === 'my' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs font-bold transition">
                    Mijn Cellen
                </button>
                <button type="button" @click="navTab = 'public'" :class="navTab === 'public' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs font-bold transition">
                    Openbare Afdelingen
                </button>
                <button type="button" @click="navTab = 'gang'" :class="navTab === 'gang' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs font-bold transition">
                    Bende Hoofdkwartieren
                </button>
            </div>

            <!-- Rooms List -->
            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                <template x-for="room in getFilteredRooms()" :key="room.id">
                    <div @click="switchRoom(room)" 
                         class="p-3 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700/80 flex items-center justify-between cursor-pointer transition active:scale-98 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" :class="room.bgClass">
                                <i :class="room.icon"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-white group-hover:text-amber-400 transition" x-text="room.name"></h4>
                                <p class="text-[11px] text-slate-400 leading-tight" x-text="room.desc"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-900 text-slate-300">
                                <i class="fa-solid fa-user text-[9px] mr-1 text-emerald-400"></i><span x-text="room.users"></span>
                            </span>
                            <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-white transition"></i>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- BOTTOM CHAT BAR & HABBO CONSOLE -->
    <footer class="h-14 bg-slate-900 border-t border-slate-800 px-3 sm:px-4 flex items-center gap-2 sm:gap-3 z-30 shrink-0">
        <!-- Avatar Preview Badge -->
        <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-400/40 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-person text-amber-400 text-base"></i>
        </div>

        <!-- Chat Input Form -->
        <form @submit.prevent="sendChat()" class="flex-1 flex items-center gap-2">
            <div class="relative flex-1">
                <input type="text" x-model="chatInput" maxlength="120"
                       placeholder="Typ een bericht... (Druk op Enter)"
                       class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-white placeholder-slate-500 focus:outline-hidden focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shrink-0 transition active:scale-95 cursor-pointer">
                <i class="fa-solid fa-paper-plane mr-1 sm:inline hidden"></i> Zeg
            </button>
        </form>

        <!-- Player Name Pill -->
        <div class="hidden lg:flex items-center gap-2 pl-2 border-l border-slate-800 text-xs">
            <span class="font-extrabold text-slate-300">{{ $character->name ?? 'Gedetineerde' }}</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Lvl {{ $character->level ?? 1 }}</span>
        </div>
    </footer>

    <!-- JAVASCRIPT: CLIENT ENGINE -->
    <script>
        function tbsComplexClient() {
            return {
                // UI state
                navigatorOpen: false,
                inventoryOpen: false,
                navTab: 'my',
                chatInput: '',
                roomOccupants: 1,
                selectedFurniToPlace: null,
                selectedRotation: 0,
                hoveredTile: null,

                // Current Active Room Definition
                currentRoom: {
                    id: 'cell_104',
                    name: 'Mijn Cel #104 &bull; Isolatievleugel',
                    desc: 'Je eigen beveiligde cel met stalen tralies en betonnen vloer',
                    width: 7,
                    height: 7,
                    doorX: 6,
                    doorY: 6,
                },

                // Preset Available Rooms for Navigator
                allRooms: [
                    { id: 'cell_104', type: 'my', name: 'Mijn Cel #104', desc: 'Je eigen cel met stalen deur en betonnen muren', icon: 'fa-solid fa-door-closed', bgClass: 'bg-amber-500/20 text-amber-400', users: 1, width: 7, height: 7, doorX: 6, doorY: 6 },
                    { id: 'luchtplaats', type: 'public', name: 'De Luchtplaats', desc: 'Centrale buitenplaats met basketbalring en bankjes', icon: 'fa-solid fa-sun', bgClass: 'bg-sky-500/20 text-sky-400', users: 4, width: 10, height: 10, doorX: 9, doorY: 9 },
                    { id: 'kantine', type: 'public', name: 'Recreatiezaal & Kantine', desc: 'Kaarttafels, TV hoek en ontmoetingsplek', icon: 'fa-solid fa-utensils', bgClass: 'bg-emerald-500/20 text-emerald-400', users: 2, width: 9, height: 8, doorX: 8, doorY: 7 },
                    { id: 'bende_hq', type: 'gang', name: 'Cartel Hoofdkwartier', desc: 'Besloten clubhuis met geheime kluizen en vergadertafel', icon: 'fa-solid fa-skull', bgClass: 'bg-purple-500/20 text-purple-400', users: 3, width: 8, height: 8, doorX: 7, doorY: 7 },
                ],

                // Furniture Catalog (Clean custom TBS Kliniek items)
                catalogItems: [
                    { id: 'bed', name: 'Stalen Celbed', icon: 'fa-solid fa-bed', color: '#94a3b8', width: 2, height: 1, canSit: true },
                    { id: 'chair', name: 'Bajeskruk', icon: 'fa-solid fa-chair', color: '#cbd5e1', width: 1, height: 1, canSit: true },
                    { id: 'table', name: 'Metalen Tafel', icon: 'fa-solid fa-table', color: '#64748b', width: 2, height: 1, canSit: false },
                    { id: 'toilet', name: 'RVS Toilet', icon: 'fa-solid fa-toilet', color: '#e2e8f0', width: 1, height: 1, canSit: true },
                    { id: 'crate', name: 'Smokkelkist', icon: 'fa-solid fa-box', color: '#d97706', width: 1, height: 1, canSit: false },
                    { id: 'punching_bag', name: 'Bokszak', icon: 'fa-solid fa-mitten', color: '#ef4444', width: 1, height: 1, canSit: false },
                ],

                // Room Items (Active placed furniture in current room)
                placedItems: [],

                // Avatar State
                avatar: {
                    x: 3,
                    y: 3,
                    targetX: 3,
                    targetY: 3,
                    pixelX: 0,
                    pixelY: 0,
                    direction: 4, // 0: N, 1: NE, 2: E, 3: SE, 4: S, 5: SW, 6: W, 7: NW
                    isWalking: false,
                    isSitting: false,
                    isWaving: false,
                    waveTimer: 0,
                    path: [],
                    name: @json($character->name ?? 'Gedetineerde'),
                },

                // Engine variables
                canvas: null,
                ctx: null,
                camera: {
                    x: 0,
                    y: 0,
                    zoom: 1.0,
                    isDragging: false,
                    dragStartX: 0,
                    dragStartY: 0,
                },
                tileWidth: 64,
                tileHeight: 32,
                animFrameId: null,

                init() {
                    this.canvas = document.getElementById('gameCanvas');
                    this.ctx = this.canvas.getContext('2d');
                    
                    this.setupResize();
                    this.initDefaultRoomItems();
                    this.setupEvents();
                    this.centerCamera();

                    // Start 60fps render and update loop
                    const loop = () => {
                        this.update();
                        this.render();
                        this.animFrameId = requestAnimationFrame(loop);
                    };
                    loop();
                },

                initDefaultRoomItems() {
                    // Place iconic starter furniture in cell
                    this.placedItems = [
                        { id: 1, type: 'bed', name: 'Stalen Celbed', x: 1, y: 1, width: 2, height: 1, canSit: true, rotation: 0 },
                        { id: 2, type: 'table', name: 'Metalen Tafel', x: 4, y: 1, width: 2, height: 1, canSit: false, rotation: 0 },
                        { id: 3, type: 'chair', name: 'Bajeskruk', x: 4, y: 2, width: 1, height: 1, canSit: true, rotation: 0 },
                        { id: 4, type: 'toilet', name: 'RVS Toilet', x: 1, y: 5, width: 1, height: 1, canSit: true, rotation: 0 },
                        { id: 5, type: 'crate', name: 'Smokkelkist', x: 5, y: 5, width: 1, height: 1, canSit: false, rotation: 0 },
                    ];
                },

                setupResize() {
                    const resize = () => {
                        const container = document.getElementById('canvas-container');
                        if (!container) return;
                        this.canvas.width = container.clientWidth * window.devicePixelRatio;
                        this.canvas.height = container.clientHeight * window.devicePixelRatio;
                        this.ctx.imageSmoothingEnabled = false;
                    };
                    window.addEventListener('resize', resize);
                    resize();
                },

                centerCamera() {
                    const container = document.getElementById('canvas-container');
                    if (!container) return;
                    this.camera.x = (container.clientWidth / 2);
                    this.camera.y = (container.clientHeight / 2) - 80;
                },

                setupEvents() {
                    const canvas = this.canvas;

                    // Mouse / Touch Dragging and Clicking
                    canvas.addEventListener('mousedown', (e) => {
                        if (e.button === 0) { // Left click
                            this.handleCanvasClick(e.clientX, e.clientY);
                        } else if (e.button === 2) { // Right click drag
                            this.camera.isDragging = true;
                            this.camera.dragStartX = e.clientX - this.camera.x;
                            this.camera.dragStartY = e.clientY - this.camera.y;
                        }
                    });

                    window.addEventListener('mousemove', (e) => {
                        if (this.camera.isDragging) {
                            this.camera.x = e.clientX - this.camera.dragStartX;
                            this.camera.y = e.clientY - this.camera.dragStartY;
                        }
                        this.updateHoverTile(e.clientX, e.clientY);
                    });

                    window.addEventListener('mouseup', () => {
                        this.camera.isDragging = false;
                    });

                    // Touch support for Mobile
                    let touchStartX = 0;
                    let touchStartY = 0;
                    let touchMoved = false;

                    canvas.addEventListener('touchstart', (e) => {
                        if (e.touches.length === 1) {
                            touchStartX = e.touches[0].clientX;
                            touchStartY = e.touches[0].clientY;
                            touchMoved = false;
                        }
                    }, { passive: true });

                    canvas.addEventListener('touchmove', (e) => {
                        if (e.touches.length === 1) {
                            const dx = Math.abs(e.touches[0].clientX - touchStartX);
                            const dy = Math.abs(e.touches[0].clientY - touchStartY);
                            if (dx > 8 || dy > 8) {
                                touchMoved = true;
                                this.camera.x += (e.touches[0].clientX - touchStartX) * 0.4;
                                this.camera.y += (e.touches[0].clientY - touchStartY) * 0.4;
                                touchStartX = e.touches[0].clientX;
                                touchStartY = e.touches[0].clientY;
                            }
                        }
                    }, { passive: true });

                    canvas.addEventListener('touchend', (e) => {
                        if (!touchMoved && e.changedTouches.length > 0) {
                            const touch = e.changedTouches[0];
                            this.handleCanvasClick(touch.clientX, touch.clientY);
                        }
                    });

                    // Zoom with mousewheel
                    canvas.addEventListener('wheel', (e) => {
                        e.preventDefault();
                        if (e.deltaY < 0) {
                            this.zoomIn();
                        } else {
                            this.zoomOut();
                        }
                    }, { passive: false });

                    // Disable context menu on right click
                    canvas.addEventListener('contextmenu', (e) => e.preventDefault());
                },

                // Screen to Isometric Tile Coordinate transformation
                screenToTile(screenX, screenY) {
                    const rect = this.canvas.getBoundingClientRect();
                    const clickX = (screenX - rect.left - this.camera.x) / this.camera.zoom;
                    const clickY = (screenY - rect.top - this.camera.y) / this.camera.zoom;

                    const halfW = this.tileWidth / 2;
                    const halfH = this.tileHeight / 2;

                    const tileX = Math.floor((clickX / halfW + clickY / halfH) / 2);
                    const tileY = Math.floor((clickY / halfH - clickX / halfW) / 2);

                    return { x: tileX, y: tileY };
                },

                // Isometric Tile Coordinate to Screen Pixel transformation
                tileToScreen(tileX, tileY) {
                    const halfW = this.tileWidth / 2;
                    const halfH = this.tileHeight / 2;
                    return {
                        x: (tileX - tileY) * halfW,
                        y: (tileX + tileY) * halfH
                    };
                },

                updateHoverTile(clientX, clientY) {
                    const coords = this.screenToTile(clientX, clientY);
                    if (coords.x >= 0 && coords.x < this.currentRoom.width &&
                        coords.y >= 0 && coords.y < this.currentRoom.height) {
                        const itemOnTile = this.placedItems.find(it => it.x === coords.x && it.y === coords.y);
                        this.hoveredTile = {
                            x: coords.x,
                            y: coords.y,
                            walkable: !itemOnTile || itemOnTile.canSit,
                            item: itemOnTile || null
                        };
                    } else {
                        this.hoveredTile = null;
                    }
                },

                handleCanvasClick(clientX, clientY) {
                    const coords = this.screenToTile(clientX, clientY);

                    // Check bounds
                    if (coords.x < 0 || coords.x >= this.currentRoom.width ||
                        coords.y < 0 || coords.y >= this.currentRoom.height) {
                        return;
                    }

                    // Placing furniture mode
                    if (this.selectedFurniToPlace) {
                        this.placeItemAt(coords.x, coords.y);
                        return;
                    }

                    // Check if clicked on a chair/bed to sit
                    const furni = this.placedItems.find(it => it.x === coords.x && it.y === coords.y);
                    if (furni && furni.canSit) {
                        this.walkTo(coords.x, coords.y, true);
                        return;
                    }

                    // Regular walk to tile
                    this.walkTo(coords.x, coords.y, false);
                },

                walkTo(targetX, targetY, shouldSitOnArrival) {
                    this.avatar.isSitting = false;
                    this.avatar.isWaving = false;

                    // Simple Bresenham / tile-step path generator
                    const path = [];
                    let currX = Math.round(this.avatar.x);
                    let currY = Math.round(this.avatar.y);

                    while (currX !== targetX || currY !== targetY) {
                        if (currX < targetX) currX++;
                        else if (currX > targetX) currX--;

                        if (currY < targetY) currY++;
                        else if (currY > targetY) currY--;

                        path.push({ x: currX, y: currY });
                    }

                    this.avatar.path = path;
                    this.avatar.shouldSitOnArrival = shouldSitOnArrival;
                },

                // Update game state (Movement, animations)
                update() {
                    const av = this.avatar;

                    // Handle path movement
                    if (av.path.length > 0) {
                        av.isWalking = true;
                        const nextStep = av.path[0];
                        const dx = nextStep.x - av.x;
                        const dy = nextStep.y - av.y;
                        const dist = Math.hypot(dx, dy);

                        const speed = 0.08; // smooth walking speed

                        if (dist <= speed) {
                            av.x = nextStep.x;
                            av.y = nextStep.y;
                            av.path.shift();

                            if (av.path.length === 0) {
                                av.isWalking = false;
                                if (av.shouldSitOnArrival) {
                                    av.isSitting = true;
                                }
                            }
                        } else {
                            av.x += (dx / dist) * speed;
                            av.y += (dy / dist) * speed;

                            // Update direction angle
                            if (dx > 0 && dy === 0) av.direction = 2; // East
                            else if (dx > 0 && dy > 0) av.direction = 3; // South-East
                            else if (dx === 0 && dy > 0) av.direction = 4; // South
                            else if (dx < 0 && dy > 0) av.direction = 5; // South-West
                            else if (dx < 0 && dy === 0) av.direction = 6; // West
                            else if (dx < 0 && dy < 0) av.direction = 7; // North-West
                            else if (dx === 0 && dy < 0) av.direction = 0; // North
                            else if (dx > 0 && dy < 0) av.direction = 1; // North-East
                        }
                    }

                    // Waving timer
                    if (av.isWaving) {
                        av.waveTimer--;
                        if (av.waveTimer <= 0) {
                            av.isWaving = false;
                        }
                    }
                },

                // Main Render loop (draws the isometric scene)
                render() {
                    const ctx = this.ctx;
                    const dpr = window.devicePixelRatio || 1;

                    ctx.save();
                    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

                    // Clear background (Deep prison dark atmosphere)
                    ctx.fillStyle = '#020617';
                    ctx.fillRect(0, 0, this.canvas.width / dpr, this.canvas.height / dpr);

                    // Apply Camera Transform
                    ctx.translate(this.camera.x, this.camera.y);
                    ctx.scale(this.camera.zoom, this.camera.zoom);

                    // 1. Draw Walls
                    this.drawWalls(ctx);

                    // 2. Draw Floor Tiles
                    this.drawFloor(ctx);

                    // 3. Draw Depth-Sorted Objects (Furniture & Avatar)
                    this.drawDepthSortedEntities(ctx);

                    ctx.restore();
                },

                drawWalls(ctx) {
                    const rW = this.currentRoom.width;
                    const rH = this.currentRoom.height;
                    const halfW = this.tileWidth / 2;
                    const halfH = this.tileHeight / 2;
                    const wallHeight = 110;

                    // North-West Wall (Left Wall)
                    ctx.fillStyle = '#334155'; // Slate concrete wall
                    ctx.strokeStyle = '#1e293b';
                    ctx.lineWidth = 1.5;

                    ctx.beginPath();
                    const topStart = this.tileToScreen(0, 0);
                    const topEnd = this.tileToScreen(rW, 0);
                    ctx.moveTo(topStart.x, topStart.y);
                    ctx.lineTo(topEnd.x, topEnd.y);
                    ctx.lineTo(topEnd.x, topEnd.y - wallHeight);
                    ctx.lineTo(topStart.x, topStart.y - wallHeight);
                    ctx.closePath();
                    ctx.fill();
                    ctx.stroke();

                    // Security Window with bars on North-West wall
                    const winPos = this.tileToScreen(rW / 2, 0);
                    ctx.fillStyle = '#0f172a';
                    ctx.fillRect(winPos.x - 24, winPos.y - wallHeight + 25, 48, 36);
                    ctx.strokeStyle = '#64748b';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(winPos.x - 24, winPos.y - wallHeight + 25, 48, 36);

                    // Window Bars
                    ctx.strokeStyle = '#94a3b8';
                    ctx.lineWidth = 2;
                    for (let bx = winPos.x - 16; bx <= winPos.x + 16; bx += 10) {
                        ctx.beginPath();
                        ctx.moveTo(bx, winPos.y - wallHeight + 25);
                        ctx.lineTo(bx, winPos.y - wallHeight + 61);
                        ctx.stroke();
                    }

                    // North-East Wall (Right Wall)
                    ctx.fillStyle = '#1e293b'; // Darker shadow concrete wall
                    ctx.strokeStyle = '#0f172a';
                    ctx.beginPath();
                    const rightEnd = this.tileToScreen(0, rH);
                    ctx.moveTo(topStart.x, topStart.y);
                    ctx.lineTo(rightEnd.x, rightEnd.y);
                    ctx.lineTo(rightEnd.x, rightEnd.y - wallHeight);
                    ctx.lineTo(topStart.x, topStart.y - wallHeight);
                    ctx.closePath();
                    ctx.fill();
                    ctx.stroke();

                    // Wall skirting board (Plint)
                    ctx.fillStyle = '#0f172a';
                    ctx.beginPath();
                    ctx.moveTo(topStart.x, topStart.y);
                    ctx.lineTo(topEnd.x, topEnd.y);
                    ctx.lineTo(topEnd.x, topEnd.y - 8);
                    ctx.lineTo(topStart.x, topStart.y - 8);
                    ctx.closePath();
                    ctx.fill();

                    ctx.beginPath();
                    ctx.moveTo(topStart.x, topStart.y);
                    ctx.lineTo(rightEnd.x, rightEnd.y);
                    ctx.lineTo(rightEnd.x, rightEnd.y - 8);
                    ctx.lineTo(topStart.x, topStart.y - 8);
                    ctx.closePath();
                    ctx.fill();
                },

                drawFloor(ctx) {
                    const rW = this.currentRoom.width;
                    const rH = this.currentRoom.height;
                    const halfW = this.tileWidth / 2;
                    const halfH = this.tileHeight / 2;

                    for (let x = 0; x < rW; x++) {
                        for (let y = 0; y < rH; y++) {
                            const p = this.tileToScreen(x, y);

                            // Diamond Tile Shape
                            ctx.beginPath();
                            ctx.moveTo(p.x, p.y);
                            ctx.lineTo(p.x + halfW, p.y + halfH);
                            ctx.lineTo(p.x, p.y + this.tileHeight);
                            ctx.lineTo(p.x - halfW, p.y + halfH);
                            ctx.closePath();

                            // Checkerboard concrete floor styling
                            const isAlt = (x + y) % 2 === 0;
                            ctx.fillStyle = isAlt ? '#475569' : '#3f4e64';
                            ctx.fill();

                            // Subtle tile border
                            ctx.strokeStyle = '#1e293b';
                            ctx.lineWidth = 1;
                            ctx.stroke();

                            // Hover highlight
                            if (this.hoveredTile && this.hoveredTile.x === x && this.hoveredTile.y === y) {
                                ctx.fillStyle = this.hoveredTile.walkable ? 'rgba(52, 211, 153, 0.4)' : 'rgba(244, 63, 94, 0.4)';
                                ctx.fill();
                                ctx.strokeStyle = this.hoveredTile.walkable ? '#10b981' : '#f43f5e';
                                ctx.lineWidth = 2;
                                ctx.stroke();
                            }
                        }
                    }
                },

                // Depth Sorting: Renders entities in order of (x + y) so closer objects overlap background ones
                drawDepthSortedEntities(ctx) {
                    const entities = [];

                    // Add placed furniture
                    this.placedItems.forEach(it => {
                        entities.push({
                            type: 'furni',
                            depth: it.x + it.y + 0.1,
                            item: it
                        });
                    });

                    // Add player avatar
                    entities.push({
                        type: 'avatar',
                        depth: this.avatar.x + this.avatar.y + 0.5,
                        avatar: this.avatar
                    });

                    // Add ghost preview if placing furniture
                    if (this.selectedFurniToPlace && this.hoveredTile) {
                        entities.push({
                            type: 'ghost',
                            depth: this.hoveredTile.x + this.hoveredTile.y + 0.2,
                            item: this.selectedFurniToPlace,
                            x: this.hoveredTile.x,
                            y: this.hoveredTile.y
                        });
                    }

                    // Sort by depth
                    entities.sort((a, b) => a.depth - b.depth);

                    // Render in order
                    entities.forEach(ent => {
                        if (ent.type === 'furni') {
                            this.drawFurniture(ctx, ent.item);
                        } else if (ent.type === 'avatar') {
                            this.drawAvatar(ctx, ent.avatar);
                        } else if (ent.type === 'ghost') {
                            this.drawGhostFurni(ctx, ent.item, ent.x, ent.y);
                        }
                    });
                },

                drawFurniture(ctx, it) {
                    const p = this.tileToScreen(it.x, it.y);
                    const halfH = this.tileHeight / 2;

                    ctx.save();

                    switch (it.type) {
                        case 'bed': // Stalen Bajesbed
                            // Shadow
                            ctx.fillStyle = 'rgba(0,0,0,0.3)';
                            ctx.beginPath();
                            ctx.ellipse(p.x, p.y + halfH + 8, 30, 14, 0, 0, Math.PI * 2);
                            ctx.fill();

                            // Bed Frame
                            ctx.fillStyle = '#64748b'; // Steel blue
                            ctx.fillRect(p.x - 22, p.y - 18, 44, 26);
                            ctx.fillStyle = '#cbd5e1'; // Inmate sheet
                            ctx.fillRect(p.x - 20, p.y - 16, 40, 22);

                            // Pillow
                            ctx.fillStyle = '#f8fafc';
                            ctx.fillRect(p.x - 18, p.y - 14, 12, 18);

                            // Steel frame border
                            ctx.strokeStyle = '#334155';
                            ctx.lineWidth = 1.5;
                            ctx.strokeRect(p.x - 22, p.y - 18, 44, 26);
                            break;

                        case 'table': // Metalen Tafel
                            ctx.fillStyle = 'rgba(0,0,0,0.25)';
                            ctx.beginPath();
                            ctx.ellipse(p.x, p.y + halfH + 6, 26, 12, 0, 0, Math.PI * 2);
                            ctx.fill();

                            // Table legs
                            ctx.strokeStyle = '#1e293b';
                            ctx.lineWidth = 2.5;
                            ctx.strokeRect(p.x - 18, p.y - 10, 36, 16);

                            // Table top
                            ctx.fillStyle = '#475569';
                            ctx.fillRect(p.x - 20, p.y - 18, 40, 14);
                            ctx.strokeStyle = '#0f172a';
                            ctx.strokeRect(p.x - 20, p.y - 18, 40, 14);

                            // Coffee mug on table
                            ctx.fillStyle = '#f59e0b';
                            ctx.fillRect(p.x - 8, p.y - 23, 6, 6);
                            break;

                        case 'chair': // Bajeskruk
                            ctx.fillStyle = 'rgba(0,0,0,0.25)';
                            ctx.beginPath();
                            ctx.ellipse(p.x, p.y + halfH, 12, 7, 0, 0, Math.PI * 2);
                            ctx.fill();

                            ctx.strokeStyle = '#334155';
                            ctx.lineWidth = 2;
                            ctx.beginPath();
                            ctx.moveTo(p.x, p.y - 10);
                            ctx.lineTo(p.x, p.y + 4);
                            ctx.stroke();

                            // Round seat
                            ctx.fillStyle = '#94a3b8';
                            ctx.beginPath();
                            ctx.ellipse(p.x, p.y - 10, 10, 6, 0, 0, Math.PI * 2);
                            ctx.fill();
                            ctx.strokeStyle = '#1e293b';
                            ctx.stroke();
                            break;

                        case 'toilet': // RVS Toilet
                            ctx.fillStyle = 'rgba(0,0,0,0.3)';
                            ctx.beginPath();
                            ctx.ellipse(p.x, p.y + halfH, 14, 8, 0, 0, Math.PI * 2);
                            ctx.fill();

                            // Steel Tank
                            ctx.fillStyle = '#cbd5e1';
                            ctx.fillRect(p.x - 10, p.y - 30, 20, 18);
                            // Toilet Bowl
                            ctx.fillStyle = '#e2e8f0';
                            ctx.beginPath();
                            ctx.ellipse(p.x, p.y - 10, 11, 8, 0, 0, Math.PI * 2);
                            ctx.fill();
                            ctx.strokeStyle = '#475569';
                            ctx.stroke();
                            break;

                        case 'crate': // Smokkelkist
                            ctx.fillStyle = '#b45309';
                            ctx.fillRect(p.x - 14, p.y - 16, 28, 20);
                            ctx.strokeStyle = '#78350f';
                            ctx.lineWidth = 1.5;
                            ctx.strokeRect(p.x - 14, p.y - 16, 28, 20);
                            ctx.beginPath();
                            ctx.moveTo(p.x - 14, p.y - 16);
                            ctx.lineTo(p.x + 14, p.y + 4);
                            ctx.stroke();
                            break;

                        default:
                            ctx.fillStyle = '#a855f7';
                            ctx.fillRect(p.x - 12, p.y - 18, 24, 20);
                            break;
                    }

                    ctx.restore();
                },

                drawGhostFurni(ctx, it, tileX, tileY) {
                    const p = this.tileToScreen(tileX, tileY);
                    ctx.save();
                    ctx.globalAlpha = 0.55;
                    ctx.fillStyle = '#fbbf24';
                    ctx.fillRect(p.x - 16, p.y - 18, 32, 22);
                    ctx.strokeStyle = '#d97706';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(p.x - 16, p.y - 18, 32, 22);
                    ctx.restore();
                },

                // Renders the Habbo-style Inmate Avatar
                drawAvatar(ctx, av) {
                    const p = this.tileToScreen(av.x, av.y);
                    const halfH = this.tileHeight / 2;

                    // Bobbing effect when walking
                    const bob = av.isWalking ? Math.sin(Date.now() * 0.015) * 2.5 : 0;
                    const sitOffset = av.isSitting ? 10 : 0;

                    ctx.save();
                    ctx.translate(p.x, p.y + halfH + bob + sitOffset);

                    // 1. Oval Floor Shadow
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.35)';
                    ctx.beginPath();
                    ctx.ellipse(0, 4, 14, 7, 0, 0, Math.PI * 2);
                    ctx.fill();

                    // 2. Legs / Prison Pants (Orange Inmate Pants)
                    ctx.fillStyle = '#ea580c'; // Vibrant prison orange
                    if (av.isSitting) {
                        ctx.fillRect(-7, -12, 14, 10);
                        // Boots forward
                        ctx.fillStyle = '#0f172a';
                        ctx.fillRect(-7, -2, 14, 6);
                    } else if (av.isWalking) {
                        // Walking stride
                        const stride = Math.sin(Date.now() * 0.015) * 5;
                        ctx.fillRect(-7, -18, 6, 16 + stride);
                        ctx.fillRect(1, -18, 6, 16 - stride);

                        // Boots
                        ctx.fillStyle = '#0f172a';
                        ctx.fillRect(-8, -2 + stride, 8, 5);
                        ctx.fillRect(0, -2 - stride, 8, 5);
                    } else {
                        // Standing Idle
                        ctx.fillRect(-6, -18, 5, 16);
                        ctx.fillRect(1, -18, 5, 16);

                        // Boots
                        ctx.fillStyle = '#0f172a';
                        ctx.fillRect(-7, -2, 7, 5);
                        ctx.fillRect(0, -2, 7, 5);
                    }

                    // 3. Torso (Grey Tanktop / Orange Prison Vest)
                    ctx.fillStyle = '#f97316';
                    ctx.fillRect(-8, -34, 16, 18);
                    ctx.fillStyle = '#fdba74'; // Muscle chest
                    ctx.fillRect(-5, -34, 10, 8);

                    // 4. Arms
                    ctx.fillStyle = '#fdba74'; // Skin tone
                    if (av.isWaving) {
                        // Right arm raised waving
                        const waveAngle = Math.sin(Date.now() * 0.02) * 4;
                        ctx.fillRect(8, -42 + waveAngle, 6, 14);
                        ctx.fillRect(-11, -32, 4, 14); // Left arm down
                    } else {
                        ctx.fillRect(-11, -32, 4, 14);
                        ctx.fillRect(7, -32, 4, 14);
                    }

                    // 5. Head
                    ctx.fillStyle = '#fdba74'; // Face skin
                    ctx.fillRect(-7, -48, 14, 14);

                    // Hair (Black shaved bajes coup)
                    ctx.fillStyle = '#0f172a';
                    ctx.fillRect(-8, -52, 16, 5);
                    ctx.fillRect(-8, -48, 3, 5);

                    // Eyes
                    ctx.fillStyle = '#0f172a';
                    if (av.direction >= 2 && av.direction <= 4) {
                        // Looking front-right
                        ctx.fillRect(0, -44, 2, 3);
                        ctx.fillRect(4, -44, 2, 3);
                    } else {
                        // Looking front-left
                        ctx.fillRect(-4, -44, 2, 3);
                        ctx.fillRect(0, -44, 2, 3);
                    }

                    // 6. Name Pill above avatar
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
                    ctx.beginPath();
                    ctx.roundRect(-25, -66, 50, 13, 4);
                    ctx.fill();
                    ctx.strokeStyle = '#334155';
                    ctx.lineWidth = 1;
                    ctx.stroke();

                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 9px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText(av.name, 0, -56);

                    ctx.restore();
                },

                // Chat message sending with floating bubble
                sendChat() {
                    const text = this.chatInput.trim();
                    if (!text) return;

                    this.createSpeechBubble(text);
                    this.chatInput = '';
                },

                createSpeechBubble(text) {
                    const container = document.getElementById('speech-bubbles');
                    if (!container) return;

                    // Calculate screen position of avatar head
                    const p = this.tileToScreen(this.avatar.x, this.avatar.y);
                    const headX = (p.x * this.camera.zoom) + this.camera.x;
                    const headY = (p.y * this.camera.zoom) + this.camera.y - 75;

                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble absolute px-3 py-1.5 rounded-xl bg-white text-slate-900 font-extrabold text-xs shadow-xl border border-slate-300 max-w-xs';
                    bubble.style.left = `${headX}px`;
                    bubble.style.top = `${headY}px`;
                    bubble.style.transform = 'translate(-50%, -100%)';
                    bubble.innerText = `${this.avatar.name}: ${text}`;

                    container.appendChild(bubble);

                    // Auto-remove bubble DOM element after 5 seconds
                    setTimeout(() => {
                        if (bubble.parentNode) {
                            bubble.parentNode.removeChild(bubble);
                        }
                    }, 5000);
                },

                // Quick avatar actions
                avatarWave() {
                    this.avatar.isWaving = true;
                    this.avatar.waveTimer = 90; // ~1.5 sec
                },

                avatarToggleSit() {
                    this.avatar.isSitting = !this.avatar.isSitting;
                    if (this.avatar.isSitting) {
                        this.avatar.path = [];
                        this.avatar.isWalking = false;
                    }
                },

                resetCamera() {
                    this.centerCamera();
                    this.camera.zoom = 1.0;
                },

                zoomIn() {
                    this.camera.zoom = Math.min(1.8, this.camera.zoom + 0.15);
                },

                zoomOut() {
                    this.camera.zoom = Math.max(0.6, this.camera.zoom - 0.15);
                },

                // Navigator & Room Switching
                toggleNavigator() {
                    this.navigatorOpen = !this.navigatorOpen;
                    if (this.navigatorOpen) {
                        this.inventoryOpen = false;
                    }
                },

                getFilteredRooms() {
                    return this.allRooms.filter(r => r.type === this.navTab);
                },

                switchRoom(room) {
                    this.currentRoom = {
                        id: room.id,
                        name: room.name,
                        desc: room.desc,
                        width: room.width,
                        height: room.height,
                        doorX: room.doorX,
                        doorY: room.doorY,
                    };
                    this.roomOccupants = room.users;

                    // Place avatar at room entrance
                    this.avatar.x = room.doorX - 1;
                    this.avatar.y = room.doorY - 1;
                    this.avatar.path = [];
                    this.avatar.isWalking = false;
                    this.avatar.isSitting = false;

                    // Adjust default furniture based on room
                    if (room.id === 'luchtplaats') {
                        this.placedItems = [
                            { id: 10, type: 'punching_bag', name: 'Bokszak', x: 2, y: 2, canSit: false },
                            { id: 11, type: 'punching_bag', name: 'Bokszak', x: 4, y: 2, canSit: false },
                            { id: 12, type: 'chair', name: 'Betonbank', x: 7, y: 3, canSit: true },
                            { id: 13, type: 'table', name: 'Picknicktafel', x: 7, y: 4, canSit: false },
                        ];
                    } else if (room.id === 'kantine') {
                        this.placedItems = [
                            { id: 20, type: 'table', name: 'Lange Eettafel', x: 3, y: 2, canSit: false },
                            { id: 21, type: 'chair', name: 'Kruk', x: 3, y: 3, canSit: true },
                            { id: 22, type: 'chair', name: 'Kruk', x: 4, y: 3, canSit: true },
                            { id: 23, type: 'crate', name: 'Voorraadkist', x: 7, y: 1, canSit: false },
                        ];
                    } else if (room.id === 'bende_hq') {
                        this.placedItems = [
                            { id: 30, type: 'table', name: 'Bende Vergadertafel', x: 3, y: 3, canSit: false },
                            { id: 31, type: 'chair', name: 'Leren Fauteuil', x: 3, y: 4, canSit: true },
                            { id: 32, type: 'crate', name: 'Buitkluis', x: 1, y: 1, canSit: false },
                            { id: 33, type: 'crate', name: 'Wapenbox', x: 1, y: 2, canSit: false },
                        ];
                    } else {
                        this.initDefaultRoomItems();
                    }

                    this.navigatorOpen = false;
                    this.centerCamera();
                },

                // Furniture Placement
                toggleInventory() {
                    this.inventoryOpen = !this.inventoryOpen;
                    if (this.inventoryOpen) {
                        this.navigatorOpen = false;
                    }
                },

                selectItemToPlace(item) {
                    this.selectedFurniToPlace = item;
                },

                rotateSelectedFurni() {
                    this.selectedRotation = (this.selectedRotation + 1) % 4;
                },

                cancelPlacing() {
                    this.selectedFurniToPlace = null;
                },

                placeItemAt(x, y) {
                    if (!this.selectedFurniToPlace) return;

                    // Remove existing item on that tile if any
                    this.placedItems = this.placedItems.filter(it => it.x !== x || it.y !== y);

                    // Add placed item
                    this.placedItems.push({
                        id: Date.now(),
                        type: this.selectedFurniToPlace.id,
                        name: this.selectedFurniToPlace.name,
                        x: x,
                        y: y,
                        canSit: this.selectedFurniToPlace.canSit,
                        rotation: this.selectedRotation
                    });

                    // Keep placing or cancel
                    this.selectedFurniToPlace = null;
                }
            };
        }
    </script>
</body>
</html>

