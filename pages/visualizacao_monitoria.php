<?php
$nameCSS = 'visualizacao_monitoria';
$titlePage = "Detalhes da Monitoria";
require_once __DIR__ . '/header.php';


$id_monitoria = $_GET['id'];

$voltar = $_SERVER['HTTP_REFERER'] ?? 'disciplinas.php';

require_once __DIR__ . '/../src/controllers/dados_visualizacao_monitoria.php';

if (!$monitoria) {
    header('Location: minhas_monitorias_inscritas.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'inscrever' && !$esta_inscrito) {
            $_SESSION['inscricoes'][] = $monitoria_id;
            $esta_inscrito = true;
            $mensagem = "Inscrição realizada com sucesso!";
            $tipo_mensagem = "sucesso";
        } elseif ($_POST['acao'] === 'cancelar' && $esta_inscrito) {
            $key = array_search($monitoria_id, $_SESSION['inscricoes']);
            if ($key !== false) {
                unset($_SESSION['inscricoes'][$key]);
                $_SESSION['inscricoes'] = array_values($_SESSION['inscricoes']);
            }
            $esta_inscrito = false;
            $mensagem = "Inscrição cancelada com sucesso!";
            $tipo_mensagem = "aviso";
        }
    }
}

$percentual_ocupacao = ($monitoria['alunos_inscritos'] / $monitoria['vagas']) * 100;
$cor_ocupacao = $percentual_ocupacao >= 80 ? '#ef4444' : ($percentual_ocupacao >= 50 ? '#f59e0b' : '#10b981');

function diasParaSessao($data)
{
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

$dias_para_sessao = diasParaSessao($monitoria['proxima_sessao']);


?>
<style>
    :root {
        --primary: <?php echo $monitoria['cor']; ?>;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --neutral: #6b7280;
        --bg: #f8fafc;
        --card-bg: #ffffff;
        --text: #1e293b;
        --text-light: #64748b;
        --border: #e2e8f0;
    }

    .header-card {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary) 100%);
        color: white;
        padding: 2.5rem;
        border-radius: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .vagas-percentage {
        font-size: 1.5rem;
        font-weight: 700;
        color: <?php echo $cor_ocupacao; ?>;
    }


    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, <?php echo $cor_ocupacao; ?> 0%, <?php echo $cor_ocupacao; ?>dd 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.875rem;
        transition: width 1s ease;
        width: 0%;
    }


    .status-inscricao {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem;
        background: <?php echo $monitoria['inscrito'] ? '#d1fae5' : '#fef3c7'; ?>;
        border: 2px solid <?php echo $monitoria['inscrito'] ? '#10b981' : '#f59e0b'; ?>;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        font-weight: 600;
        color: <?php echo $monitoria['inscrito'] ? '#065f46' : '#92400e'; ?>;
    }
