@extends('layouts.app')

@section('title', $room->title . ' - TBS Cinema')

@section('content')
<div class="space-y-6" x-data="cinemaTheater({{ $room->id }}, '{{ $room->youtube_video_id }}', {{ $messages->last()?->id ?? 0 }})">

    <!-- Top Cinema Navigation & Status -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('cinema.index') }}" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-sm transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 dark:text-white truncate max-w-md">
                        {{ $room->title }}
                    </h1>
                    @if($room->is_official)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300">
                        OFFICIEEL
                    </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Host: <strong>{{ $room->host?->name ?? 'TBS Kliniek' }}</strong> • {{ $room->description }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-center">
            <span class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold text-xs flex items-center gap-1.5 border border-emerald-200 dark:border-emerald-800/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Live Sessie</span>
            </span>

            @if($room->host_character_id === $character->id || Auth::user()->isStaff())
            <button type="button" @click="showChangeVideoModal = true"
                    class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-film"></i>
                <span>Video Wijzigen</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Main Watchparty Layout: 16:9 Cinema Screen (Left) + Interactive Session Chat (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- THEATER SCREEN (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
            <div class="rounded-3xl overflow-hidden bg-black border-2 border-slate-900 shadow-2xl relative aspect-video">
                <iframe id="cinema-player"
                        :src="'https://www.youtube-nocookie.com/embed/' + currentVideoId + '?autoplay=1&rel=0&modestbranding=1'"
                        class="w-full h-full border-0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                </iframe>
            </div>

            <!-- Quick Cinema Floating Reactions Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-xs flex items-center justify-between gap-2 overflow-x-auto">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 shrink-0 flex items-center gap-1.5">
                    <i class="fa-solid fa-sparkles text-amber-500"></i>
                    <span class="hidden sm:inline">Snelle Reacties:</span>
                </span>
                
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" @click="sendQuickReaction('🍿 Popcorn crunch!')" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 text-sm transition cursor-pointer active:scale-95" title="Popcorn">🍿</button>
                    <button type="button" @click="sendQuickReaction('🔥 HEET / HYPE!')" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 text-sm transition cursor-pointer active:scale-95" title="Fire">🔥</button>
                    <button type="button" @click="sendQuickReaction('😂 Stuk gaan!')" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 text-sm transition cursor-pointer active:scale-95" title="Laugh">😂</button>
                    <button type="button" @click="sendQuickReaction('🎬 Bioscoop vibes!')" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 text-sm transition cursor-pointer active:scale-95" title="Clapper">🎬</button>
                    <button type="button" @click="sendQuickReaction('💀 DOOD!')" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 text-sm transition cursor-pointer active:scale-95" title="Skull">💀</button>
                    <button type="button" @click="sendQuickReaction('😱 WAT GEBEURT DAAR!')" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 text-sm transition cursor-pointer active:scale-95" title="Shock">😱</button>
                    <button type="button" @click="sendQuickReaction('🚓 Zwaailichten / Boeven!')" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 text-sm transition cursor-pointer active:scale-95" title="Police">🚓</button>
                    <button type="button" @click="sendQuickReaction('🍻 Proost!')" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 text-sm transition cursor-pointer active:scale-95" title="Cheers">🍻</button>
                </div>
            </div>
        </div>

        <!-- SESSION LIVE CHAT (4 cols) -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xl flex flex-col h-[560px]">
            
            <!-- Chat Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-comments text-purple-600 dark:text-purple-400"></i>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Zaal Chatroom</h3>
                </div>
                <span class="text-[10px] text-slate-400 font-bold" x-text="pollStatus">Live</span>
            </div>

            <!-- Messages Stream -->
            <div id="cinema-chat-box" class="flex-1 overflow-y-auto py-3 space-y-3 pr-1 text-xs">
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex items-start gap-2.5 p-2 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <img :src="msg.avatar" class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-black text-slate-900 dark:text-white truncate" x-text="msg.character_name"></span>
                                    <template x-if="msg.is_staff">
                                        <span class="px-1 py-0.2 rounded text-[8px] font-black bg-purple-600 text-white">STAFF</span>
                                    </template>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="msg.time"></span>
                            </div>

                            <!-- Text Message -->
                            <template x-if="msg.text">
                                <p class="text-slate-700 dark:text-slate-300 break-words leading-relaxed" x-text="msg.text"></p>
                            </template>

                            <!-- Animated GIF -->
                            <template x-if="msg.gif_url">
                                <div class="mt-1.5 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 max-w-[220px]">
                                    <img :src="msg.gif_url" class="w-full h-auto object-cover max-h-40">
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Chat Inputs Bar & Modals -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 shrink-0 space-y-2 relative">
                
                <!-- GIF Picker Drawer -->
                <div x-show="showGifPicker" x-cloak @click.away="showGifPicker = false"
                     class="absolute bottom-16 inset-x-0 bg-white dark:bg-slate-900 border-2 border-purple-500 rounded-2xl p-3 shadow-2xl z-30 max-h-60 overflow-y-auto space-y-2">
                    <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-black text-purple-600 dark:text-purple-400">Kies een Meme GIF Reactie:</span>
                        <button type="button" @click="showGifPicker = false" class="text-slate-400 text-xs">&times;</button>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($gifs as $gif)
                        <div @click="sendGif('{{ $gif['url'] }}')" 
                             class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-purple-500 transition cursor-pointer aspect-video bg-slate-950 group">
                            <img src="{{ $gif['url'] }}" alt="{{ $gif['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Input Form -->
                <form @submit.prevent="sendMessage" class="space-y-2">
                    <div class="flex items-center gap-1.5">
                        <input type="text" x-model="chatInput" placeholder="Typ je reactie in de zaal..."
                               class="flex-1 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-purple-500">
                        
                        <button type="button" @click="showGifPicker = !showGifPicker"
                                class="px-2.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 hover:bg-purple-100 font-black text-xs transition cursor-pointer"
                                title="Kies een GIF reactie">
                            GIF
                        </button>

                        <button type="submit" 
                                class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs transition shadow-xs flex items-center justify-center cursor-pointer">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    <!-- CHANGE VIDEO MODAL (Host only) -->
    <div x-show="showChangeVideoModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs">
        <div @click.away="showChangeVideoModal = false"
             class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-film text-purple-600"></i>
                    <span>Zaal Video Wijzigen</span>
                </h3>
                <button type="button" @click="showChangeVideoModal = false" class="text-slate-400 text-sm">&times;</button>
            </div>

            <form action="{{ route('cinema.update-video', $room->slug) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nieuwe YouTube Video Link:</label>
                    <input type="text" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showChangeVideoModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 text-white font-black text-xs">
                        Video Starten
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function cinemaTheater(roomId, initialVideoId, lastMessageId) {
    return {
        roomId: roomId,
        currentVideoId: initialVideoId,
        lastId: lastMessageId,
        chatInput: '',
        showGifPicker: false,
        showChangeVideoModal: false,
        pollStatus: 'Verbonden',
        messages: @json($messages->map(fn($m) => [
            'id' => $m->id,
            'text' => $m->message,
            'gif_url' => $m->gif_url,
            'character_name' => $m->character?->name ?? 'Onbekend',
            'avatar' => asset($m->character?->avatar ?? 'characters/avatar-default.png'),
            'is_staff' => (bool)($m->character?->user?->isStaff()),
            'time' => $m->created_at->format('H:i'),
        ])),

        init() {
            this.scrollToBottom();
            // Poll for new messages every 3 seconds
            setInterval(() => this.pollMessages(), 3000);
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const box = document.getElementById('cinema-chat-box');
                if (box) box.scrollTop = box.scrollHeight;
            });
        },

        async sendMessage() {
            if (!this.chatInput.trim()) return;
            const text = this.chatInput.trim();
            this.chatInput = '';

            try {
                const res = await fetch(`/cinema/${this.roomId}/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.message) {
                        this.messages.push(data.message);
                        this.lastId = Math.max(this.lastId, data.message.id);
                        this.scrollToBottom();
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        async sendGif(gifUrl) {
            this.showGifPicker = false;
            try {
                const res = await fetch(`/cinema/${this.roomId}/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ gif_url: gifUrl }),
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.message) {
                        this.messages.push(data.message);
                        this.lastId = Math.max(this.lastId, data.message.id);
                        this.scrollToBottom();
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        async sendQuickReaction(reactionText) {
            this.chatInput = reactionText;
            this.sendMessage();
        },

        async pollMessages() {
            try {
                const res = await fetch(`/cinema/${this.roomId}/messages?after_id=${this.lastId}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.video_id && data.video_id !== this.currentVideoId) {
                        this.currentVideoId = data.video_id;
                    }
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(m => {
                            if (!this.messages.some(existing => existing.id === m.id)) {
                                this.messages.push(m);
                                this.lastId = Math.max(this.lastId, m.id);
                            }
                        });
                        this.scrollToBottom();
                    }
                }
            } catch (e) {
                // background poll silent
            }
        }
    }
}
</script>
@endsection
