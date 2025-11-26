<?php
$nameCSS = 'relatorio';
$titlePage = 'Relatório de Monitoria';

require_once __DIR__ . '/header.php';

$monitoriaId = $_GET['id'] ?? 1;

require_once __DIR__ . '/../src/controllers/relatorio_dados.php';

?>
<div class="container">
    <header style="background: <?php echo $monitoria['cor']; ?>">
        <h1>Relatório de Monitoria</h1>
        <p>Gerencie sua sessão e confirme a presença dos alunos</p>
    </header>

    <div class="content">
        <div class="info-grid">
            <div class="info-card">
                <label>Disciplina</label>
                <p><?php echo htmlspecialchars($monitoria['disciplina']); ?></p>
            </div>
            <div class="info-card">
                <label>Monitor</label>
                <p><?php echo htmlspecialchars($monitoria['monitor']); ?></p>
            </div>
            <div class="info-card">
                <label>Data e Hora</label>
                <p><?php echo date('d/m/Y', strtotime($monitoria['data'])) . ' · ' . $monitoria['hora_inicio'] ?></p>
            </div>
            <div class="info-card">
                <label>Local</label>
                <p>Sala <?php echo htmlspecialchars($monitoria['local']); ?></p>
            </div>
            <div class="info-card">
                <label>Status da Monitoria</label>
                <p>
                    <span class="status-badge status-<?php echo $monitoria['status']; ?>">
                        <?php echo $monitoria['status'] === 'concluida' ? '✓ Concluída' : '● Ativa'; ?>
                    </span>
                </p>
            </div>
            <div class="info-card">
                <label>Matérias Abordadas</label>
                <p><?php echo htmlspecialchars($monitoria['descricao']); ?></p>
            </div>
        </div>

        <div class="stats-container">
            <div class="stats-box">
                <p><?php echo $monitoria['presentes']; ?></p>
                <span>Alunos Presentes</span>
            </div>
            <div class="stats-box">
                <p><?php echo $monitoria['ausentes']; ?></p>
                <span>Alunos Ausentes</span>
            </div>
            <div class="stats-box">
                <p><?php echo ($monitoria['presentes'] === 0) ? 0 : round(($monitoria['presentes'] / ($monitoria['presentes'] + $monitoria['ausentes'])) * 100); ?>%</p>
                <span>Taxa de Presença</span>
            </div>
        </div>

        <?php if ($monitoria['status'] === 'ativa'): ?>
            <div class="info-notice">
                ⚠️ As marcações de presença são temporárias. Elas serão salvas permanentemente quando você concluir a monitoria.
            </div>
        <?php endif; ?>

        <h2 class="section-header">Confirmação de Presença</h2>
        <form method="POST" action="../src/controllers/salvar_informacoes_relatorio.php?id=<?php echo $monitoria['id']; ?>">
            <div class="students-list">
                <?php foreach ($alunos as $aluno): ?>
                    <div class="student-item">
                        <input type="checkbox"
                            name="aluno_<?php echo $aluno['id']; ?>"
                            id="aluno_<?php echo $aluno['id']; ?>"
                            <?php echo $aluno['confirmada'] == 1 ? 'checked' : ''; ?>
                            <?php echo $monitoria['status'] == 'concluida' ? 'disabled' : ''; ?>>
                        <div class="student-info">
                            <div class="student-name"><?php echo htmlspecialchars($aluno['nome']); ?></div>
                            <div class="student-id">Matrícula: <?php echo htmlspecialchars($aluno['id']); ?></div>
                        </div>
                        <div class="presence-indicator <?php echo $aluno['confirmada'] == 1 ? 'present' : ''; ?>">
                            <?php echo $aluno['confirmada'] == 1 ? 'Marcado' : 'Não marcado'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="feedback-section">
                <h2 class="section-header">Feedback da Monitoria</h2>
                <?php var_dump($monitoria['feedback']); ?>
                <label class="feedback-label" for="feedback">Observações sobre a sessão</label>
                <textarea name="feedback" id="feedback" class="feedback-textarea" placeholder="Adicione observações sobre a sessão de monitoria, tópicos abordados, dificuldades dos alunos, engajamento, sugestões para próximas sessões..." <?php echo $monitoria['status'] === 'concluida' ? 'readonly' : ''; ?>><?php echo htmlspecialchars($monitoria['feedback']); ?></textarea>
            </div>

            <div class="actions">
                <button type="submit" name="acao" value="marcar_presenca" class="btn-primary" style="background: <?php echo $monitoria['cor']; ?>">Atualizar Marcações</button>
                <button type="submit" name="acao" value="salvar_feedback" class="btn-primary" style="background: <?php echo $monitoria['cor']; ?>">Salvar Feedback</button>
                <?php if ($monitoria['status'] === 0): ?>
                    <button type="submit" name="acao" value="concluir_monitoria" class="btn-success">Concluir Monitoria</button>
                <?php else: ?>
                    <button type="submit" name="acao" value="cancelar_conclusao" class="btn-secondary">Reabrir Monitoria</button>
                <?php endif; ?>
                <button type="button" class="btn-exit"
                    onclick="window.location.href='minhas_monitorias.php?acao=sair';">
                    Sair
                </button>
            </div>
        </form>
    </div>
</div>


<?php

require_once __DIR__ . '/footer.php';

?>