</style>
<div class="container">
    <a href="index.php" class="back-button">
        <span>←</span> Voltar para Monitorias
    </a>

    <?php if (isset($mensagem)): ?>
        <div class="mensagem <?php echo $tipo_mensagem; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <div class="header-card">
        <span class="materia-badge"><?php echo $monitoria['materia']; ?></span>
        <h1 class="disciplina-title"><?php echo $monitoria['disciplina']; ?></h1>

        <div class="monitor-info-header">
            <img class="monitor-avatar" src="<?php echo $monitoria['foto']; ?>" />
            <div class="monitor-details">
                <h3><?php echo $monitoria['monitor']; ?></h3>
                <p><?php echo $monitoria['email_monitor']; ?></p>
            </div>
        </div>
    </div>

    <div class="status-inscricao">
        <span class="status-icon"><?php echo $monitoria['inscrito'] ? '✓' : 'ℹ'; ?></span>
        <span>
            <?php echo $monitoria['inscrito'] ? 'Você está inscrito nesta monitoria' : 'Você ainda não está inscrito nesta monitoria'; ?>
        </span>
    </div>

    <div class="content-grid">
        <div class="card">
            <h2 class="card-title">
                Informações Gerais
            </h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-content">
                        <div class="info-label">Data da Sessão</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($monitoria['proxima_sessao'])); ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-content">
                        <div class="info-label">Horário</div>
                        <div class="info-value"><?php echo $monitoria['horario']; ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-content">
                        <div class="info-label">Local</div>
                        <div class="info-value"><?php echo $monitoria['local']; ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-content">
                        <div class="info-label">Duração</div>
                        <div class="info-value">1 hora</div>
                    </div>
                </div>
            </div>

            <div class="vagas-section">
                <div class="vagas-header">
                    <span class="vagas-title">Ocupação de Vagas</span>
                    <span class="vagas-percentage"><?php echo round($percentual_ocupacao); ?>%</span>
                </div>

                <div class="progress-bar-container">
                    <div class="progress-bar-fill" id="progressBar">
                        <?php echo $monitoria['alunos_inscritos']; ?> de <?php echo $monitoria['vagas']; ?> vagas
                    </div>
                </div>

                <div class="vagas-stats">
                    <div class="vagas-stat">
                        <div class="vagas-stat-value"><?php echo $monitoria['vagas']; ?></div>
                        <div class="vagas-stat-label">Total de Vagas</div>
                    </div>
                    <div class="vagas-stat">
                        <div class="vagas-stat-value"><?php echo $monitoria['alunos_inscritos']; ?></div>
                        <div class="vagas-stat-label">Inscritos</div>
                    </div>
                    <div class="vagas-stat">
                        <div class="vagas-stat-value"><?php echo $monitoria['vagas_disponiveis']; ?></div>
                        <div class="vagas-stat-label">Disponíveis</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="proxima-sessao-card">
            <h3>Sessão</h3>
            <div class="sessao-data">
                <?php echo date('d/m/Y', strtotime($monitoria['proxima_sessao'])); ?> às <?php echo $monitoria['horario']; ?>
            </div>
            <span class="sessao-countdown"><?php echo $dias_para_sessao; ?></span>
        </div>

        <div class="card">
            <h2 class="card-title">
                Conteúdo Programático
            </h2>
            <ul class="lista-topicos">
                <?php foreach ($monitoria['conteudo_programatico'] as $topico): ?>
                    <li><?php echo $topico; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card">
            <h2 class="card-title">
                Professores Responsáveis
            </h2>
            <div class="professores-list">
                <?php
                $professores = explode(', ', $monitoria['professores']);
                foreach ($professores as $professor):
                ?>
                    <div class="professor-item">
                        <span class="professor-name"><?php echo $professor; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">
                Público Alvo
            </h2>
            <div class="info-item">
                <div class="info-content">
                    <div class="info-value"><?php echo $monitoria['publico_alvo']; ?></div>
                </div>
            </div>
        </div>

        <form method="POST" style="width: 100%;" action="../src/controllers/inscrever_monitoria.php?id_monitoria=<?php echo $monitoria['id']; ?>">
            <div class="action-buttons">
                <a href="minhas_monitorias_inscritas.php?mensagem=sucesso" class="btn btn-sair"  onclick="window.history.back();">
                    <span>✕</span> Sair
                </a>
                <?php if ($monitoria['inscrito']): ?>
                    <button type="submit" name="cancelar" value="cancelar" class="btn btn-danger">
                        <span>✕</span> Cancelar Inscrição
                    </button>
                <?php else: ?>
                    <?php if ($monitoria['vagas_disponiveis'] > 0): ?>
                        <button type="submit" name="inscricao" value="inscrever" class="btn btn-success">
                            <span>✓</span>Inscrever-se
                        </button>
                    <?php elseif ($monitoria['RaMonitor'] === $_SESSION['registro']): ?>
                        <button type="button" class="btn btn-danger" disabled style="opacity: 0.6; cursor: not-allowed;">
                            <span>⚠</span> Essa monitoria é sua
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-danger" disabled style="opacity: 0.6; cursor: not-allowed;">
                            <span>⚠</span> Vagas Esgotadas
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            document.getElementById('progressBar').style.width = '<?php echo $percentual_ocupacao; ?>%';
        }, 100);
    });
</script>

<?php 

require_once __DIR__ . '/footer.php';

?>