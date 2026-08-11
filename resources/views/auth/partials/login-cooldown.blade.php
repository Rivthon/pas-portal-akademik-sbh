@php
    $blockedUntil = (int) session('login_blocked_until', 0);
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById(@json($buttonId));
        const label = button?.querySelector('[data-login-label]');

        if (!button || !label) {
            return;
        }

        const storageKey = @json('login-cooldown:' . $storageKey);
        const serverBlockedUntil = @json($blockedUntil * 1000);
        const storedBlockedUntil = Number(window.localStorage.getItem(storageKey) || 0);
        const blockedUntil = Math.max(serverBlockedUntil, storedBlockedUntil);
        const defaultLabel = label.textContent.trim();

        if (serverBlockedUntil > Date.now()) {
            window.localStorage.setItem(storageKey, String(serverBlockedUntil));
        }

        const updateButton = () => {
            const remainingSeconds = Math.ceil((blockedUntil - Date.now()) / 1000);

            if (remainingSeconds <= 0) {
                button.disabled = false;
                button.classList.remove('opacity-60', 'cursor-not-allowed');
                label.textContent = defaultLabel;
                window.localStorage.removeItem(storageKey);

                return;
            }

            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = String(remainingSeconds % 60).padStart(2, '0');
            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-not-allowed');
            label.textContent = `Coba lagi dalam ${minutes}:${seconds}`;
            window.setTimeout(updateButton, 1000);
        };

        updateButton();
    });
</script>
