<?php
session_start();
require_once __DIR__ . '/../utils/con_db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de monitoria inválido.");
}
$monitoriaId = (int) $_GET['id'];

$sql = "SELECT Registro_Academico FROM Monitoria WHERE ID_Monitoria = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $monitoriaId]);

if (!$stmt->fetch()) {
    die("Monitoria não encontrada.");
}

$acao = $_POST['acao'] ?? '';

switch ($acao) {

    case "marcar_presenca":

        $sqlAlunos = "
            SELECT Registro_Academico AS id
            FROM Alunos_Inscritos
            WHERE ID_Monitoria = :id
        ";
        $stmtAlu = $pdo->prepare($sqlAlunos);
        $stmtAlu->execute([':id' => $monitoriaId]);
        $alunos = $stmtAlu->fetchAll(PDO::FETCH_ASSOC);

        foreach ($alunos as $aluno) {
            $id = $aluno['id'];

            $marcado = isset($_POST["aluno_$id"]) ? 1 : 0;

            $pdo->prepare("
                UPDATE Alunos_Inscritos
                SET Presenca_Confirmada = :p
                WHERE Registro_Academico = :aluno
                AND ID_Monitoria = :mon
            ")->execute([
                ':p' => $marcado,
                ':aluno' => $id,
                ':mon' => $monitoriaId
            ]);
        }

        $_SESSION['mensagem'] = "Presenças atualizadas com sucesso!";
        break;

    case "salvar_feedback":

        $feedback = trim($_POST['feedback'] ?? "");

        $pdo->prepare("
            UPDATE Monitoria
            SET Feedback = :f
            WHERE ID_Monitoria = :id
        ")->execute([
            ':f' => $feedback,
            ':id' => $monitoriaId
        ]);

        $_SESSION['mensagem'] = "Feedback salvo com sucesso!";
        break;

    // =====================================================
    // 3. CONCLUIR MONITORIA
    // =====================================================
    case "concluir_monitoria":

        $pdo->prepare("
            UPDATE Monitoria
            SET Concluida = 1
            WHERE ID_Monitoria = :id
        ")->execute([':id' => $monitoriaId]);

        $_SESSION['mensagem'] = "Monitoria concluída!";
        break;

    // =====================================================
    // 4. REABRIR MONITORIA
    // =====================================================
    case "cancelar_conclusao":

        $pdo->prepare("
            UPDATE Monitoria
            SET Concluida = 0
            WHERE ID_Monitoria = :id
        ")->execute([':id' => $monitoriaId]);

        $_SESSION['mensagem'] = "Monitoria reaberta!";
        break;

    default:
        $_SESSION['mensagem'] = "Ação inválida.";
}

// ===============================
// REDIRECIONAR DE VOLTA À PÁGINA
// ===============================
header("Location: ../../pages/relatorio.php?id=$monitoriaId");
exit;
