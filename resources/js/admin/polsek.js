(function () {
    const searchForm  = document.getElementById('polsek-search-form');
    const searchInput = document.getElementById('polsek-search-input');
    let searchTimer;
    let searchController;

    function resetAndCloseModal(modal) {
        if (!modal) return;
        modal.querySelectorAll('form').forEach((form) => form.reset());
        modal.setAttribute('hidden', true);
    }

    async function loadPolsekUrl(url, pushState = true) {
        searchController?.abort();
        searchController = new AbortController();

        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: searchController.signal,
        });

        const html           = await response.text();
        const nextDocument   = new DOMParser().parseFromString(html, 'text/html');
        const nextResults    = nextDocument.getElementById('polsek-results');
        const currentResults = document.getElementById('polsek-results');

        if (nextResults && currentResults) {
            currentResults.innerHTML = nextResults.innerHTML;
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
            if (q) url.searchParams.set('search', q);

            loadPolsekUrl(url.toString()).catch((err) => {
                if (err.name !== 'AbortError') console.error(err);
            });
        }, 300);
    });

    document.addEventListener('input', function (event) {
        const field = event.target;
        if (field.dataset.numeric) {
            field.value = field.value.replace(/\D/g, '');
        }
    });

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('#polsek-results nav a');
        if (paginationLink) {
            event.preventDefault();
            loadPolsekUrl(paginationLink.href).catch((err) => {
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
})();
