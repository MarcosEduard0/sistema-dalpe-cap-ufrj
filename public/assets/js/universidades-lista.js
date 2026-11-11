document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var dialog = document.getElementById('uni-delete-dialog');
    document.querySelectorAll('[data-delete-url]').forEach(function (button) { button.disabled = false; });
    document.getElementById('universidades-table').addEventListener('click', function (event) {
        var button = event.target.closest('[data-delete-url]');
        if (!button) return;
        document.getElementById('uni-delete-name').textContent = button.dataset.universityName;
        document.getElementById('uni-delete-form').action = button.dataset.deleteUrl;
        dialog.showModal(); document.getElementById('uni-delete-cancel').focus();
    });
    document.getElementById('uni-delete-cancel').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (event) {
        var box = dialog.getBoundingClientRect();
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
    });
    if (!window.jQuery || !jQuery.fn.DataTable) return;
    var table = jQuery('#universidades-table').DataTable({
        dom: 't', pageLength: 10, autoWidth: false, order: [[0, 'asc']],
        columnDefs: [{targets: 4, orderable: false, searchable: false}, {targets: [2, 3], searchable: false}, {targets: 2, type: 'num'}],
        language: {emptyTable: 'Nenhuma universidade cadastrada.', zeroRecords: 'Nenhuma universidade encontrada para a pesquisa.', aria: {sortAscending: ': ordenar em ordem crescente', sortDescending: ': ordenar em ordem decrescente'}}
    });
    var search = document.getElementById('uni-search'); search.disabled = false;
    var size = document.getElementById('uni-page-size'); size.disabled = false;
    search.addEventListener('input', function () { table.search(search.value).draw(); });
    size.addEventListener('change', function () { table.page.len(Number(size.value)).draw(); });
    var previous = document.getElementById('uni-previous'); var next = document.getElementById('uni-next');
    previous.addEventListener('click', function () { table.page('previous').draw('page'); });
    next.addEventListener('click', function () { table.page('next').draw('page'); });
    var number = new Intl.NumberFormat('pt-BR');
    function update() {
        var info = table.page.info();
        var text = info.recordsDisplay ? 'Mostrando ' + number.format(info.start + 1) + '–' + number.format(info.end) + ' de ' + number.format(info.recordsDisplay) + ' universidades' : 'Nenhuma universidade encontrada';
        if (info.recordsDisplay !== info.recordsTotal) text += ' (' + number.format(info.recordsTotal) + ' no total)';
        document.getElementById('uni-results').textContent = text;
        previous.disabled = info.page === 0; next.disabled = info.page >= info.pages - 1;
        var pages = document.getElementById('uni-pages'); pages.replaceChildren();
        var start = Math.max(0, Math.min(info.page - 2, info.pages - 5));
        for (let page = start; page < Math.min(info.pages, start + 5); page++) {
            var button = document.createElement('button'); button.type = 'button'; button.textContent = page + 1;
            button.setAttribute('aria-label', 'Página ' + (page + 1));
            if (page === info.page) button.setAttribute('aria-current', 'page');
            button.addEventListener('click', function () { table.page(page).draw('page'); }); pages.appendChild(button);
        }
    }
    table.on('draw', update); update();
});
