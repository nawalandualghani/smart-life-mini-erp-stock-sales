<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($pages > 1): ?>
<nav class="pagination" aria-label="صفحات النتائج">
    <?php if ($page > 1): ?>
        <a rel="prev" href="<?= html_escape($listing_url . '?' . http_build_query(array_merge($filters, array('page' => $page - 1)))) ?>">السابق</a>
    <?php else: ?>
        <span class="reset" aria-disabled="true" style="opacity:.45">السابق</span>
    <?php endif; ?>
    <?php $numbers = array_unique(array_merge(array(1), range(max(1, $page - 2), min($pages, $page + 2)), array($pages))); $previous = 0; ?>
    <?php foreach ($numbers as $number): ?>
        <?php if ($previous && $number > $previous + 1): ?><span aria-hidden="true">…</span><?php endif; ?>
        <?php if ($number === $page): ?>
            <span class="current-page" aria-current="page" aria-label="الصفحة الحالية <?= (int) $page ?>"><?= (int) $page ?></span>
        <?php else: ?>
            <a aria-label="صفحة <?= (int) $number ?>" href="<?= html_escape($listing_url . '?' . http_build_query(array_merge($filters, array('page' => $number)))) ?>"><?= (int) $number ?></a>
        <?php endif; ?>
        <?php $previous = $number; ?>
    <?php endforeach; ?>
    <?php if ($page < $pages): ?>
        <a rel="next" href="<?= html_escape($listing_url . '?' . http_build_query(array_merge($filters, array('page' => $page + 1)))) ?>">التالي</a>
    <?php else: ?>
        <span class="reset" aria-disabled="true" style="opacity:.45">التالي</span>
    <?php endif; ?>
    <span class="hint">صفحة <?= (int) $page ?> من <?= (int) $pages ?> · <?= (int) $per_page ?> نتيجة لكل صفحة</span>
</nav>
<?php endif; ?>
