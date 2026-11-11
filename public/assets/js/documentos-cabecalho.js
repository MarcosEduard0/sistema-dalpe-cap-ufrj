(function () {
    'use strict';
    var form = document.getElementById('header-settings-form');
    var cards = Array.from(form.querySelectorAll('.header-image-card'));
    var add = document.getElementById('header-add-image');
    var preview = document.getElementById('header-preview-images');
    var states = cards.map(function (card) {
        return {card: card, original: card.dataset.originalSrc, src: card.dataset.originalSrc, file: null, url: null, removed: false, loading: false, version: 0};
    });
    function release(state) { if (state.url) URL.revokeObjectURL(state.url); state.url = null; }
    function error(state, message) {
        var node = state.card.querySelector('.header-file-error');
        node.textContent = message;
        node.hidden = !message;
        state.card.querySelector('.header-file-input').setCustomValidity(message);
    }
    function render() {
        var count = 0;
        var changes = 0;
        preview.replaceChildren();
        states.forEach(function (state) {
            var card = state.card;
            var image = card.querySelector('.header-image-preview');
            var active = !!state.src && !state.removed;
            card.classList.toggle('is-removed', state.removed);
            image.hidden = !state.src;
            if (state.src) image.src = state.src;
            else image.removeAttribute('src');
            card.querySelector('.header-image-empty').hidden = !!state.src;
            card.querySelector('.header-delete-checkbox').checked = state.removed;
            card.querySelector('.header-remove-image').disabled = !state.src || state.removed;
            card.querySelector('.header-undo-remove').hidden = !state.removed;
            card.querySelector('.header-image-status').textContent = state.loading ? 'Conferindo imagem...' : (state.removed ? 'Será removida ao salvar' : (state.file ? 'Nova imagem selecionada' : (state.src ? 'Imagem atual' : 'Posição disponível')));
            if (state.removed || state.file) changes++;
            if (active) {
                count++;
                var copy = document.createElement('img');
                copy.src = state.src;
                copy.alt = 'Posição ' + card.dataset.slot;
                preview.appendChild(copy);
            }
        });
        document.getElementById('header-images-count').textContent = count + '/3';
        document.getElementById('header-preview-empty').hidden = count > 0;
        document.getElementById('header-change-status').textContent = changes ? changes + (changes === 1 ? ' posição alterada. Salve para aplicar.' : ' posições alteradas. Salve para aplicar.') : 'Nenhuma alteração pendente.';
        add.disabled = count === 3;
        add.title = count === 3 ? 'As três posições estão ocupadas. Use o lápis para substituir uma imagem.' : 'Selecionar imagem para uma posição disponível';
        form.querySelector('button[type=submit]').disabled = states.some(function (state) { return state.loading; });
    }
    states.forEach(function (state) {
        var input = state.card.querySelector('.header-file-input');
        state.card.querySelector('.header-pick-file').addEventListener('click', function () { input.click(); });
        state.card.querySelector('.header-remove-image').addEventListener('click', function () {
            state.version++; release(state); input.value = ''; state.file = null;
            state.src = state.original; state.removed = true; state.loading = false;
            error(state, ''); render();
        });
        state.card.querySelector('.header-undo-remove').addEventListener('click', function () { state.removed = false; render(); });
        input.addEventListener('change', function () {
            var version = ++state.version;
            release(state); state.file = null; state.src = state.original; state.removed = false; state.loading = false;
            error(state, '');
            var file = input.files[0];
            if (!file) { render(); return; }
            if (file.size > 2097152 || !/\.(jpg|jpeg|png)$/i.test(file.name) || !['image/jpeg', 'image/png'].includes(file.type)) {
                input.value = ''; error(state, 'Selecione uma imagem JPG, JPEG ou PNG de até 2 MB.'); render(); return;
            }
            state.loading = true;
            var url = URL.createObjectURL(file);
            state.url = url;
            var image = new Image();
            image.onload = function () {
                if (state.version !== version) return;
                state.loading = false; state.src = url; state.file = file; render();
            };
            image.onerror = function () {
                if (state.version !== version) return;
                state.loading = false; input.value = ''; release(state);
                error(state, 'Não foi possível abrir essa imagem. Selecione outro arquivo.'); render();
            };
            image.src = url;
            render();
        });
    });
    add.addEventListener('click', function () {
        var available = states.find(function (state) { return !state.src || state.removed; });
        if (available) available.card.querySelector('.header-file-input').click();
    });
    form.addEventListener('reset', function () {
        setTimeout(function () {
            states.forEach(function (state) {
                state.version++; release(state); state.src = state.original;
                state.file = null; state.removed = false; state.loading = false; error(state, '');
            });
            render();
        }, 0);
    });
    window.addEventListener('pagehide', function () { states.forEach(release); });
    render();
}());
