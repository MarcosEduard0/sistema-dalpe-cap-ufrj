document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var dialog = document.getElementById('docs-delete-dialog');
    var deleteForm = document.getElementById('docs-delete-form');
    document.querySelectorAll('[data-delete-url]').forEach(function (button) { button.disabled = false; });
    document.getElementById('documentos-table').addEventListener('click', function (event) {
        var button = event.target.closest('[data-delete-url]');
        if (!button) return;
        document.getElementById('docs-delete-name').textContent = button.dataset.documentName;
        deleteForm.action = button.dataset.deleteUrl;
        dialog.showModal();
        document.getElementById('docs-delete-cancel').focus();
    });
    document.getElementById('docs-delete-cancel').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (event) {
        var box = dialog.getBoundingClientRect();
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
    });

    if (!window.jQuery || !jQuery.fn.DataTable) return;
    var table = jQuery('#documentos-table').DataTable({
        dom: 't', pageLength: 10, lengthChange: false, autoWidth: false,
        order: [[0, 'asc']],
        columnDefs: [{targets: 2, orderable: false, searchable: false}, {targets: 1, type: 'num'}],
        language: {
            emptyTable: 'Nenhum modelo cadastrado. Use Adicionar documento para começar.',
            zeroRecords: 'Nenhum documento encontrado para os filtros selecionados.',
            aria: {sortAscending: ': ordenar em ordem crescente', sortDescending: ': ordenar em ordem decrescente'}
        }
    });
    var search = document.getElementById('docs-search');
    var filter = document.getElementById('docs-filter');
    var previous = document.getElementById('docs-previous');
    var next = document.getElementById('docs-next');
    search.disabled = false;
    filter.disabled = false;
    search.addEventListener('input', function () { table.search(search.value).draw(); });
    filter.addEventListener('change', function () { table.column(1).search(filter.value, false, false).draw(); });
    previous.addEventListener('click', function () { table.page('previous').draw('page'); });
    next.addEventListener('click', function () { table.page('next').draw('page'); });
    function update() {
        var info = table.page.info();
        var label = info.recordsDisplay ? 'Exibindo ' + (info.start + 1) + '–' + info.end + ' de ' + info.recordsDisplay + ' documentos' : 'Nenhum documento encontrado';
        if (info.recordsDisplay !== info.recordsTotal) label += ' (' + info.recordsTotal + ' no total)';
        document.getElementById('docs-results').textContent = label;
        previous.disabled = info.page <= 0;
        next.disabled = info.page >= info.pages - 1;
        var pages = document.getElementById('docs-pages');
        pages.replaceChildren();
        var start = Math.max(0, Math.min(info.page - 2, info.pages - 5));
        for (let page = start; page < Math.min(info.pages, start + 5); page++) {
            var button = document.createElement('button');
            button.type = 'button';
            button.textContent = page + 1;
            button.setAttribute('aria-label', 'Página ' + (page + 1));
            if (page === info.page) button.setAttribute('aria-current', 'page');
            button.addEventListener('click', function () { table.page(page).draw('page'); });
            pages.appendChild(button);
        }
    }
    table.on('draw', update);
    update();
});
