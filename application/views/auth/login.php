<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>تسجيل الدخول | SMART LIFE</title>
<?php $this->load->view('partials/assets'); ?>
<style>
.password-field{position:relative}
.password-field input{padding-inline-end:54px}
.password-toggle{position:absolute;inset-inline-end:5px;top:50%;transform:translateY(-50%);margin:0;padding:8px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:transparent;color:#52637a}
.password-toggle:hover{background:#edf2f8}
.password-toggle[hidden]{display:none}
.password-toggle:focus-visible{outline:2px solid #2458a6;outline-offset:1px}
.password-toggle svg{width:24px;height:24px}
</style></head><body class="auth-page"><main>
<div class="brand company-brand"><span class="brand-logo"><img src="<?= html_escape(base_url('assets/images/smart-life-logo.png')) ?>" alt="SMART LIFE" width="1024" height="557"></span><small>إدارة المخزون والمبيعات</small></div>
<h1>مرحبًا بعودتك</h1><p class="hint">سجّل الدخول للوصول إلى المنتجات والمخزون والفواتير.</p>
<?php if ($error): ?><p class="error" role="alert"><?= html_escape($error) ?></p><?php endif; ?>
<?= form_open('auth/login') ?>
<label for="username">اسم المستخدم</label><input id="username" name="username" required maxlength="80" autocomplete="username" value="<?= html_escape($username) ?>">
<label for="password">كلمة المرور</label>
<div class="password-field">
    <input id="password" name="password" type="password" required autocomplete="current-password">
    <button id="password-toggle" class="password-toggle" type="button" aria-label="إظهار كلمة المرور" title="إظهار كلمة المرور" aria-controls="password" aria-pressed="false" hidden>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
            <path id="password-eye-slash" d="m3 3 18 18" style="display:none"/>
        </svg>
    </button>
</div>
<button type="submit">دخول</button>
<?= form_close() ?>
</main>
<script>
(function () {
    var password = document.getElementById('password');
    var toggle = document.getElementById('password-toggle');
    var slash = document.getElementById('password-eye-slash');
    toggle.hidden = false;
    slash.style.display = 'none';
    toggle.addEventListener('click', function () {
        var visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        var label = visible ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور';
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
        toggle.setAttribute('aria-pressed', String(visible));
        slash.style.display = visible ? '' : 'none';
    });
}());
</script>
</body></html>
