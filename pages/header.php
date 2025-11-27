<?php
session_start();
if (!isset($_SESSION['status'])) {
    header('Location: login.php?mensagem=usuario_nao_esta_logado');
    exit;
}

$paginaAtual = basename($_SERVER['PHP_SELF']);

if ($_SESSION['status'] === 'Administrador' && $paginaAtual !== 'adminPage.php') {
    header('Location: adminPage.php?mensagem=acesso_invalido');
    exit;
}

$sessionId = session_id();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($titlePage) ? $titlePage : 'MoniFácil'; ?></title>
    <link rel="stylesheet" href="../public/css/menu.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../public/img/menuItens/icone.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="../public/css/<?php echo !empty($nameCSS) ? $nameCSS : ''; ?>.css">
</head>

<body>
    <header class="cabecalho">
        <nav class="cabecalho-navegacao">
            <div class="cabecalho-navegacao-menu">
                <div class="menu-imagem-wrapper">
                    <img class="cabecalho-navegacao-menu-imagem" src="../public/img/menuItens/menu-hamburguer.png" alt="Menu hamburguer para ser aberto">
                </div>
                <ul class="cabecalho-navegacao-menu-elementos">
                    <?php if ($_SESSION['status'] === 'Administrador'): ?>
                    <?php else: ?>
                        <a href="disciplinas.php">
                            <li class="cabecalho-navegacao-menu-elemento">Disciplinas</li>
                        </a>
                        <a href="chat.php">
                            <li class="cabecalho-navegacao-menu-elemento">Chat</li>
                        </a>
                        <a href="FAQ.php">
                            <li class="cabecalho-navegacao-menu-elemento">FAQ's</li>
                        </a>
                        <a href="monitores.php">
                            <li class="cabecalho-navegacao-menu-elemento">Monitores</li>
                        </a>
                        <a href="minhas_monitorias_inscritas.php">
                            <li class="cabecalho-navegacao-menu-elemento">Monitorias Inscritas</li>
                        </a>
                        <?php if ($_SESSION['status'] === 'Monitor'): ?>
                            <a href="minhas_monitorias.php">
                                <li class="cabecalho-navegacao-menu-elemento">Minhas Monitorias</li>
                            </a>
                        <?php else: ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <a href="inicial.php">
                <img class="cabecalho-navegacao-menu-logo" src="../public/img/menuItens/logo.png" alt="Logo do site MoniFacil">
            </a>

            <!-- Adicionado contêiner para mostrar tipo de sessão e perfil -->
            <div class="cabecalho-navegacao-menu-perfil-container">
                <!-- Nova div mostrando o tipo de sessão do usuário -->
                <div class="cabecalho-navegacao-sessao-tipo">
                    <span class="sessao-tipo-badge">
                        <?php
                        $statusTexto = $_SESSION['status'];
                        if ($_SESSION['status'] === 'Monitor') {
                            $statusTexto = 'Monitor';
                        } elseif ($_SESSION['status'] === 'Administrador') {
                            $statusTexto = 'Administrador';
                        } else {
                            $statusTexto = 'Aluno';
                        }
                        echo $statusTexto;
                        ?>
                    </span>
                    <!-- Adicionado identificador de sessão -->
                    <span class="sessao-id-badge" title="ID da Sessão">
                        <?php if (isset($_SESSION['registro'])): ?>
                            RA: <?php echo $_SESSION['registro']; ?>
                        <?php else: ?>
                            SIAPE: <?php echo $_SESSION['siape'] ?>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="cabecalho-navegacao-menu-perfil">
                    <div class="menu-wrapper-perfil">
                        <img class="cabecalho-navegacao-menu-perfil-imagem" src="../public/img/menuItens/meu-perfil.png" alt="Foto para acessar o meu perfil">
                    </div>
                    <div class="menu-quebra">
                        <ul class="cabecalho-navegacao-menu-perfil-elementos aberto">
                            <?php if ($paginaAtual !== 'adminPage.php'): ?>
                                <a href='perfil.php' class="perfil">
                                    <li class="cabecalho-navegacao-menu-perfil-elemento">Meu Perfil</li>
                                </a>
                            <?php else: ?>
                            <?php endif; ?>
                            <a href="logout.php" class="perfil">
                                <li class="cabecalho-navegacao-menu-perfil-elemento">Log out</li>
                            </a>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>