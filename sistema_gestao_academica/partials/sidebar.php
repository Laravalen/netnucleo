<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">

    <div class="brand">
        <span class="brand-mark">⌂</span>

        <div>
            <b>SESI SENAI</b>
            <small>Gestão Acadêmica</small>
        </div>
    </div>

    <nav class="sidebar-nav">

        <!-- INÍCIO -->
        <a href="dashboard.php"
           class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <span class="nav-ico">▦</span>
            <span>Início</span>
        </a>

        <?php if (is_instructor()): ?>

            <div class="nav-label">GESTÃO</div>

            <!-- CADASTROS -->
            <a href="cadastros.php"
               class="<?= $currentPage === 'cadastros.php' ? 'active' : '' ?>">
                <span class="nav-ico">▣</span>
                <span>Cadastros</span>
            </a>

            <!-- MOVIMENTAÇÃO -->
            <a href="movimentacoes.php"
               class="<?= $currentPage === 'movimentacoes.php' ? 'active' : '' ?>">
                <span class="nav-ico">↔</span>
                <span>Movimentação</span>
            </a>

        <?php endif; ?>

        <div class="nav-label">CONSULTAS</div>

        <!-- HORÁRIOS -->
        <a href="horarios.php"
           class="<?= $currentPage === 'horarios.php' ? 'active' : '' ?>">
            <span class="nav-ico">□</span>
            <span>Consulta de Horários</span>
        </a>

        <!-- INSTRUTORES -->
        <a href="instrutores.php"
           class="<?= $currentPage === 'instrutores.php' ? 'active' : '' ?>">
            <span class="nav-ico">♙</span>
            <span>Consulta de Instrutores</span>
        </a>

        <!-- SALAS -->
        <a href="salas.php"
           class="<?= $currentPage === 'salas.php' ? 'active' : '' ?>">
            <span class="nav-ico">▥</span>
            <span>Consulta de Salas</span>
        </a>

        <div class="nav-label">RELATÓRIOS</div>

        <!-- RELATÓRIOS -->
        <a href="relatorios.php"
           class="<?= $currentPage === 'relatorios.php' ? 'active' : '' ?>">
            <span class="nav-ico">▥</span>
            <span>Relatórios</span>
        </a>

    </nav>


    <!-- BOTÃO SAIR -->
    <a href="logout.php" class="logout">
        <span class="nav-ico">↪</span>
        <span>Sair</span>
    </a>

</aside>


<main class="main">

    <header class="topbar">

        <div class="crumb">
            <span class="light">SGA</span>
            <span class="light">›</span>
            Sistema Web de Gestão Acadêmica
        </div>

        <div class="top-actions">

            <input
                class="search-top"
                placeholder="⌕  Pesquisar..."
                aria-label="Pesquisar"
            >

            <div class="user-chip">

                <span class="avatar">
                    <?= e(mb_strtoupper(mb_substr(user()['nome'], 0, 2))) ?>
                </span>

                <div>
                    <?= e(user()['nome']) ?>

                    <div class="role">
                        <?= e(user()['perfil']) ?>
                    </div>
                </div>

            </div>

        </div>

    </header>