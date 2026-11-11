document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var password = document.getElementById('login-password');
    var toggle = document.getElementById('login-show-password');
    toggle.hidden = false;
    toggle.addEventListener('click', function () {
        var visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-label', visible ? 'Ocultar senha' : 'Mostrar senha');
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.querySelector('i').className = visible ? 'bx bx-hide' : 'bx bx-show';
    });
    var user = document.getElementById('login-user');
    var remember = document.getElementById('login-remember');
    var storageKey = 'dalpe.login.usuario';
    try {
        var saved = localStorage.getItem(storageKey);
        if (saved) { user.value = saved; remember.checked = true; }
        document.getElementById('login-remember-label').hidden = false;
    } catch (error) { /* Login remains usable when storage is unavailable. */ }
    remember.addEventListener('change', function () {
        if (!remember.checked) { try { localStorage.removeItem(storageKey); } catch (error) {} }
    });
    document.getElementById('login-form').addEventListener('submit', function () {
        try {
            if (remember.checked) localStorage.setItem(storageKey, user.value);
            else localStorage.removeItem(storageKey);
        } catch (error) {}
    });
});
