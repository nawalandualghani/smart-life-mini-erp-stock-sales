<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تحديد كمية المخزون | SMART LIFE</title>
<?php $this->load->view('partials/assets'); ?>
</head>
<body><main>
<?php $this->load->view('partials/account'); ?>
    <h1>تحديد كمية المخزون</h1>
    <a href="<?= html_escape(site_url('stock')) ?>">العودة إلى المخزون</a>
    <?php if ($errors): ?>
        <ul class="errors" role="alert"><?php foreach ($errors as $error): ?><li><?= html_escape($error) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <section class="card form-card">
    <div class="section-heading"><span class="step-number">١</span><h2>اختر المنتج والمستودع</h2></div>
    <?php if ($selected_product && !$selected_warehouse): ?><p class="hint">تم اختيار المنتج. اختر المستودع ثم اضغط متابعة.</p><?php endif; ?>
    <form method="get" action="<?= html_escape(site_url('stock/manage') . '#stock-values') ?>">
        <label for="warehouse_id">المستودع</label>
        <select id="warehouse_id" name="warehouse_id" required>
            <option value="">اختر المستودع</option>
            <?php foreach ($warehouses as $warehouse): ?>
                <option value="<?= (int) $warehouse['id'] ?>" <?= (int) $warehouse['id'] === $warehouse_id ? 'selected' : '' ?>><?= html_escape($warehouse['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="product_id">المنتج</label>
        <select id="product_id" name="product_id" required>
            <option value="">اختر المنتج</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= (int) $product['id'] ?>" <?= (int) $product['id'] === $product_id ? 'selected' : '' ?>><?= html_escape($product['code'] . ' - ' . $product['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="actions"><button type="submit">متابعة وعرض الكمية الحالية ←</button></div>
    </form>
    </section>
    <?php if ($ready): ?>
        <section id="stock-values" class="card form-card">
        <div class="section-heading"><span class="step-number">٢</span><h2><?= $existing ? 'تعديل الكمية وحد التنبيه' : 'أدخل الكمية الافتتاحية' ?></h2></div>
        <p><?= html_escape($selected_product['name'] . ' — ' . $selected_warehouse['name']) ?></p>
        <div class="workflow"><strong>انتبه للكمية النهائية</strong> إذا كان الرصيد ١٠ وأدخلت ١٥، يصبح الرصيد ١٥.</div>
        <?= form_open('stock/manage', array('data-track-changes' => 'true')) ?>
            <input type="hidden" name="warehouse_id" value="<?= $warehouse_id ?>">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <label for="quantity">الكمية النهائية</label>
            <input id="quantity" name="quantity" type="number" min="0" max="4294967295" step="1" required value="<?= html_escape($values['quantity']) ?>">
            <label for="alert_quantity">حد التنبيه</label>
            <input id="alert_quantity" name="alert_quantity" type="number" min="0" max="4294967295" step="1" required value="<?= html_escape($values['alert_quantity']) ?>">
            <p class="hint">يظهر تنبيه انخفاض المخزون عندما تصل الكمية إلى هذا الحد أو أقل.</p>
            <div class="actions form-actions"><button type="submit">حفظ الكمية وحد التنبيه</button><a class="reset" href="<?= html_escape(site_url('stock')) ?>">إلغاء والعودة</a></div>
        <?= form_close() ?>
        </section>
    <?php endif; ?>
</main></body>
</html>
