<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>فاتورة #<?= (int) $invoice['id'] ?> | SMART LIFE</title><?php $this->load->view('partials/assets'); ?>
<link rel="stylesheet" href="<?= html_escape(base_url('assets/css/invoice-print.css')) ?>">
<script src="<?= html_escape(base_url('assets/js/print.js')) ?>" defer></script>
</head><body class="invoice-document"><main>
<?php $this->load->view('partials/account'); ?>
<nav class="nav"><a href="<?= html_escape(site_url('invoices')) ?>">كل الفواتير</a><a href="<?= html_escape(site_url('invoices/create')) ?>">فاتورة جديدة</a><a href="<?= html_escape(site_url('stock')) ?>">المخزون</a></nav>
<h1>فاتورة بيع #<?= (int) $invoice['id'] ?></h1>
<div class="actions no-print"><button type="button" data-print>طباعة الفاتورة / حفظ PDF</button><a class="reset" href="<?= html_escape(site_url('invoices/export/' . $invoice['id'])) ?>">تصدير الفاتورة CSV</a></div>
<p class="hint no-print">لحفظ نسخة PDF، اضغط طباعة ثم اختر «حفظ بتنسيق PDF» بدل الطابعة.</p>
<div class="print-only print-brand"><span class="brand-logo"><img src="<?= html_escape(base_url('assets/images/smart-life-logo.png')) ?>" alt="SMART LIFE" width="1024" height="557"></span><span>فاتورة بيع</span></div>
<section class="card fields"><div>العميل: <?= html_escape($invoice['customer_name']) ?><br>المستودع: <?= html_escape($invoice['warehouse_name']) ?></div>
<div>أنشأها: <?= html_escape($invoice['creator_name']) ?><br>التاريخ: <?= html_escape($invoice['created_at']) ?></div></section>
<section class="card table-wrap"><table><thead><tr><th>الكود</th><th>المنتج</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead><tbody>
<?php foreach ($invoice['items'] as $item): ?><tr>
<td><?= html_escape($item['product_code']) ?></td><td><?= html_escape($item['product_name']) ?></td><td><?= (int) $item['quantity'] ?></td><td><?= html_escape($item['unit_price']) ?></td><td><?= html_escape($item['line_total']) ?></td>
</tr><?php endforeach; ?></tbody></table></section>
<section class="card totals"><p><span>المجموع الفرعي</span><strong><?= html_escape($invoice['subtotal']) ?></strong></p>
<p><span>الخصم (<?= html_escape($invoice['discount_percent']) ?>%)</span><strong><?= html_escape($invoice['discount_amount']) ?></strong></p>
<p><span>الإجمالي النهائي</span><strong><?= html_escape($invoice['total']) ?></strong></p></section>
</main></body></html>
