(function () {
    const WARNA_MAP = {
        '#FF0000': 'Merah',
        '#E91E63': 'Merah Muda',
        '#9C27B0': 'Ungu',
        '#673AB7': 'Ungu Tua',
        '#3F51B5': 'Indigo',
        '#2196F3': 'Biru',
        '#03A9F4': 'Biru Muda',
        '#00BCD4': 'Tosca',
        '#009688': 'Hijau Tosca',
        '#4CAF50': 'Hijau',
        '#8BC34A': 'Hijau Muda',
        '#CDDC39': 'Kuning Hijau',
        '#FFEB3B': 'Kuning',
        '#FFC107': 'Amber',
        '#FF9800': 'Oranye',
        '#FF5722': 'Oranye Tua',
        '#795548': 'Coklat',
        '#607D8B': 'Abu Biru',
        '#9E9E9E': 'Abu-abu',
        '#000000': 'Hitam',
    };

    window.WARNA_MAP = WARNA_MAP;

    const searchForm  = document.getElementById('kategori-search-form');
    const searchInput = document.getElementById('kategori-search-input');
    let searchTimer;
    let searchController;

    function resetAndCloseModal(modal) {
        if (!modal) return;
        modal.querySelectorAll('form').forEach((form) => form.reset());
        modal.setAttribute('hidden', true);
    }

    function renderColorSwatches(scope = document) {
        scope.querySelectorAll('[data-color-swatches]').forEach((container) => {
            const hiddenInput = container.closest('.color-picker-wrap')?.querySelector('[data-color-value]');
            const previewSwatch = container.closest('.color-picker-wrap')?.querySelector('[data-color-preview]');
            const selectedLabel = container.closest('.color-picker-wrap')?.querySelector('[data-color-label]');

            if (!hiddenInput) return;

            container.innerHTML = '';

            Object.entries(WARNA_MAP).forEach(([hex, name]) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'color-swatch';
                btn.style.background = hex;
                btn.dataset.hex = hex;
                btn.title = name;
                btn.setAttribute('aria-label', name);

                if (hiddenInput.value.toUpperCase() === hex) {
                    btn.classList.add('selected');
                }

                btn.addEventListener('click', () => {
                    container.querySelectorAll('.color-swatch').forEach((s) => s.classList.remove('selected'));
                    btn.classList.add('selected');
                    hiddenInput.value = hex;
                    if (previewSwatch) previewSwatch.style.background = hex;
                    if (selectedLabel) selectedLabel.textContent = name;
                });

                container.appendChild(btn);
            });

            if (previewSwatch) previewSwatch.style.background = hiddenInput.value || '#FF0000';
            if (selectedLabel) selectedLabel.textContent = WARNA_MAP[hiddenInput.value?.toUpperCase()] ?? hiddenInput.value;
        });
    }

    async function loadKategoriUrl(url, pushState = true) {
        searchController?.abort();
        searchController = new AbortController();

        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: searchController.signal,
        });

        const html           = await response.text();
        const nextDocument   = new DOMParser().parseFromString(html, 'text/html');
        const nextResults    = nextDocument.getElementById('kategori-results');
        const currentResults = document.getElementById('kategori-results');

        if (nextResults && currentResults) {
            currentResults.innerHTML = nextResults.innerHTML;
            renderColorSwatches(currentResults);
        }

        if (pushState) {
            window.history.replaceState({}, '', url);
        }
    }

    searchForm?.addEventListener('submit', (e) => e.preventDefault());

    searchInput?.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(searchForm.action, window.location.origin);
            const q   = searchInput.value.trim();
            const jenisVal = searchForm.querySelector('[name="jenis"]')?.value;

            if (jenisVal) url.searchParams.set('jenis', jenisVal);
            if (q) url.searchParams.set('search', q);

            loadKategoriUrl(url.toString()).catch((err) => {
                if (err.name !== 'AbortError') console.error(err);
            });
        }, 300);
    });

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('#kategori-results nav a');
        if (paginationLink) {
            event.preventDefault();
            loadKategoriUrl(paginationLink.href).catch((err) => {
                if (err.name !== 'AbortError') console.error(err);
            });
            return;
        }

        const closeId = event.target.closest('[data-modal-close]')?.dataset.modalClose;
        if (closeId) {
            resetAndCloseModal(document.getElementById(closeId));
        }

        if (event.target.classList.contains('modal-backdrop')) {
            resetAndCloseModal(event.target);
        }

        if (event.target.closest('[data-toast-close]')) {
            event.target.closest('[data-toast]')?.remove();
        }
    });

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        setTimeout(() => toast.remove(), 4500);
    });

    renderColorSwatches();
})();
