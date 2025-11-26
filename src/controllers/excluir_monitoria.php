<?php
require_once __DIR__ . '/../utils/con_db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../../pages/minhas_monitorias.php?mensagem=ID inválido");
    exit;
}

$monitoriaId = (int)$_GET['id'];

$sqlCheck = "SELECT ID_Monitoria FROM Monitoria WHERE ID_Monitoria = :id LIMIT 1";
$stmtCheck = $pdo->prepare($sqlCheck);
$stmtCheck->execute([':id' => $monitoriaId]);
$existe = $stmtCheck->fetchColumn();

if (!$existe) {
    header("Location: ../../pages/minhas_monitorias.php?mensagem=Monitoria não encontrada");
    exit;
}

$sqlDeleteAlunos = "
    DELETE FROM Alunos_Inscritos 
    WHERE ID_Monitoria = :id
";
$stmtDelAlunos = $pdo->prepare($sqlDeleteAlunos);
$stmtDelAlunos->execute([':id' => $monitoriaId]);

$sqlDeleteMonitoria = "
    DELETE FROM Monitoria
    WHERE ID_Monitoria = :id
";
$stmtDel = $pdo->prepare($sqlDeleteMonitoria);

if ($stmtDel->execute([':id' => $monitoriaId])) {
    header("Location: ../../pages/minhas_monitorias.php?mensagem=Monitoria excluída com sucesso");
    exit;
} else {
    header("Location: ../../pages/minhas_monitorias.php?mensagem=Erro ao excluir a monitoria");
    exit;
}
