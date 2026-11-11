(function () {
    'use strict';
    var form = document.getElementById('usuario-editor-form');
    [['user-nome', 'user-summary-name', 'Novo usuário'], ['user-login', 'user-summary-login', 'Preencha os dados de acesso'], ['user-cargo', 'user-summary-job', 'Cargo não informado']].forEach(function (item) {
        var input = document.getElementById(item[0]);
        input.addEventListener('input', function () { document.getElementById(item[1]).textContent = input.value.trim() || item[2]; });
    });
    document.querySelectorAll('.user-toggle-password').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('aria-controls'));
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', (show ? 'Ocultar' : 'Mostrar') + (input.name === 'senha1' ? ' senha' : ' confirmação de senha'));
            button.querySelector('i').className = show ? 'bx bx-hide' : 'bx bx-show';
        });
    });
    var password = document.getElementById('user-senha1');
    var confirm = document.getElementById('user-senha2');
    var requiredInitially = password.required;
    function checkPasswords() {
        var any = !!password.value || !!confirm.value;
        password.required = requiredInitially || any;
        confirm.required = requiredInitially || any;
        confirm.setCustomValidity(any && password.value !== confirm.value ? 'As senhas precisam ser iguais.' : '');
    }
    password.addEventListener('input', checkPasswords);
    confirm.addEventListener('input', checkPasswords);

    var preview = document.querySelector('.user-signature-preview');
    var original = preview.dataset.originalSrc;
    var image = document.getElementById('user-signature-image');
    var input = document.getElementById('user-assinatura');
    var remove = document.getElementById('deleteAssinatura');
    var removeButton = document.getElementById('user-signature-remove');
    var hasOriginal = !remove.disabled;
    var src = original;
    var file = null;
    var url = null;
    var version = 0;
    function release() { if (url) URL.revokeObjectURL(url); url = null; }
    function error(message) {
        var node = document.getElementById('user-signature-error');
        node.textContent = message;
        node.hidden = !message;
        input.setCustomValidity(message);
    }
    function render() {
        image.hidden = !src;
        if (src) image.src = src;
        else image.removeAttribute('src');
        document.getElementById('user-signature-empty').hidden = !!src;
        preview.classList.toggle('is-removed', remove.checked);
        document.getElementById('user-signature-removed').hidden = !remove.checked;
        removeButton.disabled = !hasOriginal && !file && !input.validationMessage;
        removeButton.querySelector('span').textContent = remove.checked ? 'Desfazer' : 'Remover';
        document.getElementById('user-signature-status').textContent = remove.checked ? 'A assinatura será removida ao salvar.' : (file ? 'Nova assinatura selecionada. Salve para aplicar.' : 'As alterações da assinatura serão aplicadas ao salvar.');
    }
    function clearFile() { version++; release(); input.value = ''; file = null; src = original; error(''); form.querySelector('button[type=submit]').disabled = false; }
    document.getElementById('user-signature-pick').addEventListener('click', function () { input.click(); });
    removeButton.addEventListener('click', function () { clearFile(); remove.checked = hasOriginal && !remove.checked; render(); });
    remove.addEventListener('change', function () { clearFile(); render(); });
    input.addEventListener('change', function () {
        var candidate = input.files[0];
        var token = ++version;
        release(); file = null; src = original; remove.checked = false; error('');
        form.querySelector('button[type=submit]').disabled = false;
        if (!candidate) { render(); return; }
        if (candidate.size > 4194304 || !/\.(jpg|jpeg|png)$/i.test(candidate.name) || !['image/jpeg', 'image/png'].includes(candidate.type)) {
            input.value = ''; error('Selecione uma imagem JPG, JPEG ou PNG de até 4 MB.'); render(); return;
        }
        url = URL.createObjectURL(candidate);
        var next = new Image();
        form.querySelector('button[type=submit]').disabled = true;
        next.onload = function () {
            if (version !== token) return;
            src = url; file = candidate; form.querySelector('button[type=submit]').disabled = false; render();
        };
        next.onerror = function () {
            if (version !== token) return;
            input.value = ''; release(); error('Não foi possível abrir essa imagem. Selecione outro arquivo.');
            form.querySelector('button[type=submit]').disabled = false; render();
        };
        next.src = url;
        render();
    });
    window.addEventListener('pagehide', release);
    render();
}());
