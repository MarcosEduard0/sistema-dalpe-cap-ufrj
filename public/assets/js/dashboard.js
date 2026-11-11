(function () {
    'use strict';
    var data = JSON.parse(document.getElementById('dashboard-data').textContent);
    if (typeof Chart === 'undefined') return; // Os valores continuam disponíveis em texto.
    Object.keys(data).forEach(function (key) {
        var canvas = document.getElementById('dash-' + key);
        if (!canvas) return;
        var line = key === 'periodos';
        new Chart(canvas, {
            type: line ? 'line' : (key === 'instituicoes' ? 'horizontalBar' : 'bar'),
            data: {
                labels: data[key].map(function (row) { return row.nome; }),
                datasets: [{label: 'Licenciandos', data: data[key].map(function (row) { return Number(row.quantidade); }), backgroundColor: line ? 'rgba(17, 142, 170, .12)' : '#118eaa', borderColor: '#087c96', borderWidth: 1, pointBackgroundColor: '#087c96', lineTension: 0, fill: line}]
            },
            options: {
                responsive: true, maintainAspectRatio: false, legend: {display: false},
                animation: {duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 500},
                scales: {
                    xAxes: [{gridLines: {display: key === 'instituicoes', color: '#edf0f3'}, ticks: {beginAtZero: true, fontColor: '#606572', precision: 0, autoSkip: false}}],
                    yAxes: [{gridLines: {display: key !== 'instituicoes', color: '#edf0f3'}, ticks: {beginAtZero: true, fontColor: '#606572', precision: 0}}]
                }
            }
        });
    });
}());
