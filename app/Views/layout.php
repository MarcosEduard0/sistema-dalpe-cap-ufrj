<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <script>
        // Restaure a largura antes de carregar o CSS e renderizar o menu.
        try {
            if (window.matchMedia('(min-width:768px)').matches && localStorage.getItem('dalpe.nav.collapsed') === 'true') {
                document.documentElement.classList.add('nav-collapsed');
            }
        } catch (_) {}
    </script>
    <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url("public/assets/img/apple-icon.png") ?>">
    <link rel="icon" type="image/png" href="<?= base_url("public/assets/img/favicon.png") ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
        <?= $titulo ?>
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/bootstrap.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/bootstrap.min.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/paper-dashboard.css?v=2.0.1") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/style.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/dataTables.bootstrap4.min.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/fontawesome-5.15.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/choices.min.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/navbar.css") . '?v=' . filemtime(FCPATH . 'public/assets/css/navbar.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("public/assets/css/login.css") . '?v=' . filemtime(FCPATH . 'public/assets/css/login.css') ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

</head>

<?php if (isset($_COOKIE['ci_session']) && session()->get('logged_in')) : ?>

    <body id="body-pd" class="<?= session()->get('posicao') === 'Home' ? 'dashboard-page' : '' ?>" onload="<?= session()->getFlashdata('msg') ?>">
        <header class="header" id="header">
            <div class="header_toggle"><button type="button" id="header-toggle" class="nav-toggle" aria-controls="nav-bar" aria-expanded="true" aria-label="Recolher menu"><i class="bx bx-menu" aria-hidden="true"></i></button><span><?= esc($titulo) ?></span></div>
            <div> <?= (strlen(session()->get('usuario')['nome_social']) < 1) ? session()->get('usuario')['nome_completo'] : session()->get('usuario')['nome_social'] ?> </div>
        </header>
        <button type="button" id="nav-overlay" class="nav-overlay" aria-label="Fechar menu" tabindex="-1" hidden></button>
        <div class="l-navbar" id="nav-bar">
            <nav class="nav" aria-label="Menu principal">
                <button type="button" id="nav-close" class="nav-close" aria-label="Fechar menu"><i class="bx bx-x" aria-hidden="true"></i></button>
                <div> <a href="<?= base_url('home') ?>" class="nav_logo" aria-label="DALPE CAp UFRJ - Início"> <i class='bx bx-layer nav_logo-icon'></i> <span class="nav_logo-name">DALPE CAp UFRJ</span> </a>
                    <div class="nav_list">
                        <a href="<?= base_url('/home') ?>" class="nav_link <?= (session()->get('posicao') == 'Home') ? 'active_nav' : '' ?>">
                            <i class='bx bx-home-alt nav_icon'></i>
                            <span class="nav_name">Início</span>
                        </a>
                        <a href="<?= base_url('/licenciandos') ?>" class="nav_link <?= (session()->get('posicao') == 'Licenciandos') ? 'active_nav' : '' ?>">
                            <i class='bx bxs-graduation nav_icon'></i>
                            <span class="nav_name">Licenciandos</span>
                        </a>
                        <?php if (session()->get('authlevel') == ADMINISTRADOR) : ?>
                            <a href="<?= base_url('/setores') ?>" class="nav_link <?= (session()->get('posicao') == 'Setores') ? 'active_nav' : '' ?>">
                                <i class='bx bx-food-menu nav_icon'></i>
                                <span class="nav_name">Setores</span>
                            </a>
                            <a href="<?= base_url('/universidades') ?>" class="nav_link <?= (session()->get('posicao') == 'Universidade') ? 'active_nav' : '' ?>">
                                <i class='bx bxs-institution nav_icon'></i>
                                <span class="nav_name">Universidades</span>
                            </a>
                            <a href="<?= base_url('/documentos') ?>" class="nav_link <?= (session()->get('posicao') == 'Documentos') ? 'active_nav' : '' ?>">
                                <i class='bx bx-file nav_icon'></i>
                                <span class="nav_name">Documentos</span>
                            </a>
                            <a href="<?= base_url('/usuarios') ?>" class="nav_link <?= (session()->get('posicao') == 'Usuarios') ? 'active_nav' : '' ?>">
                                <i class='bx bx-group nav_icon'></i>
                                <span class="nav_name">Usuários</span>
                            </a>
                        <?php endif ?>
                        <a href="<?= base_url('relatorios') ?>" class="nav_link <?= session()->get('posicao') === 'Relatorios' ? 'active_nav' : '' ?>"><i class="bx bx-bar-chart-alt-2 nav_icon" aria-hidden="true"></i><span class="nav_name">Relatórios</span></a>
                    </div>
                </div> <a href="<?= base_url('/logout') ?>" class="nav_link">
                    <i class='bx bx-log-out nav_icon'></i>
                    <span class="nav_name">Sair</span> </a>
            </nav>
        </div>
        <?php if (isset($body)) : ?>
            <div style="padding-top: 10px;">
                <?= $body ?>
            </div>
        <?php endif ?>
        <footer class="footer footer-black  footer-white ">
            <div class="container-fluid">
                <div class="row">
                    <div class="credits ml-auto">
                        <span class="copyright">
                            Por <t data-toggle="tooltip" data-placement="top" title="marcos.eduardo22@gmail.com">
                                Marcos Eduardo de Souza
                            </t> &nbsp;&bull;&nbsp; Versão <?= VERSAO ?>.&copy; <?= date('Y') ?>
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    </body>
<?php else :
    echo $body;
endif ?>
<script type='text/javascript' src="<?= base_url("public/assets/js/jquery-3.5.1.js") ?>"></script>
<script type='text/javascript' src="<?= base_url("public/assets/js/dataTables.min.js") ?>"></script>
<script type='text/javascript' src="<?= base_url("public/assets/js/choices.min.js") ?>"></script>
<script type='text/javascript' src="<?= base_url("public/assets/js/dataTables.bootstrap4.min.js") ?>"></script>
<script type='text/javascript' src="<?= base_url("public/assets/js/main.js") ?>"></script>
<script type='text/javascript' src="<?= base_url("public/assets/js/functions.js") . '?v=' . filemtime(FCPATH . 'public/assets/js/functions.js') ?>"></script>
<script type='text/javascript' src="<?= base_url("public/assets/js/popper.min.js") ?>"></script>
<script type='text/javascript' src="<?= base_url("public/assets/js/bootstrap.js") ?>"></script>
<script type='text/javascript' src="<?= base_url("public/assets/js/plugins/bootstrap-notify.js") ?>"></script>


</html>
