<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>المنتجات | SMART LIFE</title>
<?php $this->load->view('partials/assets'); ?>
</head>
<body>
    <main>
        <?php $this->load->view('partials/account'); ?>
        <h1>إدارة المنتجات</h1>
        <p class="description">قائمة المنتجات وأسعار البيع والتصنيفات.</p>
        <?php if ($current_user['role'] === 'admin'): ?>
            <a class="button" href="<?= html_escape(site_url('products/create')) ?>">＋ إضافة منتج جديد</a>
        <?php endif; ?>
        <a class="reset" href="<?= html_escape(site_url('stock')) ?>">المخزون</a>
        <?php if ($current_user['role'] === 'admin'): ?><div class="workflow"><strong>ابدأ من هنا</strong><a href="<?= html_escape(site_url('products/create')) ?>">١. إضافة منتج</a><span>←</span><a href="<?= html_escape(site_url('stock/manage')) ?>">٢. تحديد المخزون</a><span>←</span><a href="<?= html_escape(site_url('invoices/create')) ?>">٣. إنشاء فاتورة</a></div><?php endif; ?>
        <?php if ($created): ?>
            <div role="status" class="notice"><strong>تمت إضافة المنتج بنجاح.</strong><a href="<?= html_escape(site_url('stock/manage')) ?>">الخطوة التالية: تحديد كمية المنتج في المستودع ←</a></div>
        <?php endif; ?>
        <?php if ($updated || $status_changed): ?>
            <p role="status" class="notice"><?= $updated ? 'تم حفظ تعديلات المنتج.' : 'تم تحديث حالة المنتج.' ?></p>
        <?php endif; ?>
        <form data-ajax-filters class="filters" method="get" action="<?= html_escape(site_url('products')) ?>" role="search">
            <div>
                <label for="q">البحث بالاسم أو الكود</label>
                <input id="q" name="q" type="search" value="<?= html_escape($search) ?>" placeholder="مثال: دفتر أو EL-001">
            </div>
            <div>
                <label for="category_id">التصنيف</label>
                <select id="category_id" name="category_id">
                    <option value="">كل التصنيفات</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= $category_id === (int) $category['id'] ? 'selected' : '' ?>><?= html_escape($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">بحث</button>
            <a class="reset" href="<?= html_escape(site_url('products')) ?>" data-reset-filters>مسح الفلاتر</a>
        </form>
        <p id="listing-status" class="hint" role="status" aria-live="polite"></p>
        <div id="listing-results" aria-busy="false">
            <?php $this->load->view('products/results'); ?>
        </div>
    </main>
<script src="<?= html_escape(base_url('assets/js/listing.js')) ?>" defer></script>
</body>
</html>
