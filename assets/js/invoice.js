(function () {
    'use strict';
    const state = JSON.parse(document.getElementById('invoice-state').textContent);
    const warehouse = document.getElementById('warehouse');
    const search = document.getElementById('product-search');
    const results = document.getElementById('search-results');
    const message = document.getElementById('search-message');
    const lines = document.getElementById('invoice-lines');
    const discount = document.getElementById('discount');
    const save = document.getElementById('save-invoice');
    let items = state.items.map(p => ({ ...p, quantity: String(p.quantity) }));
    let previousWarehouse = warehouse.value;
    let requestId = 0;
    let timer;
    function updateSearchAvailability() {
        search.disabled = !warehouse.value;
        document.getElementById('search-button').disabled = !warehouse.value;
        search.placeholder = warehouse.value ? 'اكتب اسم المنتج أو الكود، أو اضغط بحث لعرض المتاح' : 'اختر المستودع أولًا';
        if (!warehouse.value) message.textContent = 'اختر المستودع في الخطوة الأولى لعرض المنتجات المتاحة.';
    }
    function cents(value) {
        if (!/^\d+(\.\d{1,2})?$/.test(String(value))) throw new Error('أدخل سعرًا أو خصمًا صحيحًا.');
        const [whole, fraction = ''] = String(value).split('.');
        return BigInt(whole) * 100n + BigInt(fraction.padEnd(2, '0'));
    }
    function money(value) { return (value / 100n).toString() + '.' + (value % 100n).toString().padStart(2, '0'); }
    function totals() {
        try {
            let subtotal = 0n;
            items.forEach((item, i) => {
                if (!/^[1-9]\d*$/.test(item.quantity) || BigInt(item.quantity) > 4294967295n) throw new Error('أدخل كميات صحيحة موجبة.');
                if (item.available_quantity !== undefined && BigInt(item.quantity) > BigInt(item.available_quantity)) throw new Error('الكمية المطلوبة للمنتج «' + item.name + '» أكبر من المتاح (' + item.available_quantity + ').');
                const total = cents(item.selling_price) * BigInt(item.quantity);
                document.getElementById('line-total-' + i).textContent = money(total);
                subtotal += total;
            });
            if (subtotal > 99999999999999n) throw new Error('إجمالي الفاتورة يتجاوز الحد المسموح.');
            const bp = cents(discount.value);
            if (bp > 10000n) throw new Error('الخصم لا يتجاوز 100%.');
            const amount = (subtotal * bp + 5000n) / 10000n;
            document.getElementById('subtotal').textContent = money(subtotal);
            document.getElementById('discount-amount').textContent = money(amount);
            document.getElementById('grand-total').textContent = money(subtotal - amount);
            document.getElementById('calculation-error').textContent = '';
            save.disabled = items.length === 0;
        } catch (error) {
            document.getElementById('calculation-error').textContent = error.message;
            save.disabled = true;
        }
    }
    function render() {
        lines.replaceChildren();
        document.getElementById('empty-lines').hidden = items.length > 0;
        items.forEach((item, i) => {
            const row = document.createElement('tr');
            [item.code, item.name, item.selling_price].forEach(text => {
                const cell = document.createElement('td'); cell.textContent = text; row.append(cell);
            });
            const cell = document.createElement('td');
            const id = document.createElement('input'); id.type = 'hidden'; id.name = `items[${i}][product_id]`; id.value = item.id;
            const quantity = document.createElement('input'); quantity.type = 'number'; quantity.min = '1'; quantity.max = item.available_quantity === undefined ? '4294967295' : String(item.available_quantity); quantity.step = '1'; quantity.required = true;
            quantity.className = 'quantity'; quantity.name = `items[${i}][quantity]`; quantity.value = item.quantity; quantity.setAttribute('aria-label', 'كمية ' + item.name);
            quantity.addEventListener('input', () => { item.quantity = quantity.value; totals(); });
            cell.append(id, quantity);
            if (item.available_quantity !== undefined) { const available = document.createElement('small'); available.className = 'available-quantity'; available.textContent = 'المتاح: ' + item.available_quantity; cell.append(available); }
            row.append(cell);
            const total = document.createElement('td'); total.id = 'line-total-' + i; row.append(total);
            const action = document.createElement('td'); const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'secondary'; remove.textContent = 'حذف';
            remove.setAttribute('aria-label', 'حذف ' + item.name); remove.addEventListener('click', () => { items.splice(i, 1); document.getElementById('invoice-form').dataset.dirty = 'true'; render(); });
            action.append(remove); row.append(action); lines.append(row);
        });
        totals();
    }
    async function findProducts() {
        const current = ++requestId;
        results.replaceChildren();
        if (!warehouse.value) { message.textContent = 'اختر المستودع أولًا.'; return; }
        message.textContent = 'جارٍ البحث...';
        try {
            const url = new URL(state.searchUrl); url.searchParams.set('warehouse_id', warehouse.value); url.searchParams.set('q', search.value.trim());
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (response.redirected) throw new Error('انتهت جلسة الدخول. أعد تسجيل الدخول.');
            const data = await response.json();
            if (current !== requestId) return;
            if (!response.ok) throw new Error(data.error || 'تعذر البحث.');
            message.textContent = data.products.length ? 'النتائج (حتى 20 منتجًا):' : 'لا توجد منتجات متاحة مطابقة.';
            data.products.forEach(product => {
                const li = document.createElement('li'); const text = document.createElement('span');
                text.textContent = `${product.code} — ${product.name} | السعر: ${product.selling_price} | المتاح: ${product.quantity}`;
                const add = document.createElement('button'); add.type = 'button'; add.textContent = Number(product.quantity) === 0 ? 'نفد المخزون' : '＋ إضافة'; add.disabled = Number(product.quantity) === 0;
                add.addEventListener('click', () => {
                    const existing = items.find(item => String(item.id) === String(product.id));
                    if (existing) existing.quantity = String(Number(existing.quantity || 0) + 1);
                    else if (items.length < 100) items.push({ ...product, available_quantity: product.quantity, quantity: '1' });
                    else { message.textContent = 'الحد الأقصى 100 منتج.'; return; }
                    document.getElementById('invoice-form').dataset.dirty = 'true'; render();
                });
                li.append(text, add); results.append(li);
            });
        } catch (error) { if (current === requestId) message.textContent = error.message || 'تعذر البحث. حاول مرة أخرى.'; }
    }
    warehouse.addEventListener('change', async () => {
        warehouse.disabled = true;
        const accepted = !items.length || await SmartUI.confirm('تغيير المستودع سيمسح سطور الفاتورة. هل تريد المتابعة؟');
        warehouse.disabled = false;
        if (!accepted) { warehouse.value = previousWarehouse; return; }
        previousWarehouse = warehouse.value; items = []; ++requestId; results.replaceChildren(); message.textContent = ''; render(); updateSearchAvailability();
        if (warehouse.value) findProducts();
    });
    search.addEventListener('input', () => { clearTimeout(timer); ++requestId; timer = setTimeout(findProducts, 300); });
    search.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); clearTimeout(timer); findProducts(); } });
    document.getElementById('search-button').addEventListener('click', () => { clearTimeout(timer); findProducts(); });
    discount.addEventListener('input', totals);
    document.getElementById('invoice-form').addEventListener('submit', event => {
        totals(); if (save.disabled) { event.preventDefault(); return; }
        save.disabled = true; save.textContent = 'جارٍ الحفظ...';
    });
    render();
    updateSearchAvailability();
    if (warehouse.value) findProducts();
}());
