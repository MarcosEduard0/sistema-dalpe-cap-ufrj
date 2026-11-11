document.addEventListener('DOMContentLoaded', function () {
    var button = document.getElementById('rel-print');
    button.hidden = false;
    button.addEventListener('click', function () { window.print(); });
});
