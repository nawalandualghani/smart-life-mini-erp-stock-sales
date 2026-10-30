<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="actions">
    <a class="reset" href="<?= html_escape(site_url('stock/export_low_stock') . '?' . http_build_query(array('q' => $search, 'warehouse_id' => $warehouse_id))) ?>">تصدير المخزون المنخفض CSV</a>
    <span class="hint">يشمل جميع الصفحات حسب البحث والمستودع المطبّقين، للمنتجات عند حد التنبيه أو أقل.</span>
</div>
        <section class="card" aria-label="كميات المخزون">
            <div class="summary">عدد النتائج: <?= (int) $total ?> — المعروض: <?= count($rows) ?></div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th scope="col">الكود</th><th scope="col">المنتج</th><th scope="col">المستودع</th>
                        <th scope="col">الكمية</th><th scope="col">حد التنبيه</th><th scope="col">مقدار النقص</th><th scope="col">حالة المخزون</th>
                        <th scope="col">الإجراءات</th>
                    </tr></thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="empty">لا توجد كميات مخزون مطابقة.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php $is_low = (int) $row['quantity'] <= (int) $row['alert_quantity']; ?>
                            <tr data-stock-row>
                                <td><span class="code"><?= html_escape($row['code']) ?></span></td>
                                <td><?= html_escape($row['name']) ?><?= (int) $row['is_active'] === 0 ? ' (معطّل)' : '' ?></td>
                                <td><?= html_escape($row['warehouse_name']) ?></td>
                                <td><?= (int) $row['quantity'] ?></td>
                                <td><?= (int) $row['alert_quantity'] ?></td>
                                <td><?= max(0, (int) $row['alert_quantity'] - (int) $row['quantity']) ?></td>
                                <td><span class="badge <?= $is_low ? 'low' : 'normal' ?>"><?= $is_low ? 'عند حد التنبيه أو أقل' : 'فوق حد التنبيه' ?></span></td>
                                <td><?php if ($current_user['role'] === 'admin'): ?><a href="<?= html_escape(site_url('stock/manage') . '?' . http_build_query(array('warehouse_id' => $row['warehouse_id'], 'product_id' => $row['product_id']))) ?>">تعديل الكمية</a><?php else: ?>عرض فقط<?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
<?php $this->load->view('partials/pagination'); ?>
