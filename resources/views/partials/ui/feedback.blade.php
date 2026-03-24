<div id="uiToastHost" class="pointer-events-none fixed right-4 top-4 z-[1200] flex w-full max-w-sm flex-col gap-2"></div>

<div id="uiConfirmModal" class="fixed inset-0 z-[1300] hidden items-center justify-center bg-slate-950/50 p-4" aria-hidden="true">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
        <h3 id="uiConfirmTitle" class="text-lg font-semibold text-slate-900">Confirm action</h3>
        <p id="uiConfirmMessage" class="mt-2 text-sm text-slate-600">Are you sure you want to continue?</p>
        <div class="mt-5 flex justify-end gap-2">
            <button id="uiConfirmCancel" type="button" class="ui-btn-secondary">Cancel</button>
            <button id="uiConfirmAccept" type="button" class="ui-btn-primary">Confirm</button>
        </div>
    </div>
</div>

<script>
(() => {
    if (window.__uiFeedbackInit) {
        return;
    }
    window.__uiFeedbackInit = true;

    const toastHost = document.getElementById('uiToastHost');
    const confirmModal = document.getElementById('uiConfirmModal');
    const confirmTitle = document.getElementById('uiConfirmTitle');
    const confirmMessage = document.getElementById('uiConfirmMessage');
    const confirmCancel = document.getElementById('uiConfirmCancel');
    const confirmAccept = document.getElementById('uiConfirmAccept');

    const toneMap = {
        info: 'border-slate-200 bg-white text-slate-800',
        success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
        error: 'border-rose-200 bg-rose-50 text-rose-800',
        warning: 'border-amber-200 bg-amber-50 text-amber-800',
    };

    function renderToast(message, tone = 'info', duration = 3200) {
        if (!toastHost) {
            return;
        }

        const toast = document.createElement('div');
        const toneClass = toneMap[tone] || toneMap.info;
        toast.className = `pointer-events-auto rounded-xl border px-3 py-2 text-sm shadow-sm transition ${toneClass}`;
        toast.textContent = String(message || '');
        toastHost.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-1');
            setTimeout(() => toast.remove(), 180);
        }, duration);
    }

    window.uiToast = function (message, tone = 'info', duration = 3200) {
        renderToast(message, tone, duration);
    };

    const nativeAlert = window.alert.bind(window);
    const nativeConfirm = window.confirm.bind(window);
    window.nativeAlert = nativeAlert;
    window.nativeConfirm = nativeConfirm;
    window.alert = function (message) {
        renderToast(message, 'info', 3500);
    };

    function closeConfirm(resolve, result) {
        if (confirmModal) {
            confirmModal.classList.add('hidden');
            confirmModal.classList.remove('flex');
            confirmModal.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
        resolve(result);
    }

    window.uiConfirm = function ({
        title = 'Confirm action',
        message = 'Are you sure you want to continue?',
        confirmText = 'Confirm',
        cancelText = 'Cancel',
        variant = 'primary',
    } = {}) {
        if (!confirmModal || !confirmTitle || !confirmMessage || !confirmCancel || !confirmAccept) {
            return Promise.resolve(nativeConfirm(message));
        }

        return new Promise((resolve) => {
            confirmTitle.textContent = title;
            confirmMessage.textContent = message;
            confirmCancel.textContent = cancelText;
            confirmAccept.textContent = confirmText;

            confirmAccept.className = variant === 'danger'
                ? 'inline-flex items-center rounded-xl border border-rose-700 bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-700'
                : 'ui-btn-primary';

            const onCancel = () => {
                cleanup();
                closeConfirm(resolve, false);
            };
            const onAccept = () => {
                cleanup();
                closeConfirm(resolve, true);
            };
            const onBackdrop = (event) => {
                if (event.target === confirmModal) {
                    onCancel();
                }
            };
            const onEscape = (event) => {
                if (event.key === 'Escape') {
                    onCancel();
                }
            };

            function cleanup() {
                confirmCancel.removeEventListener('click', onCancel);
                confirmAccept.removeEventListener('click', onAccept);
                confirmModal.removeEventListener('click', onBackdrop);
                document.removeEventListener('keydown', onEscape);
            }

            confirmCancel.addEventListener('click', onCancel);
            confirmAccept.addEventListener('click', onAccept);
            confirmModal.addEventListener('click', onBackdrop);
            document.addEventListener('keydown', onEscape);

            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
            confirmModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });
    };

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const prompt = form.dataset.confirm;
        if (!prompt) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();

        const approved = await window.uiConfirm({
            title: form.dataset.confirmTitle || 'Please confirm',
            message: prompt,
            confirmText: form.dataset.confirmText || 'Confirm',
            cancelText: form.dataset.cancelText || 'Cancel',
            variant: form.dataset.confirmVariant || 'primary',
        });

        if (!approved) {
            return;
        }

        form.dataset.confirmed = 'true';
        form.submit();
    });
})();
</script>
