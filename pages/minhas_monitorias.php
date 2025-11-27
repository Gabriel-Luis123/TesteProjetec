<?php

$nameCSS = 'minhas_monitorias';
$title = 'Minhas Monitorias - MONIFÁCIL';

require_once __DIR__ . '/header.php';

require_once __DIR__ . '/../src/controllers/minhas_monitorias_dados.php';


$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
$monitorias_filtradas = $filtro === 'todos'
    ? $monitorias
    : array_filter($monitorias, fn($m) => $m['status'] === $filtro);

$total_monitorias = count($monitorias);
$ativas = count(array_filter($monitorias, fn($m) => $m['status'] === 'ativo'));
$em_espera = count(array_filter($monitorias, fn($m) => $m['status'] === 'em_espera'));
$em_espera_geral = $ativas + $em_espera;
$concluidas = count(array_filter($monitorias, fn($m) => $m['status'] === 'concluido'));
$total_alunos = array_sum(array_column($monitorias, 'alunos_inscritos'));

function getStatusColor($status)
{
    $colors = [
        'ativo' => '#10b981',
        'em_espera' => '#f59e0b',
        'concluido' => '#6b7280'
    ];
    return $colors[$status] ?? '#6b7280';
}
function getStatusLabel($status)
{
    $labels = [
        'em_espera' => 'Em Espera',
        'concluido' => 'Concluído'
    ];
    return $labels[$status] ?? 'Desconhecido';
}

function getInscricaoPercentage($monitoria)
{
    if ($monitoria['capacidade_maxima'] == 0) return 0;
    return round(($monitoria['alunos_inscritos'] / $monitoria['capacidade_maxima']) * 100);
}

function getInscricaoColor($percentage)
{
    if ($percentage >= 80) return '#10b981';
    if ($percentage >= 50) return '#f59e0b';
    return '#ef4444';
}

if (empty($monitorias_filtradas)) {
    $position = 'absolute';
} else {
    $position = 'static';
}

?>

<style>
    footer {
        position: <?php echo $position; ?>;
    }

    @media (max-width: 1568px) {
        footer {
            position: static;
        }
    }
</style>


<div class="container">
    <div class="page-header">
        <h1 class="page-title">Minhas Monitorias</h1>
        <p class="page-description">Gerencie todas as suas sessões de monitoria em um único lugar</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $total_monitorias; ?></div>
            <div class="stat-label">Monitorias Criadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--success);"><?php echo $em_espera_geral; ?></div>
            <div class="stat-label">Em espera</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--warning);"><?php echo $concluidas; ?></div>
            <div class="stat-label">Concluídas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $total_alunos; ?></div>
            <div class="stat-label">Alunos Inscritos</div>
        </div>
    </div>

    <div class="filters-section">
        <span class="filter-label">Filtrar:</span>
        <div class="filter-buttons">
            <a href="?filtro=todos">
                <button class="filter-btn <?php echo $filtro === 'todos' ? 'active' : ''; ?>">
                    Todas
                </button>
            </a>
            <a href="?filtro=em_espera">
                <button class="filter-btn <?php echo $filtro === 'em_espera' ? 'active' : ''; ?>">
                    Em Espera
                </button>
            </a>
            <a href="?filtro=concluido">
                <button class="filter-btn <?php echo $filtro === 'concluido' ? 'active' : ''; ?>">
                    Concluído
                </button>
            </a>
        </div>
    </div>

    <?php if (!empty($monitorias_filtradas)): ?>
        <div class="monitorias-grid">
            <?php foreach ($monitorias_filtradas as $monitoria):
                $percentage = getInscricaoPercentage($monitoria);
                $color = getInscricaoColor($percentage);
            ?>
                <div class="monitoria-card">
                    <div class="card-header" style="background: <?php echo $cor_disciplina; ?>">
                        <div>
                            <div class="card-title"><?php echo $monitoria['disciplina']; ?></div>
                            <div class="card-subtitle"><?php echo $monitoria['ano']; ?>º Ano</div>
                        </div>
                        <span class="status-badge"><?php echo getStatusLabel($monitoria['status']); ?></span>
                    </div>

                    <div class="card-body">
                        <div class="progress-section">
                            <div class="progress-header">
                                <span class="progress-title">Taxa de Inscrição</span>
                                <span class="progress-percentage" style="color: <?php echo $color; ?>;">
                                    <?php echo $percentage; ?>%
                                </span>
                            </div>

                            <div class="bar-chart-container">
                                <div class="bar-chart-fill"
                                    style="width: 0%; background: linear-gradient(90deg, <?php echo $color; ?> 0%, <?php echo $color; ?>dd 100%);"
                                    data-width="<?php echo $percentage; ?>%">
                                    <?php if ($percentage > 15): ?>
                                        <span class="bar-chart-label">
                                            <?php echo $monitoria['alunos_inscritos']; ?>/<?php echo $monitoria['capacidade_maxima']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="progress-stats">
                                <div class="progress-stat">
                                    <div class="progress-stat-value"><?php echo $monitoria['alunos_inscritos']; ?></div>
                                    <div class="progress-stat-label">Inscritos</div>
                                </div>
                                <div class="progress-stat">
                                    <div class="progress-stat-value" style="color: var(--neutral);">
                                        <?php echo $monitoria['capacidade_maxima'] - $monitoria['alunos_inscritos']; ?>
                                    </div>
                                    <div class="progress-stat-label">Vagas</div>
                                </div>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-item-label">Sala</div>
                                <div class="info-item-value">Sala <?php echo $monitoria['sala']; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-item-label">acontecerá em</div>
                                <div class="info-item-value"><?php echo date('d/m/Y', strtotime($monitoria['data_criacao'])); ?></div>
                            </div>
                        </div>

                        <div class="horario-container">
                            <div class="horario-content">
                                <div class="horario-label">Horário</div>
                                <div class="horario-value"><?php echo $monitoria['horario']; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <a href="relatorio.php?id=<?php echo $monitoria['id']; ?>" class="btn btn-view"><span>Ver Detalhes</span></a>
                        <a href="../src/controllers/excluir_monitoria.php?id=<?php echo $monitoria['id']; ?>" class="btn btn-excluir"><span>Excluir</span></a>
                        <a class="btn btn-edit" href="editar_monitoria.php?id=<?php echo $monitoria['id']; ?>"><span>Editar</span></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-text">Nenhuma monitoria encontrada nesta categoria</div>
            <button class="btn-create">Criar Nova Monitoria</button>
        </div>
    <?php endif; ?>
</div>

<button class="fab" title="Criar nova monitoria">
    <p class="adicionar">+</p>
</button>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bars = document.querySelectorAll('.bar-chart-fill');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const targetWidth = bar.getAttribute('data-width');
                    setTimeout(() => {
                        bar.style.width = targetWidth;
                    }, 100);
                    observer.unobserve(bar);
                }
            });
        }, {
            threshold: 0.1
        });

        bars.forEach(bar => observer.observe(bar));
    });

    document.querySelector('.fab').addEventListener('click', function() {
        window.location.href = 'criar_monitoria.php';
    });

    document.querySelector('.btn-create').addEventListener('click', function() {
        window.location.href = 'criar_monitoria.php';
    });
</script>

<?php

include_once __DIR__ . '/footer.php';

?>