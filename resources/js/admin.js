(function () {
    const body = document.body;
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');
    const desktopQuery = window.matchMedia('(min-width: 1025px)');

    if (desktopQuery.matches && localStorage.getItem('admin-sidebar-collapsed') === 'true') {
        body.classList.add('sidebar-collapsed');
        sidebarToggle?.setAttribute('aria-expanded', 'false');
    }

    function closeMobileSidebar() {
        body.classList.remove('sidebar-open');
        sidebarToggle?.setAttribute('aria-expanded', desktopQuery.matches && !body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
    }

    sidebarToggle?.addEventListener('click', function () {
        if (desktopQuery.matches) {
            body.classList.toggle('sidebar-collapsed');
            const collapsed = body.classList.contains('sidebar-collapsed');
            localStorage.setItem('admin-sidebar-collapsed', collapsed ? 'true' : 'false');
            sidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            return;
        }

        body.classList.toggle('sidebar-open');
        sidebarToggle.setAttribute('aria-expanded', body.classList.contains('sidebar-open') ? 'true' : 'false');
    });

    sidebarOverlay?.addEventListener('click', closeMobileSidebar);

    const sidebarDropdowns = Array.from(document.querySelectorAll('[data-sidebar-dropdown]'));

    function getOpenSidebarDropdowns() {
        try {
            return JSON.parse(localStorage.getItem('admin-open-sidebar-dropdowns') || '[]');
        } catch (error) {
            return [];
        }
    }

    function saveOpenSidebarDropdowns() {
        const openDropdowns = sidebarDropdowns
            .filter((dropdown) => dropdown.classList.contains('open'))
            .map((dropdown) => dropdown.dataset.sidebarDropdown);
        localStorage.setItem('admin-open-sidebar-dropdowns', JSON.stringify(openDropdowns));
    }

    getOpenSidebarDropdowns().forEach((dropdownName) => {
        const dropdown = document.querySelector(`[data-sidebar-dropdown="${dropdownName}"]`);
        if (dropdown) {
            dropdown.classList.add('open');
        }
    });

    document.querySelectorAll('.sidebar-link').forEach((link) => {
        link.addEventListener('click', function () {
            saveOpenSidebarDropdowns();
            if (!desktopQuery.matches) closeMobileSidebar();
        });
    });

    sidebarDropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('.sidebar-dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', function () {
                dropdown.classList.toggle('open');
                saveOpenSidebarDropdowns();
            });
        }
    });

    document.addEventListener('click', function (event) {
        const targetId = event.target.closest('[data-modal-target]')?.dataset.modalTarget;
        if (targetId) document.getElementById(targetId)?.removeAttribute('hidden');

        const closeId = event.target.closest('[data-modal-close]')?.dataset.modalClose;
        if (closeId) document.getElementById(closeId)?.setAttribute('hidden', true);

        if (event.target.classList.contains('modal-backdrop')) event.target.setAttribute('hidden', true);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMobileSidebar();
            document.querySelectorAll('.modal-backdrop:not([hidden])').forEach((modal) => modal.setAttribute('hidden', true));
        }
    });

    desktopQuery.addEventListener('change', function () {
        body.classList.remove('sidebar-open');

        if (desktopQuery.matches && localStorage.getItem('admin-sidebar-collapsed') === 'true') {
            body.classList.add('sidebar-collapsed');
            sidebarToggle?.setAttribute('aria-expanded', 'false');
        } else if (!desktopQuery.matches) {
            body.classList.remove('sidebar-collapsed');
            sidebarToggle?.setAttribute('aria-expanded', 'false');
        }
    });
})();

