function deleteModal(id) {

    var data = document.getElementById(id);
    var json = JSON.parse(data.getAttribute('data-detalhes'));

    if(!json.nome){
    document.getElementById("nome").textContent = json.nome_completo;
    }else{
        document.getElementById("nome").textContent = json.nome;
    }
    document.getElementById("confirmDelete").href = window.location.href + '/excluir/' + id;

}

function documentoModel(licenciando_id,documento_id, setor_id, baseUrl) {

    var data = document.getElementById(documento_id);
    var json = JSON.parse(data.getAttribute('data-detalhes'));
    data = document.getElementById('document_action');
    data.action = baseUrl + '/documentos/emitir/'+licenciando_id+'/'+ setor_id + '/'+ documento_id;

    data = document.getElementById('nomeDocumento');
    data.innerText  = json.nome;
}

demo = {
     
    showNotification: function(from, align, msg, color) {
        icone = "fa fa-check-circle"
        if (color == 'danger'){
            icone = "fa fa-exclamation-circle";
        }
      $.notify({
        icon: icone,
        message: msg
  
      }, {
        type: color,
        timer: 5000,
        placement: {
          from: from,
          align: align
        }
      });
    }
  
  };

/* Menu lateral compartilhada por todas as telas autenticadas. */
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('header-toggle');
    const nav = document.getElementById('nav-bar');
    const overlay = document.getElementById('nav-overlay');
    const closeButton = document.getElementById('nav-close');
    if (!button || !nav || !overlay) return;
    const body = document.body;
    const mobile = window.matchMedia('(max-width: 767px)');
    let collapsed = false;
    try { collapsed = localStorage.getItem('dalpe.nav.collapsed') === 'true'; } catch (_) {}
    function update() {
        const open = mobile.matches ? body.classList.contains('nav-open') : !collapsed;
        body.classList.toggle('nav-collapsed', !mobile.matches && collapsed);
        overlay.hidden = !mobile.matches || !open;
        button.setAttribute('aria-expanded', String(open));
        button.setAttribute('aria-label', open ? 'Recolher menu' : 'Abrir menu');
        nav.inert = mobile.matches && !open;
        nav.querySelectorAll('.nav_link').forEach(function (link) {
            const label = link.textContent.trim();
            link.title = label;
            link.setAttribute('aria-label', label);
            if (link.classList.contains('active_nav')) link.setAttribute('aria-current', 'page');
        });
    }
    button.addEventListener('click', function () {
        if (mobile.matches) body.classList.toggle('nav-open');
        else { collapsed = !collapsed; try { localStorage.setItem('dalpe.nav.collapsed', String(collapsed)); } catch (_) {} }
        update();
        if (mobile.matches && body.classList.contains('nav-open')) closeButton.focus();
    });
    function closeMobile() { body.classList.remove('nav-open'); update(); button.focus(); }
    overlay.addEventListener('click', closeMobile);
    closeButton.addEventListener('click', closeMobile);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && mobile.matches && body.classList.contains('nav-open')) closeMobile();
        if (event.key === 'Tab' && mobile.matches && body.classList.contains('nav-open')) {
            const items = Array.from(nav.querySelectorAll('a[href], button'));
            const index = items.indexOf(document.activeElement);
            if (event.shiftKey && index <= 0) { event.preventDefault(); items[items.length - 1].focus(); }
            else if (!event.shiftKey && (index === items.length - 1 || index === -1)) { event.preventDefault(); items[0].focus(); }
        }
    });
    mobile.addEventListener('change', function () { body.classList.remove('nav-open'); update(); });
    update();
});
