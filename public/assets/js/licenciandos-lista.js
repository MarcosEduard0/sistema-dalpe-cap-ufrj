document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var element = document.getElementById('licenciandos-table');
    var metadata = new WeakMap();
    element.querySelectorAll('tbody tr[data-student]').forEach(function (row) { metadata.set(row, JSON.parse(row.dataset.student)); });
    element.querySelectorAll('.lic-open-summary, [data-delete-url]').forEach(function (button) { button.disabled = false; });
    var labels = {Ativo: 'Ativo', Inativo: 'Inativo', Pendente: 'Pendente', Concluido: 'Concluído', nao_informado: 'Não informado'};
    var summary = document.getElementById('lic-summary-dialog');
    var deletion = document.getElementById('lic-delete-dialog');
    function linkDescription(link) { return link.nome + (link.periodo ? ' (' + link.periodo + ')' : '') + ': ' + labels[link.situacao]; }
    element.addEventListener('click', function (event) {
        var button = event.target.closest('.lic-open-summary, [data-delete-url]');
        if (!button) return;
        var row = button.closest('tr');
        var person = metadata.get(row);
        if (!person) return;
        if (button.hasAttribute('data-delete-url')) {
            document.getElementById('lic-delete-name').textContent = person.nome;
            document.getElementById('lic-delete-form').action = button.dataset.deleteUrl;
            deletion.showModal(); document.getElementById('lic-delete-cancel').focus(); return;
        }
        document.getElementById('lic-summary-name').textContent = person.nome;
        document.getElementById('lic-summary-dre').textContent = person.dre || 'Não informado';
        document.getElementById('lic-summary-university').textContent = person.universidade;
        var links = document.getElementById('lic-summary-links'); links.replaceChildren();
        if (!person.vinculos.length) {
            var empty = document.createElement('li'); empty.textContent = 'Sem vínculo com setor.'; links.appendChild(empty);
        }
        person.vinculos.forEach(function (link) { var item = document.createElement('li'); item.textContent = linkDescription(link); links.appendChild(item); });
        var edit = row.querySelector('.lic-row-actions a');
        var summaryEdit = document.getElementById('lic-summary-edit'); summaryEdit.hidden = !edit;
        if (edit) summaryEdit.href = edit.href; else summaryEdit.removeAttribute('href');
        summary.showModal();
    });
    document.querySelectorAll('[data-close-dialog]').forEach(function (button) { button.addEventListener('click', function () { button.closest('dialog').close(); }); });
    document.getElementById('lic-delete-cancel').addEventListener('click', function () { deletion.close(); });
    [summary, deletion].forEach(function (dialog) {
        dialog.addEventListener('click', function (event) {
            var bounds = dialog.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
        });
    });
    if (!window.jQuery || !jQuery.fn.DataTable) return;
    var university = document.getElementById('lic-university');
    var sector = document.getElementById('lic-sector');
    var status = document.getElementById('lic-status');
    var search = document.getElementById('lic-search');
    var pageSize = document.getElementById('lic-page-size');
    var exportButton = document.getElementById('lic-export');
    var filter = function (settings, data, index) {
        if (settings.nTable !== element) return true;
        var person = metadata.get(settings.aoData[index].nTr);
        if (university.value && person.universidade_id !== university.value) return false;
        if (status.value === 'sem_vinculo') return !sector.value && person.vinculos.length === 0;
        if (!sector.value && !status.value) return true;
        return person.vinculos.some(function (link) {
            return (!sector.value || String(link.setor_id) === sector.value) && (!status.value || link.situacao === status.value);
        });
    };
    jQuery.fn.dataTable.ext.search.push(filter);
    var table = jQuery(element).DataTable({
        dom: 't', pageLength: 10, autoWidth: false, order: [[0, 'asc']], deferRender: true,
        columnDefs: [{targets: 5, orderable: false, searchable: false}, {targets: 4, searchable: false}],
        language: {emptyTable: 'Nenhum licenciando cadastrado.', zeroRecords: 'Nenhum licenciando encontrado para os filtros selecionados.', aria: {sortAscending: ': ordenar em ordem crescente', sortDescending: ': ordenar em ordem decrescente'}}
    });
    [university, sector, status, search, pageSize, exportButton, document.getElementById('lic-clear-filters')].forEach(function (control) { control.disabled = false; });
    [university, sector, status].forEach(function (control) { control.addEventListener('change', function () { table.draw(); }); });
    search.addEventListener('input', function () { table.search(search.value).draw(); });
    pageSize.addEventListener('change', function () { table.page.len(Number(pageSize.value)).draw(); });
    document.getElementById('lic-clear-filters').addEventListener('click', function () {
        university.value = ''; sector.value = ''; status.value = ''; search.value = ''; table.search('').draw();
    });
    var previous = document.getElementById('lic-previous');
    var next = document.getElementById('lic-next');
    previous.addEventListener('click', function () { table.page('previous').draw('page'); });
    next.addEventListener('click', function () { table.page('next').draw('page'); });
    var number = new Intl.NumberFormat('pt-BR');
    function update() {
        var info = table.page.info();
        var text = info.recordsDisplay ? 'Mostrando ' + number.format(info.start + 1) + '–' + number.format(info.end) + ' de ' + number.format(info.recordsDisplay) + ' licenciandos' : 'Nenhum licenciando encontrado';
        if (info.recordsDisplay !== info.recordsTotal) text += ' (' + number.format(info.recordsTotal) + ' no total)';
        document.getElementById('lic-results').textContent = text;
        previous.disabled = info.page === 0; next.disabled = info.page >= info.pages - 1;
        exportButton.disabled = info.recordsDisplay === 0;
        var pages = document.getElementById('lic-pages'); pages.replaceChildren();
        var indexes = new Set();
        if (info.pages) { indexes.add(0); indexes.add(info.pages - 1); }
        var start = Math.max(0, Math.min(info.page - 2, info.pages - 5));
        for (var page = start; page < Math.min(info.pages, start + 5); page++) indexes.add(page);
        var last = -1;
        Array.from(indexes).sort(function (a, b) { return a - b; }).forEach(function (page) {
            if (last !== -1 && page - last > 1) { var dots = document.createElement('span'); dots.textContent = '…'; dots.setAttribute('aria-hidden', 'true'); pages.appendChild(dots); }
            var button = document.createElement('button'); button.type = 'button'; button.textContent = page + 1; button.setAttribute('aria-label', 'Página ' + (page + 1));
            if (page === info.page) button.setAttribute('aria-current', 'page');
            button.addEventListener('click', function () { table.page(page).draw('page'); }); pages.appendChild(button); last = page;
        });
    }
    function csvCell(value) {
        var text = String(value == null ? '' : value);
        if (/^\s*[=+@-]|^[\t\r\n]/.test(text)) text = "'" + text;
        return '"' + text.replace(/"/g, '""') + '"';
    }
    exportButton.addEventListener('click', function () {
        var rows = [['Nome', 'DRE', 'Universidade', 'Setores', 'Situação por setor']];
        table.rows({search: 'applied', order: 'applied'}).nodes().toArray().forEach(function (row) {
            var person = metadata.get(row);
            rows.push([person.nome, person.dre, person.universidade, Array.from(new Set(person.vinculos.map(function (link) { return link.nome; }))).join(', '), person.vinculos.map(linkDescription).join(' | ')]);
        });
        var csv = rows.map(function (row) { return row.map(csvCell).join(';'); }).join('\r\n');
        var url = URL.createObjectURL(new Blob(['\uFEFF' + csv], {type: 'text/csv;charset=utf-8'}));
        var anchor = document.createElement('a'); anchor.href = url; anchor.download = 'licenciandos-filtrados.csv'; document.body.appendChild(anchor); anchor.click(); anchor.remove();
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
        document.getElementById('lic-export-status').textContent = number.format(rows.length - 1) + ' licenciandos exportados, considerando os filtros atuais.';
    });
    table.on('draw', update);
    table.on('destroy', function () { var index = jQuery.fn.dataTable.ext.search.indexOf(filter); if (index >= 0) jQuery.fn.dataTable.ext.search.splice(index, 1); });
    update();
});
