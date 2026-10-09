<header class="kw-header" id="site-header">
    <div class="kw-header-inner">

        {{-- LEFT: Logo --}}
        <a href="{{ url('/') }}" class="kw-logo" aria-label="KANG WENDRA - Brand & AI Architect">
            <span class="kw-logo-name">KANG WENDRA</span>
            <span class="kw-logo-role">Brand &amp; AI Architect</span>
        </a>

        {{-- CENTER: Primary Nav --}}
        <nav class="kw-nav" aria-label="Primary navigation">
            <ul class="kw-nav-list">
                <li class="kw-nav-item"><a href="{{ url('/') }}" class="kw-nav-link {{ request()->routeIs('beranda') ? 'kw-nav-active' : '' }}">Home</a></li>
                <li class="kw-nav-item"><a href="{{ url('/#ideas') }}" class="kw-nav-link">Ideas</a></li>
                <li class="kw-nav-item"><a href="{{ url('/#books-ip') }}" class="kw-nav-link">Books</a></li>
                <li class="kw-nav-item"><a href="{{ url('/#raymaizing') }}" class="kw-nav-link">Experience</a></li>
                <li class="kw-nav-item"><a href="{{ url('/#where-thinking') }}" class="kw-nav-link">Practice</a></li>
                <li class="kw-nav-item"><a href="{{ url('/#personal-note') }}" class="kw-nav-link">About</a></li>
            </ul>
        </nav>

        {{-- RIGHT: Search + CTA --}}
        <div class="kw-header-actions">
            <a href="{{ route('search') }}" class="kw-icon-btn" aria-label="Search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </a>

            <a href="{{ url('/#where-thinking') }}" class="kw-btn-work">
                <span>Work Together</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>

    </div>
</header>
