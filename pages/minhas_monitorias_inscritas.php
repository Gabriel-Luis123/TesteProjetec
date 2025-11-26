<?php
$nameCSS = 'minhas_monitorias_inscritas';
$titlePage = 'Minhas Inscrições - Sistema de Monitorias';

require_once __DIR__ . '/header.php';

require_once __DIR__ . '/../src/controllers/monitorias_inscritas_dados.php';




if($inscricoes !== null){
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
    $inscricoes_filtradas = $filtro === 'todos' 
    ? $inscricoes 
    : array_filter($inscricoes, fn($m) => $m['status'] === $filtro);
    $total_inscricoes = count($inscricoes);
    $ativas = count(array_filter($inscricoes, fn($m) => $m['status'] === 'ativo'));
    $em_espera = count(array_filter($inscricoes, fn($m) => $m['status'] === 'em_espera'));
    $em_espera_geral = $ativas + $em_espera;
    $concluidas = count(array_filter($inscricoes, fn($m) => $m['status'] === 'concluido'));
} else {
    $total_inscricoes = 0;
    $ativas = 0;
    $em_espera = 0;
    $em_espera_geral = $ativas + $em_espera;
    $concluidas = 0;
}


function getStatusColor($status) {
    $colors = [
        'em_espera' => '#f59e0b',
        'concluido' => '#6b7280'
    ];
    return $colors[$status] ?? '#6b7280';
}

function getStatusLabel($status) {
    $labels = [
        'em_espera' => 'Em Espera',
        'concluido' => 'Concluído'
    ];
    return $labels[$status] ?? 'Desconhecido';
}

function getInscricaoPercentage($monitoria) {
    if ($monitoria['capacidade_maxima'] == 0) return 0;
    return round(($monitoria['alunos_inscritos'] / $monitoria['capacidade_maxima']) * 100);
}

function getInscricaoColor($percentage) {
    if ($percentage >= 80) return '#ef4444'; 
    if ($percentage >= 50) return '#f59e0b';
    return '#10b981';
}

function diasRestantes($data) {
    $tz = new DateTimeZone('America/Sao_Paulo');

    $hoje = new DateTime('today', $tz);
    $proxima = new DateTime($data, $tz);
    $proxima->setTime(0, 0, 0);

    $diff = $hoje->diff($proxima);

    if ($diff->invert) {
        return 'Sessão passada';
    }

    if ($diff->days == 0) {
        return 'Hoje';
    } elseif ($diff->days == 1) {
        return 'Amanhã';
    } else {
        return $diff->days . ' dias';
    }
}


?>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Minhas Inscrições</h1>
            <p class="page-description">Acompanhe todas as monitorias em que você está inscrito</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_inscricoes; ?></div>
                <div class="stat-label">Total de Inscrições</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning);"><?php echo $em_espera_geral; ?></div>
                <div class="stat-label">Em Espera</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--neutral);"><?php echo $concluidas; ?></div>
                <div class="stat-label">Concluídas</div>
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
                        Concluídas
                    </button>
                </a>
            </div>
        </div>

        <?php if (!empty($inscricoes_filtradas)): ?>
        <div class="monitorias-grid">
            <?php foreach ($inscricoes_filtradas as $inscricao): 
                $percentage = getInscricaoPercentage($inscricao);
                $color = getInscricaoColor($percentage);
                $dias = diasRestantes($inscricao['data_proxima_sessao']);
                $countdown_class = '';
                if ($dias === 'Hoje' || $dias === 'Amanhã') {
                    $countdown_class = 'warning';
                } elseif ($dias === 'Sessão passada') {
                    $countdown_class = 'past';
                }
            ?>
            <div class="monitoria-card">
                <div class="card-header" style="background: <?php echo $inscricao['cor']; ?>">
                    <div>
                        <div class="card-title"><?php echo $inscricao['disciplina']; ?></div>
                        <div class="card-subtitle"><?php echo $inscricao['materia']; ?> - <?php echo $inscricao['ano']; ?>º Ano</div>
                    </div>
                    <span class="status-badge"><?php echo getStatusLabel($inscricao['status']); ?></span>
                </div>

                <div class="card-body">
                    <div class="progress-section">
                        <div class="progress-header">
                            <span class="progress-title">Ocupação da Monitoria</span>
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
                                        <?php echo $inscricao['alunos_inscritos']; ?>/<?php echo $inscricao['capacidade_maxima']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="progress-stats">
                            <div class="progress-stat">
                                <div class="progress-stat-value"><?php echo $inscricao['alunos_inscritos']; ?></div>
                                <div class="progress-stat-label">Inscritos</div>
                            </div>
                            <div class="progress-stat">
                                <div class="progress-stat-value" style="color: var(--neutral);">
                                    <?php echo $inscricao['capacidade_maxima'] - $inscricao['alunos_inscritos']; ?>
                                </div>
                                <div class="progress-stat-label">Vagas Restantes</div>
                            </div>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <img src="<?php echo $inscricao['foto']; ?>" class="info-icon"/>
                            <div class="info-content">
                                <div class="info-label">Monitor</div>
                                <div class="info-value"><?php echo $inscricao['monitor']; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">📚</div>
                            <div class="info-content">
                                <div class="info-label">Professores Responsáveis</div>
                                <div class="info-value"><?php echo $inscricao['professor']; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">🕐</div>
                            <div class="info-content">
                                <div class="info-label">Horário</div>
                                <div class="info-value"><?php echo $inscricao['horario']; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">📍</div>
                            <div class="info-content">
                                <div class="info-label">Local</div>
                                <div class="info-value">Sala <?php echo $inscricao['sala']; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">📅</div>
                            <div class="info-content">
                                <div class="info-label">Sessão</div>
                                <div class="info-value">
                                    <?php echo date('d/m/Y', strtotime($inscricao['data_proxima_sessao'])); ?>
                                    <span class="countdown-badge <?php echo $countdown_class; ?>">
                                        <?php echo $dias; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="visualizacao_monitoria.php?id=<?php echo $inscricao['id']; ?>" class="btn btn-details">Ver Detalhes</a>
                    <button class="btn btn-cancel" onclick="cancelarInscricao(<?php echo $inscricao['id']; ?>, '<?php echo $inscricao['disciplina']; ?>')">
                        Cancelar Inscrição
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <div class="empty-text">Você ainda não está inscrito em nenhuma monitoria</div>
            <button class="btn-browse" onclick="window.location.href='disciplinas.php'">
                Explorar Monitorias
            </button>
        </div>
        <?php endif; ?>
    </div>

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
            }, { threshold: 0.1 });

            bars.forEach(bar => observer.observe(bar));
        });

        function cancelarInscricao(id, disciplina) {
            if (confirm(`Tem certeza que deseja cancelar sua inscrição em ${disciplina}?`)) {
                fetch('../src/controllers/cancelar-inscricao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ monitoria_id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Inscrição cancelada com sucesso!');
                        window.location.reload();
                    } else {
                        alert('Erro ao cancelar inscrição. Tente novamente.');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao cancelar inscrição. Tente novamente.');
                });
            }
        }
    </script>

<?php 

require_once __DIR__ . '/footer.php';
?>
