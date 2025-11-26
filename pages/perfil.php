<?php
$title = 'Meu Perfil - MoniFácil';
$nameCSS = 'perfil';
require_once __DIR__ . '/header.php';

require_once __DIR__ . '/../src/controllers/perfil_backend.php';


$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? '';
$mensagem_erro = $_SESSION['mensagem_erro'] ?? '';

unset($_SESSION['mensagem_sucesso']);
unset($_SESSION['mensagem_erro']);

?>
    <div class="container">
        <div class="profile-card">
            <div class="profile-header" style="background: <?php echo $cor_disciplina; ?>">
                <div class="profile-photo-wrapper">
                    <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil" class="profile-photo">
                    <?php if ($usuario['is_monitor']): ?>
                        <span class="monitor-badge">Monitor(a)</span>
                    <?php endif; ?>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars($usuario['nome']); ?></h1>
                <p class="profile-ra">Registro Acadêmico: <?php echo htmlspecialchars($usuario['registro_academico']); ?></p>
            </div>

            <div class="profile-body">
                <?php if ($mensagem_sucesso): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($mensagem_sucesso); ?></div>
                <?php endif; ?>

                <?php if ($mensagem_erro): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($mensagem_erro); ?></div>
                <?php endif; ?>

                <form method="POST" action="../src/controllers/perfil_atualizar_backend.php">

                    <div class="form-section">
                        <h2 class="section-title">Informações Básicas</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($usuario['nome']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Registro Acadêmico</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($usuario['registro_academico']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Curso</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($usuario['curso']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="section-title">Informações Editáveis</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-input editable" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="section-title">Alterar Senha</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Senha Atual</label>
                                <div class="password-wrapper">
                                    <input type="password" name="senha_atual" id="senha_atual" class="form-input editable">
                                    <button type="button" class="toggle-password" onclick="togglePassword('senha_atual')">👁️</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nova Senha</label>
                                <div class="password-wrapper">
                                    <input type="password" name="nova_senha" id="nova_senha" class="form-input editable">
                                    <button type="button" class="toggle-password" onclick="togglePassword('nova_senha')">👁️</button>
                                </div>
                                <span class="password-hint">Mínimo de 6 caracteres</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirmar Nova Senha</label>
                                <div class="password-wrapper">
                                    <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-input editable">
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirmar_senha')">👁️</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-container">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '👁️‍🗨️';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }

        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>


<?php 

include_once __DIR__ . '/footer.php';

?>
