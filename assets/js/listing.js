(function () {
    'use strict';
    const form = document.querySelector('form[data-ajax-filters]');
    const results = document.getElementById('listing-results');
    const status = document.getElementById('listing-status');
    if (!form || !results) return;
    // Keep the address bar on the listing page; filters stay in the AJAX request.
    if (window.location.search) window.history.replaceState(null, '', form.action);
    let controller;
    let sequence = 0;
    function cancel() {
        ++sequence;
        if (controller) controller.abort();
    }
    function filterUrl() {
        const url = new URL(form.action);
        url.search = new URLSearchParams(new FormData(form)).toString();
        return url;
    }
    function syncForm(url) {
        Array.from(form.elements).forEach(field => {
            if (!field.name) return;
            if (field.type === 'checkbox') field.checked = url.searchParams.get(field.name) === field.value;
            else field.value = url.searchParams.get(field.name) || '';
        });
    }
    async function load(url) {
        cancel();
        const current = sequence;
        controller = new AbortController();
        status.textContent = 'جارٍ تحديث النتائج...';
        status.classList.remove('listing-error');
        results.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } });
            if (current !== sequence) return;
            if (response.redirected) { window.location.assign(response.url); return; }
            if (!response.ok) throw new Error('request');
            const data = await response.json();
            if (current !== sequence) return;
            results.innerHTML = data.html;
            status.textContent = 'تم عرض ' + data.total + ' نتيجة.';
        } catch (error) {
            if (current !== sequence || error.name === 'AbortError') return;
            status.textContent = 'تعذر تحديث النتائج. النتائج السابقة ما زالت معروضة؛ اضغط بحث للمحاولة مجددًا.';
            status.classList.add('listing-error');
        } finally {
            if (current === sequence) results.setAttribute('aria-busy', 'false');
        }
    }
    form.addEventListener('submit', event => { event.preventDefault(); load(filterUrl()); });
    form.querySelector('[data-reset-filters]').addEventListener('click', event => {
        event.preventDefault();
        const url = new URL(form.action); syncForm(url); load(url);
    });
    results.addEventListener('click', event => {
        const link = event.target.closest('.pagination a');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault(); load(new URL(link.href));
    });
}());
