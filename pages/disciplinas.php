<?php
$nameCSS = 'disciplina';
$titlePage = 'Monitorias Disponíveis';
include_once __DIR__ . '/header.php';

$mensagem = !isset($_GET['mensagem']) ? '' : $_GET['mensagem'];
require_once __DIR__ . '/../src/controllers/disciplinas_dados.php';

// Cores por matéria
require_once __DIR__ . '/../src/utils/cores_monitor.php';
$cores_materias = $cores_lista_monitorias;
?>
<div class="container">
    <header>
        <h1>Monitorias Disponíveis</h1>
        <p>Encontre a monitoria perfeita para você e aprimore seus conhecimentos</p>
    </header>

    <!-- Barra de pesquisa -->
    <div class="search-container">
        <input
            type="text"
            class="search-input"
            id="searchInput"
            placeholder="Pesquisar monitorias por monitor, horário, local ou descrição..."
            onkeyup="filtrarMonitorias()">
    </div>

    <?php if ($mensagem): ?>
        <div class="mensagem-sucesso" id="mensagem-sucesso">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>


    <!-- ============================= -->
    <!-- CRIA TODAS AS MATÉRIAS       -->
    <!-- ============================= -->
    <?php foreach ($cores_materias as $materia => $cor): ?>

        <?php
        // Filtrar monitorias pertencentes a essa matéria
        $monitorias_materia = array_filter($monitorias, function($m) use ($materia) {
            return strtolower($m['materia']) === strtolower($materia);
        });
        ?>

        <div class="materia-section" data-materia="<?= $materia ?>">
            <!-- HEADER DA MATÉRIA -->
            <div class="materia-header" onclick="toggleMateria('<?= $materia ?>')">
                <div class="materia-title-container">
                    <div class="materia-icon" style="background: <?= $cor ?>">
                        <?= strtoupper(substr($materia, 0, 1)) ?>
                    </div>
                    <h2><?= $materia ?></h2>
                </div>

                <button class="toggle-btn" id="toggle-<?= $materia ?>" onclick="event.stopPropagation(); toggleMateria('<?= $materia ?>')">
                    ▼
                </button>
            </div>

            <div class="monitorias-grid" id="grid-<?= $materia ?>">

                <?php if (empty($monitorias_materia)): ?>

                    <p class="sem-monitorias" style="color: white;">
                        Nenhuma monitoria disponível para esta matéria.
                    </p>

                <?php else: ?>

                    <!-- ============================= -->
                    <!-- LISTAGEM DOS CARDS           -->
                    <!-- ============================= -->
                    <?php foreach ($monitorias_materia as $monitoria): ?>
                        <div class="monitoria-card"
                            data-monitor="<?= strtolower($monitoria['monitor']) ?>"
                            data-horario="<?= strtolower($monitoria['horario']) ?>"
                            data-local="<?= strtolower($monitoria['local']) ?>"
                            onclick="abrirModal(<?= $monitoria['id'] ?>)">

                            <div class="monitor-info">
                                <span>Monitor:</span>
                                <strong><?= $monitoria['monitor'] ?></strong>
                            </div>

                            <span class="publico-badge">
                                <?= $monitoria['publico_alvo'] ?>
                            </span>

                            <div class="info-item">
                                <span><?= $monitoria['dia_semana'] ?> <?= $monitoria['horario'] ?></span>
                            </div>

                            <div class="info-item">
                                <span>Sala <?= $monitoria['local'] ?></span>
                            </div>

                            <!-- Barra de vagas -->
                            <div class="vagas-info">
                                <div class="vagas-text">
                                    Vagas: <?= $monitoria['vagas_disponiveis'] ?> / <?= $monitoria['vagas'] ?>
                                </div>
                                <?php
                                $percentual = ($monitoria['vagas_disponiveis'] / $monitoria['vagas']) * 100;
                                $bar_class =
                                    $percentual > 50 ? 'vagas-alta' :
                                    ($percentual > 20 ? 'vagas-media' : 'vagas-baixa');
                                ?>
                                <div class="vagas-bar-container">
                                    <div class="vagas-bar-fill <?= $bar_class ?>" style="width: <?= $percentual ?>%">
                                        <?= round($percentual) ?>%
                                    </div>
                                </div>
                            </div>

                            <button class="btn-detalhes" onclick="event.stopPropagation(); abrirModal(<?= $monitoria['id'] ?>)">
                                Ver Detalhes e Inscrever-se
                            </button>
                        </div>

                        <!-- ============================= -->
                        <!-- MODAL                         -->
                        <!-- ============================= -->
                        <div id="modal-<?= $monitoria['id'] ?>" class="modal" onclick="fecharModal(<?= $monitoria['id'] ?>)">
                            <div class="modal-content" onclick="event.stopPropagation()">
                                <button class="close-modal" onclick="fecharModal(<?= $monitoria['id'] ?>)">×</button>

                                <div class="modal-header">
                                    <h2><?= $monitoria['materia'] ?></h2>
                                    <div class="monitor-info">
                                        <span>Monitor:</span>
                                        <strong><?= $monitoria['monitor'] ?></strong>
                                        <img src="<?= $monitoria['foto'] ?>" class="foto-info" alt="">
                                    </div>
                                </div>

                                <div class="modal-body">
                                    <div class="modal-info-group">
                                        <h3>Descrição</h3>
                                        <p>Aula com foco em <?= $monitoria['descricao'] ?></p>
                                    </div>

                                    <div class="modal-info-group">
                                        <h3>Professores Responsáveis</h3>
                                        <p><?= $monitoria['professores'] ?></p>
                                    </div>

                                    <div class="modal-info-group">
                                        <h3>Público Alvo</h3>
                                        <p><?= $monitoria['publico_alvo'] ?></p>
                                    </div>

                                    <div class="modal-info-group">
                                        <h3>Informações</h3>
                                        <p><strong>Data:</strong> <?= $monitoria['data_formatada'] ?></p>
                                        <p><strong>Horário:</strong> <?= $monitoria['horario'] ?></p>
                                        <p><strong>Local:</strong> <?= $monitoria['local'] ?></p>
                                        <p><strong>Vagas:</strong> <?= $monitoria['vagas_disponiveis'] ?> de <?= $monitoria['vagas'] ?></p>
                                    </div>

                                    <form method="POST" action="../src/controllers/inscrever_monitoria.php?id_monitoria=<?= $monitoria['id'] ?>" style="margin-top: 2rem;">
                                        <input type="hidden" name="monitoria_id" value="<?= $monitoria['id'] ?>">

                                        <?php if (in_array($_SESSION['registro'], $monitoria['inscritos'])): ?>
                                            <button type="submit" name="cancelar" class="btn-cancelar">
                                                Cancelar Inscrição
                                            </button>

                                        <?php elseif ($_SESSION['registro'] === $monitoria['RaMonitor']): ?>
                                            <button type="button" disabled class="btn-proibido">
                                                Essa monitoria é sua
                                            </button>

                                        <?php else: ?>
                                            <button type="submit" name="inscricao" class="btn-inscrever">
                                                Inscrever-se
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    <?php endforeach; ?>

