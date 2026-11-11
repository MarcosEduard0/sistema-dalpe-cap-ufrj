(function () {
    'use strict';
    var texto = document.getElementById('conteudo');
    var nome = document.getElementById('doc-nome');
    var modo = document.getElementById('doc-preview-mode');
    var exemplos = JSON.parse(document.getElementById('doc-preview-data').textContent);
    var aviso = document.getElementById('doc-preview-warning');
    var dialog = document.getElementById('doc-preview-dialog');
    var conhecido = function (chave) { return Object.prototype.hasOwnProperty.call(exemplos, chave); };

    function marcador(chave, desconhecido) {
        var span = document.createElement('strong');
        span.className = desconhecido ? 'doc-token doc-token-unknown' : 'doc-token';
        span.textContent = '[' + chave + ']';
        return span;
    }

    function atualizar() {
        var fragmento = document.createDocumentFragment();
        var desconhecidas = new Set();
        if (!texto.value.trim()) {
            var vazio = document.createElement('p');
            vazio.className = 'doc-preview-empty';
            vazio.textContent = 'Digite o conteúdo para ver a prévia.';
            fragmento.appendChild(vazio);
        } else {
            texto.value.replace(/\r\n/g, '\n').split('[PARAGRAFO]').forEach(function (trecho, indice) {
                if (!trecho.trim() && indice === 0) return;
                var paragrafo = document.createElement('p');
                if (indice > 0) paragrafo.className = 'doc-paragraph-indent';
                if (indice > 0 && modo.value === 'variaveis') paragrafo.appendChild(marcador('PARAGRAFO', false));
                var regex = /\[([^\]\r\n]+)\]/g;
                var posicao = 0;
                var item;
                trecho = trecho.trim();
                while ((item = regex.exec(trecho)) !== null) {
                    paragrafo.appendChild(document.createTextNode(trecho.slice(posicao, item.index)));
                    var chave = item[1];
                    if (!conhecido(chave)) {
                        desconhecidas.add(chave);
                        paragrafo.appendChild(marcador(chave, true));
                    } else if (modo.value === 'variaveis') {
                        paragrafo.appendChild(marcador(chave, false));
                    } else {
                        paragrafo.appendChild(document.createTextNode(exemplos[chave]));
                    }
                    posicao = regex.lastIndex;
                }
                paragrafo.appendChild(document.createTextNode(trecho.slice(posicao)));
                fragmento.appendChild(paragrafo);
            });
        }
        document.querySelectorAll('.js-preview-content').forEach(function (destino) {
            destino.replaceChildren(fragmento.cloneNode(true));
        });
        document.querySelectorAll('.js-preview-name').forEach(function (titulo) {
            titulo.textContent = nome.value.trim() || 'Título do documento';
        });
        aviso.hidden = desconhecidas.size === 0;
        aviso.textContent = desconhecidas.size ? 'Marcadores não reconhecidos: ' + Array.from(desconhecidas).map(function (chave) { return '[' + chave + ']'; }).join(', ') + '. Confira a lista de variáveis disponíveis.' : '';
        document.getElementById('doc-preview-notice').textContent = modo.value === 'exemplo'
            ? 'Dados fictícios apenas para conferência. Nenhum cadastro real é utilizado.'
            : 'Marcadores do modelo, sem substituir por dados de exemplo.';
    }

    texto.addEventListener('input', atualizar);
    nome.addEventListener('input', atualizar);
    modo.addEventListener('change', atualizar);
    document.querySelectorAll('[data-variable]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var token = '[' + botao.dataset.variable + ']';
            texto.setRangeText(token, texto.selectionStart, texto.selectionEnd, 'end');
            texto.focus();
            texto.dispatchEvent(new Event('input', {bubbles: true}));
            document.getElementById('doc-insert-status').textContent = token + ' inserido no conteúdo.';
        });
    });
    document.querySelectorAll('[data-preview-expand]').forEach(function (botao) {
        botao.addEventListener('click', function () { atualizar(); dialog.showModal(); });
    });
    document.getElementById('doc-preview-close').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (evento) {
        var bounds = dialog.getBoundingClientRect();
        if (evento.clientX < bounds.left || evento.clientX > bounds.right || evento.clientY < bounds.top || evento.clientY > bounds.bottom) dialog.close();
    });
    atualizar();
}());
