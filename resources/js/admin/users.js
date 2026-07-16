(function () {
    const searchForm  = document.getElementById('users-search-form');
    const searchInput = document.getElementById('users-search-input');
    let searchTimer;
    let searchController;

    function resetAndCloseModal(modal) {
        if (!modal) return;
        modal.querySelectorAll('form').forEach((form) => {
            form.reset();
            clearFormErrors(form);
        });
        modal.setAttribute('hidden', true);
    }

    function clearFormErrors(form) {
        form.querySelectorAll('.form-input--error, .form-select--error').forEach((el) => {
            el.classList.remove('form-input--error', 'form-select--error');
        });
        form.querySelectorAll('.form-error-msg').forEach((el) => {
            el.textContent = '';
            el.hidden = true;
        });
    }

    function showFieldError(field, message) {
        field.classList.add(field.tagName === 'SELECT' ? 'form-select--error' : 'form-input--error');
        const name    = field.name;
        const errSpan = field.closest('div')?.querySelector(`[data-for="${name}"]`);
        if (errSpan) {
            errSpan.textContent = message;
            errSpan.hidden      = false;
        }
    }

    function clearFieldError(field) {
        field.classList.remove('form-input--error', 'form-select--error');
        const errSpan = field.closest('div')?.querySelector(`[data-for="${field.name}"]`);
        if (errSpan) {
            errSpan.textContent = '';
            errSpan.hidden      = true;
        }
    }

    function validateForm(form) {
        let valid = true;
        clearFormErrors(form);

        form.querySelectorAll('[name]').forEach((field) => {
            const value = field.value.trim();
            const label = field.dataset.label || field.name;

            if (field.required && !value) {
                showFieldError(field, `${label} wajib diisi.`);
                valid = false;
                return;
            }

            if (!value) return;

            if (field.name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                showFieldError(field, 'Format email tidak valid.');
                valid = false;
                return;
            }

            if (field.name === 'telepon' && !/^[0-9]{8,15}$/.test(value)) {
                showFieldError(field, 'Nomor telepon hanya boleh berisi angka (8–15 digit).');
                valid = false;
                return;
            }

            if (field.name === 'username' && value && !/^[a-zA-Z0-9._\-]+$/.test(value)) {
                showFieldError(field, 'Username hanya boleh berisi huruf, angka, titik, underscore, atau strip.');
                valid = false;
                return;
            }

            if (field.minLength > 0 && value.length < field.minLength) {
                showFieldError(field, `${label} minimal ${field.minLength} karakter.`);
                valid = false;
                return;
            }
        });

        return valid;
    }

    async function loadUsersUrl(url, pushState = true) {
        searchController?.abort();
        searchController = new AbortController();

        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: searchController.signal,
        });

        const html           = await response.text();
        const nextDocument   = new DOMParser().parseFromString(html, 'text/html');
        const nextResults    = nextDocument.getElementById('users-results');
        const currentResults = document.getElementById('users-results');

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

            loadUsersUrl(url.toString()).catch((err) => {
                if (err.name !== 'AbortError') console.error(err);
            });
        }, 300);
    });

    document.addEventListener('input', function (event) {
        const field = event.target;

        if (field.dataset.numeric) {
            field.value = field.value.replace(/\D/g, '');
        }

        if (field.closest('[data-user-form]') && field.name) {
            clearFieldError(field);
        }
    });

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-user-form]');
        if (!form) return;

        if (!validateForm(form)) {
            event.preventDefault();
            const firstError = form.querySelector('.form-input--error, .form-select--error');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError?.focus();
        }
    });

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('#users-results nav a');
        if (paginationLink) {
            event.preventDefault();
            loadUsersUrl(paginationLink.href).catch((err) => {
                if (err.name !== 'AbortError') console.error(err);
            });
            return;
        }

        const toggleBtn = event.target.closest('[data-toggle-password]');
        if (toggleBtn) {
            const wrap  = toggleBtn.closest('.form-password-wrap');
            const input = wrap?.querySelector('input[type="password"], input[type="text"]');
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
            }
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
