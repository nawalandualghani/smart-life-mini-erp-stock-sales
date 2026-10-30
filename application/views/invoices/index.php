<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>الفواتير | SMART LIFE</title><?php $this->load->view('partials/assets'); ?>
</head><body><main>
<?php $this->load->view('partials/account'); ?>
<nav class="nav"><a href="<?= html_escape(site_url('products')) ?>">المنتجات</a><a href="<?= html_escape(site_url('stock')) ?>">المخزون</a></nav>
<h1>فواتير البيع</h1><div class="actions"><a class="button" href="<?= html_escape(site_url('invoices/create')) ?>">فاتورة جديدة</a><a class="reset" href="<?= html_escape(site_url('invoices/export')) ?>">تصدير كل الفواتير CSV</a></div>
<p class="hint">التصدير يشمل جميع الفواتير المتاحة لك. لطباعة فاتورة، افتح «عرض وطباعة» بجانبها.</p>
<section class="card table-wrap"><table><thead><tr><th>الرقم</th><th>العميل</th><th>المستودع</th><th>أنشأها</th><th>التاريخ</th><th>الإجمالي</th><th>الإجراءات</th></tr></thead><tbody>
<?php if (!$invoices): ?><tr><td colspan="7">لا توجد فواتير حتى الآن.</td></tr><?php endif; ?>
<?php foreach ($invoices as $invoice): ?><tr>
<td><a href="<?= html_escape(site_url('invoices/show/' . $invoice['id'])) ?>">#<?= (int) $invoice['id'] ?></a></td>
<td><?= html_escape($invoice['customer_name']) ?></td><td><?= html_escape($invoice['warehouse_name']) ?></td><td><?= html_escape($invoice['creator_name']) ?></td>
<td><?= html_escape($invoice['created_at']) ?></td><td><?= html_escape($invoice['total']) ?></td>
<td><div class="row-actions"><a href="<?= html_escape(site_url('invoices/show/' . $invoice['id'])) ?>">عرض وطباعة</a><a href="<?= html_escape(site_url('invoices/export/' . $invoice['id'])) ?>">تصدير CSV</a></div></td>
</tr><?php endforeach; ?></tbody></table></section>
<?php if ($pages > 1): ?><nav class="nav" aria-label="صفحات الفواتير">
<?php if ($page > 1): ?><a href="<?= html_escape(site_url('invoices') . '?page=' . ($page - 1)) ?>">السابق</a><?php endif; ?>
<span>صفحة <?= (int) $page ?> من <?= (int) $pages ?></span>
<?php if ($page < $pages): ?><a href="<?= html_escape(site_url('invoices') . '?page=' . ($page + 1)) ?>">التالي</a><?php endif; ?>
</nav><?php endif; ?>
</main></body></html>
