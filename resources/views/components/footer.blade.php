<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="{{ asset('icons/icon-192.png') }}" alt="SafeZone">
                <span>SafeZone</span>
            </div>
            <p>Portal resmi koordinasi keamanan ketertiban masyarakat nasional di bawah naungan Kepolisian Negara
                Republik Indonesia.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Layanan</h4>
                <ul>
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('dashboard') }}">Peta GIS & CCTV</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Akses</h4>
                <ul>
                    <li><a href="{{ route('login') }}">Masuk Satker</a></li>
                    <li><a href="{{ route('register') }}">Daftar Akun</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-base">
        <p>&copy; {{ date('Y') }} Markas Besar Kepolisian Negara Republik Indonesia. Hak Cipta Dilindungi.</p>
        <div class="footer-base-links">
            <a href="#">Kebijakan Privasi</a>
            <a href="#">Ketentuan</a>
        </div>
    </div>
</footer>
</body>

</html>