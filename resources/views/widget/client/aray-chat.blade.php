<div class="aray-assistant" id="arayAssistant" data-endpoint="{{ route('aray.chat') }}">
    <div class="aray-invite" id="arayInvite" aria-hidden="true">
        <span class="aray-invite-kicker">ARAY IS ONLINE</span>
        <strong>Ada yang ingin Anda pahami?</strong>
        <span>Mari mulai dari pertanyaan yang tepat.</span>
    </div>

    <section class="aray-panel" id="arayPanel" role="dialog" aria-modal="false" aria-labelledby="arayTitle" aria-hidden="true">
        <header class="aray-header">
            <div class="aray-identity">
                <span class="aray-avatar aray-avatar--small"><img src="{{ asset('assets/img/aray orb.png') }}" alt=""></span>
                <span>
                    <strong id="arayTitle">ARAY</strong>
                    <small><i></i> AI Smart Assistant</small>
                </span>
            </div>
            <button class="aray-close" id="arayClose" type="button" aria-label="Tutup ARAY">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
            </button>
        </header>

        <div class="aray-messages" id="arayMessages" aria-live="polite">
            <div class="aray-welcome">
                <span class="aray-welcome-orb"><img src="{{ asset('assets/img/aray orb.png') }}" alt="ARAY"></span>
                <span class="aray-eyebrow">UNDERSTAND BEFORE YOU BUILD</span>
                <h2>Halo, saya ARAY.</h2>
                <p>Saya membantu Anda memahami ide tentang brand, AI, dan sistem bisnis sebelum menentukan apa yang perlu dibangun.</p>
            </div>

            <div class="aray-suggestions" id="araySuggestions">
                <span class="aray-suggestions-label">Mulai dari sini</span>
                <button type="button" data-prompt="Apa sebenarnya yang dimaksud dengan Brand & AI Architect?">
                    <span>Brand &amp; AI Architect</span><b>&rarr;</b>
                </button>
                <button type="button" data-prompt="Bagaimana AI bisa memberi nilai nyata untuk bisnis saya?">
                    <span>AI untuk bisnis</span><b>&rarr;</b>
                </button>
                <button type="button" data-prompt="Bantu saya memahami masalah brand yang sedang saya hadapi.">
                    <span>Diagnosis brand</span><b>&rarr;</b>
                </button>
            </div>
        </div>

        <form class="aray-composer" id="arayForm">
            <label class="sr-only" for="arayInput">Tanyakan sesuatu kepada ARAY</label>
            <textarea id="arayInput" rows="1" maxlength="1500" placeholder="Tanyakan tentang brand, AI, atau bisnis..." required></textarea>
            <button type="submit" id="araySend" aria-label="Kirim pesan">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </form>
        <p class="aray-disclaimer">ARAY dapat membuat kesalahan. Gunakan human judgment untuk keputusan penting.</p>
    </section>

    <button class="aray-launcher" id="arayLauncher" type="button" aria-label="Buka ARAY AI Assistant" aria-controls="arayPanel" aria-expanded="false">
        <span class="aray-launcher-ring"></span>
        <img src="{{ asset('assets/img/aray orb.png') }}" alt="">
        <span class="aray-launcher-copy"><small>ASK</small><strong>ARAY</strong></span>
    </button>
</div>
