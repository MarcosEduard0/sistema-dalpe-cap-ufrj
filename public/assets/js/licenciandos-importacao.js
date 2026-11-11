(function () {
    'use strict';
    var input = document.getElementById('userfile');
    var drop = document.getElementById('import-dropzone');
    var advance = document.getElementById('import-advance');
    var error = document.getElementById('import-client-error');
    var version = 0;
    function sample(text, separator) {
        var rows = [], row = [], field = '', quoted = false;
        for (var i = 0; i < text.length; i++) {
            var char = text[i];
            if (char === '"') {
                if (quoted && text[i + 1] === '"') { field += '"'; i++; }
                else quoted = !quoted;
            } else if (!quoted && char === separator) { row.push(field); field = ''; }
            else if (!quoted && char === '\n') {
                row.push(field); if (row.some(function (cell) { return cell.trim(); })) rows.push(row);
                row = []; field = ''; if (rows.length === 6) return rows;
            } else field += char;
        }
        if (quoted) throw new Error('Há um campo com aspas sem fechamento no CSV.');
        row.push(field); if (row.some(function (cell) { return cell.trim(); })) rows.push(row);
        return rows;
    }
    function clear() {
        version++; input.value = ''; input.setCustomValidity(''); advance.disabled = true;
        document.getElementById('import-file-selected').hidden = true;
        document.getElementById('import-preview').hidden = true;
        document.getElementById('import-preview-empty').hidden = false;
        document.getElementById('import-preview-body').replaceChildren(); error.hidden = true; error.textContent = '';
    }
    function fail(message) { clear(); error.textContent = message; error.hidden = false; }
    document.getElementById('import-pick').addEventListener('click', function () { input.click(); });
    document.getElementById('import-clear-file').addEventListener('click', clear);
    input.addEventListener('change', async function () {
        var token = ++version;
        var file = input.files[0]; advance.disabled = true; error.hidden = true;
        document.getElementById('import-preview').hidden = true;
        document.getElementById('import-preview-empty').hidden = false;
        if (!file) { clear(); return; }
        if (!/\.csv$/i.test(file.name) || file.size > 10485760) { fail('Selecione um arquivo CSV de até 10 MB.'); return; }
        try {
            var bytes = await file.arrayBuffer();
            if (token !== version) return;
            var text;
            try { text = new TextDecoder('utf-8', {fatal: true}).decode(bytes).replace(/\r\n?/g, '\n'); }
            catch (_) { throw new Error('Salve o arquivo como CSV UTF-8 e selecione novamente.'); }
            var rows = null;
            for (var separator of [';', ',']) {
                var attempt = sample(text, separator);
                if (attempt[0] && attempt[0].length === 13) { rows = attempt; break; }
            }
            if (!rows || rows.length < 2) throw new Error('Use um CSV com as 13 colunas do modelo e pelo menos um registro.');
            if (rows.slice(1).some(function (row) { return row.length !== 13; })) throw new Error('Há um registro na amostra com quantidade diferente de 13 colunas. Confira o separador e as aspas.');
            var body = document.getElementById('import-preview-body'); body.replaceChildren();
            rows.slice(1, 6).forEach(function (row) {
                var tr = document.createElement('tr');
                [1, 3, 4, 6].forEach(function (index) { var td = document.createElement('td'); td.textContent = row[index]; tr.appendChild(td); });
                body.appendChild(tr);
            });
            document.getElementById('import-file-selected').hidden = false;
            document.getElementById('import-file-name').textContent = file.name;
            document.getElementById('import-file-size').textContent = new Intl.NumberFormat('pt-BR', {maximumFractionDigits: 1}).format(file.size / 1024) + ' KB';
            document.getElementById('import-preview').hidden = false;
            document.getElementById('import-preview-empty').hidden = true;
            advance.disabled = false;
        } catch (exception) { fail(exception.message || 'Não foi possível ler o arquivo.'); }
    });
    ['dragenter', 'dragover'].forEach(function (type) { drop.addEventListener(type, function (event) { event.preventDefault(); drop.classList.add('is-dragging'); }); });
    ['dragleave', 'drop'].forEach(function (type) { drop.addEventListener(type, function (event) { event.preventDefault(); drop.classList.remove('is-dragging'); }); });
    drop.addEventListener('drop', function (event) {
        if (event.dataTransfer.files.length !== 1) { fail('Selecione apenas um arquivo CSV por vez.'); return; }
        input.files = event.dataTransfer.files; input.dispatchEvent(new Event('change', {bubbles: true}));
    });
    document.getElementById('import-upload-form').addEventListener('submit', function (event) {
        if (!input.files.length || advance.disabled) { event.preventDefault(); fail('Selecione um arquivo CSV válido antes de avançar.'); }
    });
}());
