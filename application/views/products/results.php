<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
        <section class="card" aria-label="قائمة المنتجات">
            <div class="summary">عدد النتائج: <?= (int) $total ?> — المعروض: <?= count($products) ?></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">الكود</th>
                            <th scope="col">الاسم</th>
                            <th scope="col">التصنيف</th>
                            <th scope="col">سعر البيع</th>
                            <th scope="col">الحالة</th>
                            <th scope="col">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" class="empty">لا توجد منتجات مطابقة.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><span class="code"><?= html_escape($product['code']) ?></span></td>
                                <td><?= html_escape($product['name']) ?></td>
                                <td><?= html_escape($product['category_name']) ?></td>
                                <td><span class="price"><?= number_format((float) $product['selling_price'], 2) ?></span></td>
                                <td>
                                    <?php if ((int) $product['is_active'] === 1): ?>
                                        <span class="badge active">مفعّل</span>
                                    <?php else: ?>
                                        <span class="badge inactive">معطّل</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <?php if ($current_user['role'] === 'admin'): ?>
                                        <a href="<?= html_escape(site_url('products/edit/' . (int) $product['id'])) ?>" aria-label="<?= html_escape('تعديل ' . $product['name']) ?>">تعديل</a>
                                        <a href="<?= html_escape(site_url('stock/manage') . '?product_id=' . (int) $product['id']) ?>">تحديد المخزون</a>
                                        <?= form_open('products/set_status/' . (int) $product['id'], (int) $product['is_active'] === 1 ? array('data-confirm' => 'هل تريد تعطيل هذا المنتج؟ سيبقى سجله محفوظًا ولن يتاح للبيع الجديد.') : array()) ?>
                                            <input type="hidden" name="is_active" value="<?= (int) $product['is_active'] === 1 ? '0' : '1' ?>">
                                            <button class="<?= (int) $product['is_active'] === 1 ? 'danger' : 'secondary' ?>" type="submit" aria-label="<?= html_escape(((int) $product['is_active'] === 1 ? 'تعطيل ' : 'تفعيل ') . $product['name']) ?>"><?= (int) $product['is_active'] === 1 ? 'تعطيل' : 'تفعيل' ?></button>
                                        <?= form_close() ?>
                                        <?php else: ?>
                                            <span>عرض فقط</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
<?php $this->load->view('partials/pagination'); ?>
