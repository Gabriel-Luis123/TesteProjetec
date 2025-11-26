<?php

$nameCSS= 'inicial';
$pageTitle = 'MONIFÁCIL - Plataforma de Monitorias';

include_once __DIR__ . '/header.php';
require_once __DIR__ . '/../src/controllers/monitorias_inicial.php';


$filtro_disciplina = $_GET['disciplina'] ?? null;

$monitores_filtrados = $filtro_disciplina 
    ? array_values(array_filter($monitores, fn($m) => $m['disciplina'] === $filtro_disciplina))
    : $alunos_Monitores;


shuffle($alunos_Monitores);


$selecionados = array_slice($alunos_Monitores, 0, 3);

?>

    <section class="hero">
        <h1 class="hero-title">Monitores de Elite</h1>
        <p class="hero-subtitle">Sua Jornada Acadêmica, Nossas Melhores Mentes</p>
        <p class="hero-description">Conheça nossos monitores especializados e encontre a monitoria perfeita para suas necessidades</p>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($monitores); ?></div>
                <div class="stat-label">Monitorias Cadastradas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($disciplinas_cadastradas); ?></div>
                <div class="stat-label">Disciplinas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($resultado_aluno_cadastrados); ?></div>
                <div class="stat-label">Alunos Cadastrados</div>
            </div>
        </div>
    </section>

    <section class="monitors-section" id="monitores">
        <h2 class="section-title">Confira Nossos Monitores</h2>


        <?php if (!empty($monitores_filtrados)): ?>
        <div class="monitors-grid">
            <?php foreach ($selecionados as $monitor): ?>
            <div class="monitor-card">
                <div class="monitor-header">
                    <div class="monitor-avatar" style="background-image: url('<?php echo $monitor['avatar']; ?>')"></div> 
                    <h3 class="monitor-name"><?php echo $monitor['nome']; ?></h3>
                    <span class="monitor-badge"><?php echo $monitor['turma']; ?></span>
                </div>

                <div class="monitor-body">
                    <div class="monitor-info">
                        <div class="info-label">Disciplina</div>
                        <div class="info-value"><?php echo $monitor['disciplina']; ?> - <?php echo $monitor['ano']; ?>º Ano</div>
                    </div>

                    <div class="monitor-stats">
                        <div class="stat">
                            <div class="stat-value"><?php echo $monitor['alunos']; ?></div>
                            <div class="stat-name">Alunos</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo $monitor['horarios']; ?></div>
                            <div class="stat-name">Últimas Sess/Sem</div>
                        </div>
                    </div>

                    <div class="horarios">
                        <div class="horarios-label">Último horário cadastrado:</div>
                        <div class="horarios-list">
                            <div class="horario-item"><?php echo $monitor['Ultima_Monitoria']; ?></div>
                        </div>
                    </div>

                    <div class="monitor-actions">
                        <button class="btn btn-secondary" onclick="window.location.href='visualizacao_monitor.php?registro=<?php echo $monitor['Registro_Academico'] ?>';">Ver Perfil</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-text">Nenhum monitor encontrado para esta disciplina</div>
        </div>
        <?php endif; ?>
    </section>

    <script>
    function toggleMateria(materia) {
        const grid = document.getElementById('grid-' + materia);
        const toggleBtn = document.getElementById('toggle-' + materia);

        if (grid.classList.contains('expanded')) {
            grid.classList.remove('expanded');
            toggleBtn.classList.remove('rotated');
        } else {
            grid.classList.add('expanded');
            toggleBtn.classList.add('rotated');
        }
    }

    function filtrarMonitorias() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.monitoria-card');
            
            cards.forEach(card => {
                const monitor = card.getAttribute('data-monitor');
                const horario = card.getAttribute('data-horario');
                const local = card.getAttribute('data-local');
                const descricao = card.getAttribute('data-descricao');
                
                const match = monitor.includes(searchTerm) || 
                             horario.includes(searchTerm) || 
                             local.includes(searchTerm) || 
                             descricao.includes(searchTerm);
                
                if (match || searchTerm === '') {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            // Expandir todas as seções quando houver pesquisa ativa
            if (searchTerm !== '') {
                document.querySelectorAll('.monitorias-grid').forEach(grid => {
                    grid.classList.add('expanded');
                });
                document.querySelectorAll('.toggle-btn').forEach(btn => {
                    btn.classList.add('rotated');
                });
            }
        }



    function abrirModal(id) {
        document.getElementById('modal-' + id).style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function fecharModal(id) {
        document.getElementById('modal-' + id).style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            document.body.style.overflow = 'auto';
        }
    });
</script>


<?php 

include_once __DIR__ . '/footer.php';

?>