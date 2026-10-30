(function () {
    'use strict';
    const dialog = document.createElement('dialog');
    dialog.className = 'app-popup';
    dialog.setAttribute('aria-labelledby', 'popup-title');
    dialog.setAttribute('aria-describedby', 'popup-message');
    dialog.innerHTML = '<div class="popup-symbol" aria-hidden="true">!</div><h2 id="popup-title"></h2><p id="popup-message"></p><div class="popup-actions"></div>';
    document.body.append(dialog);
    let queue = Promise.resolve();
    function show(message, options = {}) {
        const task = queue.then(() => new Promise(resolve => {
            const previousFocus = document.activeElement;
            dialog.querySelector('#popup-title').textContent = options.title || (options.confirm ? 'تأكيد العملية' : 'تنبيه');
            dialog.querySelector('#popup-message').textContent = message;
            dialog.querySelector('.popup-symbol').textContent = options.success ? '✓' : (options.confirm ? '?' : '!');
            dialog.dataset.kind = options.success ? 'success' : (options.confirm ? 'confirm' : 'error');
            const actions = dialog.querySelector('.popup-actions');
            actions.replaceChildren();
            let finished = false;
            function finish(accepted) {
                if (finished) return;
                finished = true;
                dialog.close();
                dialog.oncancel = null;
                if (previousFocus && previousFocus.isConnected) previousFocus.focus();
                resolve(accepted);
            }
            const ok = document.createElement('button'); ok.type = 'button';
            ok.textContent = options.confirm ? (options.acceptLabel || 'نعم، متابعة') : 'حسنًا';
            ok.addEventListener('click', () => finish(true)); actions.append(ok);
            if (options.confirm) {
                const cancel = document.createElement('button'); cancel.type = 'button'; cancel.className = 'secondary'; cancel.textContent = 'إلغاء';
                cancel.addEventListener('click', () => finish(false)); actions.append(cancel);
            }
            (options.links || []).forEach(source => { const link = source.cloneNode(true); link.className = 'reset'; actions.append(link); });
            dialog.oncancel = event => { event.preventDefault(); finish(false); };
            dialog.showModal();
            // Give cancellation initial focus for actions that change data.
            (options.confirm ? actions.children[1] : ok).focus();
        }));
        queue = task.catch(() => {});
        return task;
    }
    window.SmartUI = { notify: (message, options) => show(message, options), confirm: (message, options = {}) => show(message, { ...options, confirm: true }) };
    document.addEventListener('submit', async event => {
        const form = event.target.closest('form[data-confirm]');
        if (!form || form.dataset.confirmed === 'true') return;
        event.preventDefault();
        const submitter = event.submitter;
        if (await SmartUI.confirm(form.dataset.confirm)) {
            form.dataset.confirmed = 'true';
            form.requestSubmit(submitter || undefined);
            delete form.dataset.confirmed;
        }
    });
    const forms = Array.from(document.querySelectorAll('form[data-track-changes]'));
    forms.forEach(form => {
        form.addEventListener('input', () => { form.dataset.dirty = 'true'; });
        form.addEventListener('change', () => { form.dataset.dirty = 'true'; });
    });
    // Custom confirmation for in-app navigation; browsers do not allow custom
    // dialogs during tab closure or refresh.
    document.addEventListener('click', async event => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target === '_blank' || link.closest('.app-popup') || link.matches('[data-reset-filters], .pagination a, .skip-link')) return;
        if (!forms.some(form => form.dataset.dirty === 'true')) return;
        event.preventDefault();
        if (await SmartUI.confirm('لديك تغييرات لم تُحفظ. هل تريد مغادرة الصفحة؟', { acceptLabel: 'مغادرة دون حفظ' })) window.location.assign(link.href);
    });
    const errors = document.querySelector('.errors[role="alert"], .error[role="alert"]');
    if (errors && errors.textContent.trim()) { errors.tabIndex = -1; errors.focus(); }
    let validationTimer;
    document.addEventListener('invalid', event => {
        event.preventDefault(); clearTimeout(validationTimer);
        const form = event.target.form;
        if (!form) return;
        validationTimer = setTimeout(() => {
            const invalid = Array.from(form.querySelectorAll(':invalid'));
            let summary = form.querySelector('[data-validation-summary]');
            if (!summary) {
                summary = document.createElement('div');
                summary.className = 'errors'; summary.dataset.validationSummary = 'true';
                summary.setAttribute('role', 'alert'); summary.style.whiteSpace = 'pre-line';
                form.prepend(summary);
            }
            summary.textContent = invalid.map(field => {
                const label = field.labels && field.labels[0] ? field.labels[0].textContent.trim() : 'الحقل';
                return field.validity.valueMissing ? 'يرجى تعبئة ' + label + '.' : label + ': ' + field.validationMessage;
            }).join('\n');
            summary.hidden = false;
            summary.scrollIntoView({ block: 'center', behavior: 'smooth' });
            if (invalid[0]) invalid[0].focus({ preventScroll: true });
        }, 0);
    }, true);
    document.addEventListener('input', event => {
        const form = event.target.form;
        const summary = form && form.querySelector('[data-validation-summary]');
        if (summary) summary.hidden = true;
    });
}());
