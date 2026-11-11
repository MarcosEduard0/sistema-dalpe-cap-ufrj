(function () {
    'use strict';
    function atualizar(grupo) {
        var campo = grupo.querySelector('.js-situacao-setor');
        var termino = grupo.querySelector('input[name^="data_termino"]');
        if (!campo || !termino) return;
        var select = campo.querySelector('.js-situacao-select');
        var manual = campo.querySelector('.js-situacao-manual');
        var valor = termino.value;
        var data = new Date(valor + 'T00:00:00Z');
        var valida = /^\d{4}-\d{2}-\d{2}$/.test(valor) && !isNaN(data.getTime()) && data.toISOString().slice(0, 10) === valor && valor >= '1000-01-01';
        var concluido = valida && valor <= campo.dataset.hoje;
        select.disabled = concluido;
        manual.disabled = !concluido;
        select.value = concluido ? 'Concluido' : manual.value;
        campo.querySelector('.js-situacao-aviso').hidden = !concluido;
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-vinculo').forEach(atualizar);
        document.addEventListener('change', function (evento) {
            var grupo = evento.target.closest('.js-vinculo');
            if (!grupo) return;
            if (evento.target.matches('.js-situacao-select') && !evento.target.disabled) {
                grupo.querySelector('.js-situacao-manual').value = evento.target.value;
            }
            atualizar(grupo);
        });
        document.addEventListener('input', function (evento) {
            if (evento.target.matches('input[name^="data_termino"]')) {
                var grupo = evento.target.closest('.js-vinculo');
                if (grupo) atualizar(grupo);
            }
        });
        var lista = document.getElementById('divSetor');
        if (lista) new MutationObserver(function () {
            lista.querySelectorAll('.js-vinculo').forEach(atualizar);
        }).observe(lista, {childList: true});
    });
}());
