<body class="dalpe-login" onload="<?= $msg ?>">
    <main class="login-main">
        <section class="login-card" aria-labelledby="login-title">
            <div class="login-brand">
                <img class="login-logo" width="160" height="160" src="<?= base_url('public/assets/img/cap.png') . '?v=' . filemtime(FCPATH . 'public/assets/img/cap.png') ?>" alt="Colégio de Aplicação da UFRJ">
                <h1 id="login-title">DALPE CAp UFRJ</h1>
                <p>Sistema de Gestão de Licenciandos e Estágios</p>
            </div>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'autenticacao_requerida'): ?><p class="login-notice" role="alert">Você precisa estar autenticado para acessar esta página.</p><?php endif ?>
            <form id="login-form" action="<?= base_url('login') ?>" method="post">
                <?= csrf_field() ?>
                <div class="login-field"><label class="sr-only" for="login-user">Usuário</label><i class="bx bx-user" aria-hidden="true"></i><input id="login-user" type="text" placeholder="Usuário" name="usuario" autocomplete="username" autocapitalize="none" spellcheck="false" required></div>
                <div class="login-field"><label class="sr-only" for="login-password">Senha</label><i class="bx bx-lock-alt" aria-hidden="true"></i><input id="login-password" type="password" placeholder="Senha" name="senha" autocomplete="current-password" required><button id="login-show-password" class="login-show-password" type="button" aria-label="Mostrar senha" aria-pressed="false" aria-controls="login-password" hidden><i class="bx bx-show" aria-hidden="true"></i></button></div>
                <label class="login-remember" id="login-remember-label" hidden><input id="login-remember" type="checkbox">Lembrar meu usuário</label>
                <button class="login-submit" type="submit"><i class="bx bx-right-arrow-alt" aria-hidden="true"></i>Entrar</button>
            </form>
        </section>
        <footer class="login-footer">Por Marcos Eduardo de Souza <span aria-hidden="true">&bull;</span> Versão <?= esc(VERSAO) ?> &copy; <?= date('Y') ?></footer>
    </main>
    <script src="<?= base_url('public/assets/js/login.js') . '?v=' . filemtime(FCPATH . 'public/assets/js/login.js') ?>"></script>
</body>