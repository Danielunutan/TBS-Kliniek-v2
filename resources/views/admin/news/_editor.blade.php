<div x-data="tbsNewsEditor(@js(old('content', $content ?? '')))" class="space-y-3">

    <!-- Editor Toolbar Tabs -->
    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
        <div class="flex items-center gap-1.5">
            <button type="button" @click="tab = 'editor'" 
                    :class="tab === 'editor' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-pen-nib text-[10px]"></i> Teksteditor
            </button>
            <button type="button" @click="tab = 'preview'" 
                    :class="tab === 'preview' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-eye text-[10px]"></i> Live Voorbeeld
            </button>
        </div>
        <span class="text-[11px] font-bold text-slate-400">
            TBS Rich Editor
        </span>
    </div>

    <!-- Toolbar Buttons (Only in editor mode) -->
    <div x-show="tab === 'editor'" class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-50 border border-slate-200 rounded-xl">
        <!-- Text Styling -->
        <button type="button" @click="insertTag('[b]', '[/b]')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Vetgedrukt (Bold)">
            <i class="fa-solid fa-bold"></i>
        </button>
        <button type="button" @click="insertTag('[i]', '[/i]')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Cursief (Italic)">
            <i class="fa-solid fa-italic"></i>
        </button>
        <button type="button" @click="insertTag('[u]', '[/u]')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Onderstreept">
            <i class="fa-solid fa-underline"></i>
        </button>
        <button type="button" @click="insertTag('[s]', '[/s]')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Doorgehaald">
            <i class="fa-solid fa-strikethrough"></i>
        </button>

        <div class="h-5 w-px bg-slate-300 mx-1"></div>

        <!-- Headers -->
        <button type="button" @click="insertTag('[h2]', '[/h2]')" class="px-2 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Grote Koptekst (H2)">
            H2
        </button>
        <button type="button" @click="insertTag('[h3]', '[/h3]')" class="px-2 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Tussenkop (H3)">
            H3
        </button>
        <button type="button" @click="insertTag('[center]', '[/center]')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Centreren">
            <i class="fa-solid fa-align-center"></i>
        </button>

        <div class="h-5 w-px bg-slate-300 mx-1"></div>

        <!-- Colors -->
        <div class="flex items-center gap-1">
            <button type="button" @click="insertColor('purple')" class="w-5 h-5 rounded-full bg-purple-600 hover:scale-110 transition shadow-2xs cursor-pointer" title="Kleur: Paars"></button>
            <button type="button" @click="insertColor('red')" class="w-5 h-5 rounded-full bg-rose-600 hover:scale-110 transition shadow-2xs cursor-pointer" title="Kleur: Rood"></button>
            <button type="button" @click="insertColor('emerald')" class="w-5 h-5 rounded-full bg-emerald-600 hover:scale-110 transition shadow-2xs cursor-pointer" title="Kleur: Groen"></button>
            <button type="button" @click="insertColor('amber')" class="w-5 h-5 rounded-full bg-amber-500 hover:scale-110 transition shadow-2xs cursor-pointer" title="Kleur: Goud/Oranje"></button>
            <button type="button" @click="insertColor('blue')" class="w-5 h-5 rounded-full bg-blue-600 hover:scale-110 transition shadow-2xs cursor-pointer" title="Kleur: Blauw"></button>
        </div>

        <div class="h-5 w-px bg-slate-300 mx-1"></div>

        <!-- Media & Links -->
        <button type="button" @click="insertLink()" class="px-2.5 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Hyperlink Toevoegen">
            <i class="fa-solid fa-link text-indigo-500"></i>
            <span class="text-[11px]">Link</span>
        </button>
        <button type="button" @click="insertImage()" class="px-2.5 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Afbeelding URL Invoegen">
            <i class="fa-solid fa-image text-emerald-500"></i>
            <span class="text-[11px]">Plaatje</span>
        </button>

        <div class="h-5 w-px bg-slate-300 mx-1"></div>

        <!-- Quotes & Lists -->
        <button type="button" @click="insertTag('[quote]', '[/quote]')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Citaatblok">
            <i class="fa-solid fa-quote-left"></i>
        </button>
        <button type="button" @click="insertTag('- ')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Opsomming">
            <i class="fa-solid fa-list-ul"></i>
        </button>
        <button type="button" @click="insertTag('\n---\n')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-xs font-black transition flex items-center justify-center cursor-pointer shadow-2xs" title="Scheidingslijn">
            <i class="fa-solid fa-minus"></i>
        </button>

        <div class="h-5 w-px bg-slate-300 mx-1"></div>

        <!-- Quick Emojis -->
        <div class="flex items-center gap-1">
            <template x-for="emo in ['💖', '🔥', '📢', '⚠️', '👑', '💉', '⚡', '🎉', '🚨']">
                <button type="button" @click="insertEmoji(emo)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-sm transition flex items-center justify-center cursor-pointer shadow-2xs" x-text="emo"></button>
            </template>
        </div>
    </div>

    <!-- Editor Textarea Area -->
    <div x-show="tab === 'editor'">
        <textarea name="content" 
                  x-ref="editorTextarea"
                  x-model="content"
                  required 
                  rows="14" 
                  placeholder="Typ hier de tekst voor je nieuwsbericht... Gebruik de werkbalk hierboven voor opmaak, koppen, links en afbeeldingen."
                  class="w-full text-xs sm:text-sm font-mono font-medium bg-slate-50 border border-slate-200 text-slate-900 rounded-xl p-4 focus:bg-white focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none resize-y transition"></textarea>
        <p class="text-[11px] text-slate-400 mt-1 font-medium">
            Ondersteunt BBCode tags zoals <code>[b]vet[/b]</code>, <code>[i]cursief[/i]</code>, <code>[link=url]tekst[/link]</code> en <code>[img]url[/img]</code>.
        </p>
    </div>

    <!-- Live Preview Area -->
    <div x-show="tab === 'preview'" class="p-6 bg-slate-50/70 border border-slate-200 rounded-xl min-h-[350px]">
        <div class="text-xs font-black uppercase tracking-wider text-purple-700 mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-sparkles"></i> Live Voorbeeld van het artikel
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-xs sm:text-sm text-slate-800 font-medium leading-relaxed prose prose-purple max-w-none shadow-xs" 
             x-html="renderedPreview">
        </div>
    </div>

