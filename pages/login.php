<?php
session_start();

$errors = [];
$success = false;

if(isset($_SESSION['siape']) || isset($_SESSION['registro'])){
    header("Location: inicial.php?mensagem=usuario_ja_logado");
    exit;
}

require_once __DIR__ . '/../src/utils/erros_mapeados.php';


if (isset($_GET['mensagem']) && $_GET['mensagem'] !== '') {
    $codigo = $_GET['mensagem'];

    if (isset($errosPermitidos[$codigo])) {
        $errors[] = $errosPermitidos[$codigo]['mensagem'];
        $errorClass = $errosPermitidos[$codigo]['classe'];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MONIFÁCIL</title>
    <link rel="icon" href="../public/img/menuItens/icone.png" type="image/png">
    <link rel="stylesheet" href="../public/css/login.css">
</head>

<body>
    <div class="logo-header">
        <img class="logo-text" src="../public/img/menuItens/logo.png" />
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-message global-error" id="global-erro">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="login-container">
        <div class="login-tabs">
            <button class="tab active" onclick="switchTab('aluno')">Aluno</button>
            <button class="tab" onclick="switchTab('admin')">Administrador</button>
        </div>

        <!-- TAB ALUNO -->
        <div id="aluno" class="tab-content active">
            <div class="login-content">
                <form method="POST" action="../src/controllers/login_aluno_backend.php">
                    <input type="hidden" name="login_type" value="aluno">

                    <div class="form-group">
                        <label class="form-label">Registro Acadêmico</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔢</span>
                            <input
                                type="text"
                                name="registro"
                                inputmode="numeric"
                                maxlength="7"
                                placeholder="0000000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Senha</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔐</span>
                            <input
                                type="password"
                                name="senha"
                                placeholder="Digite sua senha">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Entrar</button>

                    <div class="info-text">
                        Seu Registro Acadêmico tem <strong>7 dígitos numéricos</strong>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB ADMIN -->
        <div id="admin" class="tab-content">
            <div class="login-content">

                <form method="POST" action="../src/controllers/login_admin_backend.php">
                    <input type="hidden" name="login_type" value="admin">

                    <div class="form-group">
                        <label class="form-label">SIAPE</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔢</span>
                            <input
                                type="text"
                                name="siape"
                                inputmode="numeric"
                                maxlength="7"
                                placeholder="0000000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Senha</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔐</span>
                            <input
                                type="password"
                                name="senha"
                                placeholder="Digite sua senha">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Entrar Como Admin</button>

                    <div class="info-text">
                        Seu SIAPE tem <strong>7 dígitos numéricos</strong>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');

            // Clear errors when switching tabs
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => msg.remove());
        }

        // Validação em tempo real para números
        document.querySelectorAll('input[inputmode="numeric"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^\d]/g, '').slice(0, 7);
            });
        });
    </script>

    <script>
        setTimeout(() => {
            const alert = document.getElementById('global-erro');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.6s ease'; // fade-out
                setTimeout(() => alert.remove(), 600); // remove do DOM após sumir
            }
        }, 5000); // 5 segundos
    </script>

<?php 
include_once 'footer.php';
?>