</div>



<script>
document.addEventListener('DOMContentLoaded', function () {
    const msg = document.getElementById('mensagem-sucesso');
    if (msg) {
        setTimeout(() => {
            msg.style.opacity = "0";
            msg.style.transition = "0.5s";
            setTimeout(() => msg.remove(), 500);
        }, 3000);
    }
});
</script>

<script>
function toggleMateria(materia) {
    const grid = document.getElementById('grid-' + materia);
    const toggleBtn = document.getElementById('toggle-' + materia);

    grid.classList.toggle('expanded');
    toggleBtn.classList.toggle('rotated');
}


function removerAcentos(texto) {
    return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}


function filtrarMonitorias() {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = removerAcentos(searchInput.value.toLowerCase());
    const cards = document.querySelectorAll('.monitoria-card');

    cards.forEach(card => {
        const monitor = removerAcentos(card.getAttribute('data-monitor') || '').toLowerCase();
        const horario = removerAcentos(card.getAttribute('data-horario') || '').toLowerCase();
        const local = removerAcentos(card.getAttribute('data-local') || '').toLowerCase();
        const descricao = removerAcentos(card.getAttribute('data-descricao') || '').toLowerCase();

        const match =
            monitor.includes(searchTerm) ||
            horario.includes(searchTerm) ||
            local.includes(searchTerm) ||
            descricao.includes(searchTerm);

        card.style.display = (match || searchTerm === '') ? 'block' : 'none';
    });

    const grids = document.querySelectorAll('.monitorias-grid');
    const toggles = document.querySelectorAll('.toggle-btn');

    if (searchTerm !== '') {
        grids.forEach(grid => grid.classList.add('expanded'));
        toggles.forEach(btn => btn.classList.add('rotated'));
    } else {
        grids.forEach(grid => grid.classList.remove('expanded'));
        toggles.forEach(btn => btn.classList.remove('rotated'));
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
        document.body.style.overflow = 'auto';
    }
});
</script>

<?php include_once __DIR__ . '/footer.php'; ?>
