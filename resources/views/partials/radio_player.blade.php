{{-- TBS FM Afdelingsradio uitgeschakeld --}}
<script>
    try {
        localStorage.removeItem('tbs_radio_playing');
        localStorage.removeItem('tbs_radio_volume');
        localStorage.removeItem('tbs_radio_station');
        if (window.__tbsGlobalRadioAudio) {
            window.__tbsGlobalRadioAudio.pause();
            window.__tbsGlobalRadioAudio.src = '';
            window.__tbsGlobalRadioAudio = null;
        }
    } catch (e) {}
</script>
