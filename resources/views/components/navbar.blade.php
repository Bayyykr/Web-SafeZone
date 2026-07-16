<nav>
    <div class="nav-inner">
        <a href="/" class="nav-logo">
            <span>SafeZone</span>
        </a>
        <div class="nav-right">
            @auth
                <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="nav-link">Masuk</a>
            @endauth
            <a href="{{ route('dashboard') }}" class="btn-nav">Portal Darurat</a>
        </div>
    </div>
</nav>