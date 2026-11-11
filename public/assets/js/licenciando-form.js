(function () {
    'use strict';
    function associarCampos() {
        var form = document.getElementById('licenciando-form');
        if (!form) return;
        form.querySelectorAll('.form-group').forEach(function (group, index) {
            var label = group.querySelector('label');
            var input = group.querySelector('input:not([type="hidden"]), select, textarea');
            if (!label || !input || label.contains(input)) return;
            if (!input.id || form.querySelectorAll('[id="' + input.id + '"]').length > 1) input.id = 'licenciando-campo-' + index;
            label.htmlFor = input.id;
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        associarCampos();
        var lista = document.getElementById('divSetor');
        if (lista) new MutationObserver(associarCampos).observe(lista, {childList: true});
    });
}());
