<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>المخزون | SMART LIFE</title>
<?php $this->load->view('partials/assets'); ?>
</head>
<body>
    <main>
        <?php $this->load->view('partials/account'); ?>
        <h1>المخزون حسب المستودع</h1>
        <?php if ($current_user['role'] === 'admin'): ?><a class="button" href="<?= html_escape(site_url('stock/manage')) ?>">＋ تحديد كمية منتج</a><?php endif; ?>
        <a class="reset" href="<?= html_escape(site_url('invoices/create')) ?>">إنشاء فاتورة بيع</a>
        <?php if ($saved): ?><div role="status" class="notice"><strong>تم حفظ المخزون بنجاح.</strong><a href="<?= html_escape(site_url('invoices/create')) ?>">الانتقال لإنشاء فاتورة بيع ←</a></div><?php endif; ?>
        <p class="hint">كل سطر يمثل كمية منتج في مستودع محدد. تظهر المنتجات المرتبطة بالمستودعات فقط، بما فيها المعطّلة.</p>
        <form data-ajax-filters class="filters" method="get" action="<?= html_escape(site_url('stock')) ?>" role="search">
            <div>
                <label for="q">البحث بالاسم أو الكود</label>
                <input id="q" name="q" type="search" value="<?= html_escape($search) ?>">
            </div>
            <div>
                <label for="warehouse_id">المستودع</label>
                <select id="warehouse_id" name="warehouse_id">
                    <option value="">كل المستودعات</option>
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option value="<?= (int) $warehouse['id'] ?>" <?= $warehouse_id === (int) $warehouse['id'] ? 'selected' : '' ?>><?= html_escape($warehouse['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <label class="check"><input type="checkbox" name="low_only" value="1" <?= $low_only ? 'checked' : '' ?>> المخزون المنخفض فقط</label>
            <button type="submit">عرض</button>
            <a class="button" href="<?= html_escape(site_url('stock')) ?>" data-reset-filters>مسح الفلاتر</a>
        </form>
        <p id="listing-status" class="hint" role="status" aria-live="polite"></p>
        <div id="listing-results" aria-busy="false">
            <?php $this->load->view('stock/results'); ?>
        </div>
    </main>
<script src="<?= html_escape(base_url('assets/js/listing.js')) ?>" defer></script>
</body>
</html>
