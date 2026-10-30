<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$section = $this->uri->segment(1) ?: 'products';
$sections = array('products' => 'المنتجات', 'stock' => 'المخزون والمستودعات', 'invoices' => 'فواتير البيع');
$is_admin = $current_user['role'] === 'admin';
?>
<a class="skip-link" href="#page-content">انتقل إلى المحتوى</a>
<aside class="sidebar" aria-label="القائمة الرئيسية">
    <a class="brand company-brand" href="<?= html_escape(site_url('products')) ?>" aria-label="SMART LIFE — المنتجات"><span class="brand-logo"><img src="<?= html_escape(base_url('assets/images/smart-life-logo.png')) ?>" alt="SMART LIFE" width="1024" height="557"></span><small>إدارة المخزون والمبيعات</small></a>
    <!-- <span class="nav-label">مساحة العمل</span> -->
    <nav class="main-nav">
        <?php foreach ($sections as $path => $label): ?>
            <a href="<?= html_escape(site_url($path)) ?>" <?= $section === $path ? 'class="selected" aria-current="page"' : '' ?>>
                <span class="nav-icon" aria-hidden="true"><?= $path === 'products' ? '▦' : ($path === 'stock' ? '▤' : '▧') ?></span><?= $label ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <span class="nav-label">عمليات سريعة</span>
    <nav class="quick-nav">
        <a href="<?= html_escape(site_url('invoices/create')) ?>">＋ إنشاء فاتورة بيع</a>
        <?php if ($is_admin): ?>
            <a href="<?= html_escape(site_url('products/create')) ?>">＋ إضافة منتج</a>
            <a href="<?= html_escape(site_url('stock/manage')) ?>">＋ تحديد كمية منتج</a>
        <?php endif; ?>
    </nav>
    <!-- <div class="sidebar-note"><strong>خطوات العمل</strong><span>أضف المنتج، حدّد مخزونه، ثم ابدأ البيع.</span></div> -->
</aside>
<div class="workspace-topbar">
    <span class="section-name"><?= html_escape(isset($sections[$section]) ? $sections[$section] : 'مساحة العمل') ?></span>
    <div class="account-details"><span class="avatar" aria-hidden="true"><?= $is_admin ? 'A' : 'W' ?></span><span><strong><?= html_escape($current_user['name']) ?></strong><small><?= $is_admin ? 'مدير النظام' : 'مستخدم مستودع' ?></small></span></div>
    <?= form_open('auth/logout', array('class' => 'logout-form')) ?><button class="logout-button" type="submit">تسجيل الخروج</button><?= form_close() ?>
</div>
<span id="page-content" tabindex="-1"></span>