(function () {
    let activeSosCount = Number(document.body.dataset.activeSosCount || 0);
    const statusUrl = document.body.dataset.sosStatusUrl;
    let alarmContext;
    let alarmOscillator;
    let alarmGain;
    let alarmInterval;

    async function getAlarmContext() {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return null;

        alarmContext ??= new AudioContext();

        if (alarmContext.state === 'suspended') {
            try {
                await alarmContext.resume();
            } catch (error) {
                return null;
            }
        }

        return alarmContext.state === 'running' ? alarmContext : null;
    }

    async function playSosAlarm() {
        const ctx = await getAlarmContext();
        if (!ctx) return false;

        const oscillator = ctx.createOscillator();
        const gain = ctx.createGain();
        oscillator.type = 'sawtooth';
        oscillator.frequency.setValueAtTime(780, ctx.currentTime);
        oscillator.frequency.setValueAtTime(520, ctx.currentTime + 0.22);
        oscillator.connect(gain);
        gain.connect(ctx.destination);
        gain.gain.setValueAtTime(0.001, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.18, ctx.currentTime + 0.03);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
        oscillator.start();
        oscillator.stop(ctx.currentTime + 0.65);
        return true;
    }

    async function startContinuousSosAlarm() {
        if (activeSosCount <= 0 || alarmOscillator) return false;

        const ctx = await getAlarmContext();
        if (!ctx) return false;

        localStorage.setItem('sos-alarm-unlocked', 'true');
        alarmOscillator = ctx.createOscillator();
        alarmGain = ctx.createGain();
        alarmOscillator.type = 'sawtooth';
        alarmOscillator.frequency.setValueAtTime(760, ctx.currentTime);
        alarmGain.gain.setValueAtTime(0.001, ctx.currentTime);
        alarmOscillator.connect(alarmGain);
        alarmGain.connect(ctx.destination);
        alarmOscillator.start();

        let highTone = false;
        alarmInterval = window.setInterval(() => {
            if (!alarmOscillator || !alarmGain) return;

            const now = ctx.currentTime;
            highTone = !highTone;
            alarmOscillator.frequency.cancelScheduledValues(now);
            alarmGain.gain.cancelScheduledValues(now);
            alarmOscillator.frequency.setTargetAtTime(highTone ? 920 : 520, now, 0.05);
            alarmGain.gain.setTargetAtTime(highTone ? 0.2 : 0.08, now, 0.03);
        }, 360);

        return true;
    }

    function stopContinuousSosAlarm() {
        window.clearInterval(alarmInterval);
        alarmInterval = null;

        if (alarmGain && alarmContext) {
            alarmGain.gain.setTargetAtTime(0.001, alarmContext.currentTime, 0.03);
        }

        const oscillator = alarmOscillator;
        if (oscillator) {
            window.setTimeout(() => {
                try {
                    oscillator.stop();
                } catch (error) {
                }
            }, 120);
        }

        alarmOscillator = null;
        alarmGain = null;
    }

    async function syncAlarmWithActiveSos() {
        if (activeSosCount > 0) {
            document.body.classList.add('sos-screen-alert');
            await startContinuousSosAlarm();
            return;
        }

        stopContinuousSosAlarm();
    }

    async function refreshActiveSosCount() {
        if (!statusUrl) return;

        try {
            const response = await fetch(statusUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!response.ok) return;

            const data = await response.json();
            const latestCount = Number(data.active || 0);
            const previousCount = activeSosCount;
            activeSosCount = latestCount;
            document.body.dataset.activeSosCount = String(activeSosCount);

            if (activeSosCount > previousCount) {
                document.body.classList.add('sos-screen-alert');
            }

            await syncAlarmWithActiveSos();
        } catch (error) {
        }
    }

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-test-alarm]')) playSosAlarm();
    });

    ['pointerdown', 'mousedown', 'click', 'keydown', 'touchstart'].forEach((eventName) => {
        document.addEventListener(eventName, async () => {
            if (activeSosCount > 0 && !alarmOscillator) {
                await startContinuousSosAlarm();
            }
        });
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) syncAlarmWithActiveSos();
    });

    window.addEventListener('focus', syncAlarmWithActiveSos);

    syncAlarmWithActiveSos();
    window.setTimeout(refreshActiveSosCount, 1000);
    window.setInterval(refreshActiveSosCount, 5000);
    window.setInterval(() => {
        if (!document.hidden && activeSosCount > 0 && !alarmOscillator) {
            syncAlarmWithActiveSos();
        }
    }, 2000);
})();

(function () {
    const dateEl = document.getElementById('realtime-date');
    const clockEl = document.getElementById('realtime-clock');
    if (!dateEl || !clockEl) return;

    const days = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
    const months = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];

    function updateClock() {
        const now = new Date();
        const dayName = days[now.getDay()];
        const day = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        dateEl.textContent = `${dayName}, ${day} ${monthName} ${year}`;
        clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
    }

    updateClock();
    setInterval(updateClock, 1000);
})();
