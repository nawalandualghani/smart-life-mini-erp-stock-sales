<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>فاتورة بيع جديدة | SMART LIFE</title>

<?php $this->load->view('partials/assets'); ?>
</head><body><main>
<?php $this->load->view('partials/account'); ?>
<nav class="nav"><a href="<?= html_escape(site_url('products')) ?>">المنتجات</a><a href="<?= html_escape(site_url('stock')) ?>">المخزون</a><a href="<?= html_escape(site_url('invoices')) ?>">الفواتير</a></nav>
<h1>فاتورة بيع جديدة</h1>
<p class="hint">يُخصم المخزون عند الحفظ، وتُسجّل الفاتورة باسم <?= html_escape($current_user['name']) ?>.</p>
<?php if ($errors): ?><ul class="errors" role="alert"><?php foreach ($errors as $error): ?><li><?= html_escape($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
<noscript><p class="errors">يلزم تفعيل JavaScript لإضافة المنتجات وحساب الإجمالي.</p></noscript>
<?= form_open('invoices/create', array('id' => 'invoice-form', 'data-track-changes' => 'true')) ?>
<input type="hidden" name="form_token" value="<?= html_escape($form_token) ?>">
<section class="card fields">
<div class="section-heading full-width"><span class="step-number">١</span><h2>حدّد العميل والمستودع</h2></div>
<div><label for="customer">العميل</label><select id="customer" name="customer_id" required>
<option value="">اختر العميل</option>
<?php foreach ($customers as $customer): ?><option value="<?= (int) $customer['id'] ?>" <?= (string) $customer['id'] === $state['customer_id'] ? 'selected' : '' ?>><?= html_escape($customer['name']) ?></option><?php endforeach; ?>
</select></div>
<div><label for="warehouse">المستودع</label><select id="warehouse" name="warehouse_id" required>
<option value="">اختر المستودع</option>
<?php foreach ($warehouses as $warehouse): ?><option value="<?= (int) $warehouse['id'] ?>" <?= (string) $warehouse['id'] === $state['warehouse_id'] ? 'selected' : '' ?>><?= html_escape($warehouse['name']) ?></option><?php endforeach; ?>
</select></div>
</section>
<section class="card">
<div class="section-heading"><span class="step-number">٢</span><h2>أضف المنتجات</h2></div>
<label for="product-search">البحث عن منتج بالاسم أو الكود</label>
<div class="actions search-actions"><input id="product-search" type="search" maxlength="180" placeholder="اختر المستودع أولًا" disabled><button id="search-button" type="button" disabled>بحث عن منتج</button></div>
<p id="search-message" class="hint" role="status"></p><ul id="search-results" class="results"></ul>
</section>
<section class="card table-wrap"><div class="section-heading"><span class="step-number">٣</span><h2>راجع الكميات</h2></div><table>
<thead><tr><th scope="col">الكود</th><th scope="col">المنتج</th><th scope="col">السعر</th><th scope="col">الكمية</th><th scope="col">الإجمالي</th><th scope="col">الإجراء</th></tr></thead>
<tbody id="invoice-lines"></tbody>
</table><p id="empty-lines" class="hint">أضف منتجًا إلى الفاتورة.</p></section>
<section class="card totals">
<h2>ملخص الفاتورة</h2>
<label for="discount">الخصم (%)</label><input id="discount" name="discount_percent" type="number" min="0" max="100" step="0.01" required value="<?= html_escape($state['discount_percent']) ?>">
<p><span>المجموع الفرعي</span><strong id="subtotal">0.00</strong></p>
<p><span>مبلغ الخصم</span><strong id="discount-amount">0.00</strong></p>
<p><span>الإجمالي النهائي</span><strong id="grand-total">0.00</strong></p>
<p id="calculation-error" role="alert"></p>
</section>
<div class="actions form-actions"><button id="save-invoice" type="submit" disabled>حفظ الفاتورة وخصم المخزون</button><a class="reset" href="<?= html_escape(site_url('invoices')) ?>">إلغاء والعودة</a><small class="hint">تأكد من العميل والمستودع والكميات قبل الحفظ.</small></div>
<?= form_close() ?>
<script id="invoice-state" type="application/json"><?= json_encode(array('items' => $state['items'], 'searchUrl' => site_url('invoices/search_products')), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="<?= html_escape(base_url('assets/js/invoice.js')) ?>" defer></script>
</main></body></html>
