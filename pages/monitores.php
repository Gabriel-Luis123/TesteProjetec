<?php


$nameCSS = 'monitores';
$title = 'Monitores - MoniFácil';
require_once __DIR__ . '/header.php';

require_once __DIR__ . '/../src/controllers/monitores_backend.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredMonitores = $monitores;

if (!empty($search)) {
    $filteredMonitores = array_filter($monitores, function ($monitor) use ($search) {
        $searchLower = mb_strtolower($search);
        return
            stripos($monitor['nome'], $search) !== false ||
            stripos($monitor['disciplina_monitorada'], $search) !== false ||
            stripos($monitor['email'], $search) !== false ||
            stripos($monitor['turma'], $search) !== false;
    });
}

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
    <div class="header">
        <h1>Monitores Disponíveis</h1>
        <p>Encontre o monitor ideal para a disciplina que você precisa</p>
    </div>

    <div class="search-container">
        <form method="GET" action="" class="search-box">
            <input
                type="text"
                name="search"
                id="searchInput"
                placeholder="Buscar por nome, disciplina, turma ou email..."
                value="<?php echo htmlspecialchars($search); ?>"
                autocomplete="off">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <?php if (!empty($search)): ?>
        <div class="results-count">
            <span><?php echo count($filteredMonitores); ?></span> resultado(s) encontrado(s) para "<?php echo htmlspecialchars($search); ?>"
        </div>
    <?php endif; ?>

    <?php if (empty($filteredMonitores)): ?>
        <div class="no-results">
            <h2>Nenhum monitor encontrado</h2>
            <p>Tente buscar por outro termo ou limpe o filtro de pesquisa</p>
        </div>
    <?php else: ?>
        <div class="monitors-grid">
            <?php foreach ($filteredMonitores as $index => $monitor): ?>
                <div class="monitor-card" data-registro="<?php echo $monitor['id']; ?>">
                    <div class="card-header color-<?php echo ($index % 6) + 1; ?>"  style="background: <?php echo $monitor['cor']; ?>">
                        <h2 class="monitor-name"><?php echo htmlspecialchars($monitor['nome']); ?></h2>
                        <img src="<?php echo htmlspecialchars($monitor['foto']); ?>" alt="<?php echo htmlspecialchars($monitor['nome']); ?>" class="monitor-photo">
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($monitor['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Telefone:</span>
                            <span class="info-value"><?php echo htmlspecialchars(formatarTelefone($monitor['telefone'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Turma:</span>
                            <span class="info-value"><?php echo htmlspecialchars($monitor['turma']); ?></span>
                        </div>
                        <div class="discipline-badge">
                            <?php echo htmlspecialchars($monitor['disciplina_monitorada']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll(".monitor-card");

    cards.forEach(card => {
        card.addEventListener("click", () => {
            const registro = card.dataset.registro;

            window.open(`visualizacao_monitor.php?registro=${registro}`, "_self");
        });
    });
});
</script>



<?php
require_once __DIR__ . '/footer.php';

?>