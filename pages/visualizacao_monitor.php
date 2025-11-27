<?php

$nameCSS = 'visualizar_monitor';
$titlePage = 'Perfil Monitor';
require_once __DIR__ . '/header.php';

require_once __DIR__ . '/../src/controllers/visualizacao_monitor_dados.php';

function formatarTelefone($tel) {
    $tel = preg_replace('/\D/', '', $tel);

    if (strlen($tel) === 11) {
        return sprintf("(%s) %s %s-%s",
            substr($tel, 0, 2),   
            substr($tel, 2, 1),   
            substr($tel, 3, 4),   
            substr($tel, 7, 4)   
        );
    }

    return $tel;
}


?>

    <div class="container">
        <a href="monitores.php" class="btn-back">
            <span>←</span> Voltar para Monitores
        </a>

        <div class="profile-card">
            <div class="profile-header" style="background: <?php echo $cor_disciplina; ?>;">
                <div class="profile-avatar">
                    <img src="<?php echo htmlspecialchars($monitor['foto']); ?>" alt="<?php echo htmlspecialchars($monitor['nome']); ?>">
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($monitor['nome']); ?></h1>
                    <p class="profile-curso">
                        <?php echo htmlspecialchars($monitor['curso']); ?>
                    </p>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-content">
                                <span class="stat-value"><?php echo $monitor['total_monitorias']; ?></span>
                                <span class="stat-label">Monitorias cadastradas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-body">
                <div class="info-section">
                    <h2 class="section-title">Contato</h2>
                    <div class="contact-info">
                        <div class="contact-item">
                            <span class="contact-text"><?php echo htmlspecialchars($monitor['email']); ?></span>
                        </div>
                        <div class="contact-item">
                            <span class="contact-text"><?php echo htmlspecialchars(formatarTelefone($monitor['telefone'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="monitorias-section">
            <h2 class="section-main-title">Monitorias Cadastradas (<?php echo count($todasMonitorias); ?>)</h2>
            
            <?php if (empty($todasMonitorias)): ?>
                <div class="empty-state">
                    <p>Este monitor ainda não possui monitorias cadastradas.</p>
                </div>
            <?php else: ?>
                <div class="monitorias-grid">
                    <?php foreach ($todasMonitorias as $monitoria): ?>
                        <div class="monitoria-card">
                            <div class="card-header" style="background: <?php echo $monitoria['cor']; ?>;">
                                <div class="card-header-top">
                                    <h3 class="card-materia"><?php echo htmlspecialchars($monitoria['materia']); ?></h3>
                                    <span class="status-badge status-<?php echo strtolower($monitoria['status']); ?>">
                                        <?php echo htmlspecialchars($monitoria['status']); ?>
                                    </span>
                                </div>
                                <p class="card-datetime">
                                    <?php echo htmlspecialchars($monitoria['data']); ?> às <?php echo htmlspecialchars($monitoria['horario']); ?>
                                </p>
                            </div>
                            
                            <div class="card-body">
                                <p class="card-description"><?php echo htmlspecialchars($monitoria['descricao']); ?></p>
                                
                                <div class="vagas-info">
                                    <div class="vagas-header">
                                        <span class="vagas-label">Inscritos</span>
                                        <span class="vagas-count"><?php echo $monitoria['inscritos']; ?>/<?php echo $monitoria['vagas']; ?></span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo ($monitoria['inscritos'] / $monitoria['vagas'] * 100); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <form class="card-footer" method="POST" action="../src/controllers/inscrever_monitoria.php?id_monitoria=<?php echo $monitoria['id']; ?>">
                                <a href="visualizacao_monitoria.php?id=<?php echo $monitoria['id']; ?>" class="btn btn-secondary">
                                    Ver Detalhes
                                </a>
                                <?php if ($monitoria['status'] === 'Lotada'): ?>
                                    <button class="btn btn-disabled" disabled>
                                        Lotada
                                    </button>
                                <?php elseif($monitoria['estou_inscrito'] === true): ?>
                                    <button type="submit" name="cancelar" class="btn btn-primary">
                                        Desinscrever
                                    </button>
                                <?php elseif($monitoria['e_da_mesma_pessoa']): ?>
                                    <button class="btn btn-disabled" disabled>
                                        Esta monitoria é sua
                                    </button>
                                <?php else: ?>
                                    <button name="inscricao" type="submit" class="btn btn-primary" onclick="inscrever(<?php echo $monitoria['id']; ?>)">
                                        Inscrever-se
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function verDetalhes(id) {
            alert('Visualizando detalhes da monitoria #' + id);
        }

        function inscrever(id) {
            if (confirm('Deseja se inscrever nesta monitoria?')) {
                alert('Inscrito com sucesso na monitoria #' + id);
                location.reload();
            }
        }
    </script>

<?php 

require_once __DIR__ . '/footer.php';

?>
