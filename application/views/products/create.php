<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $editing ? 'تعديل منتج' : 'إضافة منتج' ?> | SMART LIFE</title>
<?php $this->load->view('partials/assets'); ?>
</head>
<body>
<main>
<?php $this->load->view('partials/account'); ?>
    <h1><?= $editing ? 'تعديل منتج' : 'إضافة منتج' ?></h1>
    <p class="hint">كل الحقول مطلوبة. <?= $editing ? 'يمكنك تعديل بيانات المنتج، وتغيير حالته من قائمة المنتجات.' : 'يُضاف المنتج مفعّلًا؛ تُحدد كمياته في المستودعات لاحقًا.' ?></p>
    <?php if (!empty($errors)): ?>
        <ul class="errors" role="alert">
            <?php foreach ($errors as $error): ?>
                <li><?= html_escape($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <nav class="nav"><a href="<?= html_escape(site_url('products')) ?>">المنتجات</a><span> / <?= $editing ? 'تعديل المنتج' : 'إضافة منتج جديد' ?></span></nav>
    <?= form_open($form_action, array('class' => 'card form-card', 'data-track-changes' => 'true')) ?>
        <label for="code">كود المنتج</label>
        <input id="code" name="code" maxlength="50" required value="<?= html_escape($values['code']) ?>">
        <label for="name">اسم المنتج</label>
        <input id="name" name="name" maxlength="180" required value="<?= html_escape($values['name']) ?>">
        <label for="category_id">التصنيف</label>
        <select id="category_id" name="category_id" required>
            <option value="">اختر التصنيف</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (string) $category['id'] === $values['category_id'] ? 'selected' : '' ?>><?= html_escape($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="selling_price">سعر البيع</label>
        <input id="selling_price" name="selling_price" type="number" min="0" max="9999999999.99" step="0.01" required value="<?= html_escape($values['selling_price']) ?>">
        <div class="actions form-actions">
            <button type="submit"><?= $editing ? 'حفظ التعديلات' : 'حفظ المنتج الجديد' ?></button>
            <a class="reset" href="<?= html_escape(site_url('products')) ?>">إلغاء والعودة</a>
        </div>
    <?= form_close() ?>
</main>
</body>
</html>