</div>

<script>
function tbsNewsEditor(initialContent) {
    return {
        tab: 'editor',
        content: initialContent || '',
        insertTag(openTag, closeTag = '') {
            const textarea = this.$refs.editorTextarea;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = this.content;
            const selected = text.substring(start, end) || 'tekst';
            
            const replacement = openTag + selected + closeTag;
            this.content = text.substring(0, start) + replacement + text.substring(end);
            
            this.$nextTick(() => {
                textarea.focus();
                textarea.setSelectionRange(start + openTag.length, start + openTag.length + selected.length);
            });
        },
        insertLink() {
            const url = prompt('Voer de website / pagina URL in (bijv. https://discord.gg/tbs of /profiel):');
            if (!url) return;
            const label = prompt('Optionele linktekst (of laat leeg):') || url;
            this.insertTag(`[link=${url}]${label}[/link]`, '');
        },
        insertImage() {
            const url = prompt('Voer de URL van de afbeelding in (bijv. https://i.imgur.com/... of /assets/logo.png):');
            if (!url) return;
            this.insertTag(`[img]${url}[/img]`, '');
        },
        insertEmoji(emoji) {
            this.insertTag(emoji, '');
        },
        insertColor(color) {
            this.insertTag(`[color=${color}]`, '[/color]');
        },
        get renderedPreview() {
            if (!this.content) return '<p class="text-slate-400 italic text-xs">Typ content in de bewerker om hier een live voorbeeld te zien...</p>';
            
            let t = this.content
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
                
            // Images (support absolute and root-relative)
            t = t.replace(/\[img\]((?:https?:\/\/|\/)[^\s<"']+)\[\/img\]/gi, '<div class="my-3"><img src="$1" class="rounded-xl max-w-full max-h-72 object-contain border border-slate-200 shadow-xs" alt="Afbeelding" /></div>');
            
            // Links
            t = t.replace(/\[(?:link|url)=((?:https?:\/\/|\/)[^\s<"']+)\](.*?)\[\/(?:link|url)\]/gi, '<a href="$1" target="_blank" class="text-purple-600 underline font-extrabold hover:text-purple-800">$2 ↗</a>');
            
            // Colors & alignment
            const colorMap = {
                purple: '#9333ea',
                emerald: '#10b981',
                amber: '#f59e0b',
                red: '#ef4444',
                rose: '#f43f5e',
                indigo: '#6366f1',
                blue: '#3b82f6'
            };
            t = t.replace(/\[color=([a-z0-9#]+)\](.*?)\[\/color\]/gi, (m, col, inner) => {
                const resolved = colorMap[col.toLowerCase()] || col;
                return `<span style="color:${resolved}" class="font-bold">${inner}</span>`;
            });
            t = t.replace(/\[center\](.*?)\[\/center\]/gi, '<div class="text-center my-2">$1</div>');

            // Headers
            t = t.replace(/\[h2\](.*?)\[\/h2\]/gi, '<h2 class="text-lg font-black text-slate-900 mt-4 mb-2">$1</h2>');
            t = t.replace(/\[h3\](.*?)\[\/h3\]/gi, '<h3 class="text-base font-black text-slate-900 mt-3 mb-1.5">$1</h3>');
            
            // Formatting
            t = t.replace(/\[b\](.*?)\[\/b\]/gi, '<strong class="font-black text-slate-900">$1</strong>');
            t = t.replace(/\*\*(.*?)\*\*/g, '<strong class="font-black text-slate-900">$1</strong>');
            t = t.replace(/\[i\](.*?)\[\/i\]/gi, '<em class="italic text-slate-700">$1</em>');
            t = t.replace(/\[u\](.*?)\[\/u\]/gi, '<span class="underline">$1</span>');
            t = t.replace(/\[s\](.*?)\[\/s\]/gi, '<del class="line-through text-slate-400">$1</del>');
            t = t.replace(/\[quote\](.*?)\[\/quote\]/gi, '<blockquote class="border-l-4 border-purple-500 bg-purple-50 p-3 my-2 text-xs font-semibold text-purple-900 italic rounded-r-lg">$1</blockquote>');
            t = t.replace(/---/g, '<hr class="my-3 border-slate-200" />');
            t = t.replace(/^- (.*?)$/gm, '<li class="ml-4 list-disc text-slate-700 font-medium">$1</li>');
            
            return t.replace(/\n/g, '<br />');
        }
    };
}
</script>
