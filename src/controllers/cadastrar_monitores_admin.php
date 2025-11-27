<?php

require_once __DIR__ . '/../utils/con_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../pages/adminPage.php?erro=metodo_invalido");
    exit;
}

// pega todos os RAs
$sql = "SELECT Registro_Academico FROM Aluno";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lista_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($lista_users as $userId) {

    // verifica se o admin marcou o aluno como monitor
    $monitorMarcado = isset($_POST["monitor_$userId"]);
    
    // atualiza o campo E_Monitor
    $sqlUpdate = "UPDATE Aluno SET E_Monitor = :monitor WHERE Registro_Academico = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ':monitor' => $monitorMarcado ? 1 : 0,
        ':id' => $userId
    ]);

    // apaga SEMPRE da tabela Monitora (matérias antigas)
    $sqlDelete = "DELETE FROM Monitora WHERE Registro_Academico = :id";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([':id' => $userId]);

    // SE NÃO FOR MONITOR → apagar da tabela Monitoria,
    // mas primeiro remover dependências em alunos_inscritos
    if (!$monitorMarcado) {

        try {
            // inicia transação
            $pdo->beginTransaction();

            // pega os IDs das monitorias desse aluno
            $sqlIds = "SELECT ID_Monitoria FROM Monitoria WHERE Registro_Academico = :id";
            $stmtIds = $pdo->prepare($sqlIds);
            $stmtIds->execute([':id' => $userId]);
            $ids = $stmtIds->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ids)) {
                // construí a lista de placeholders para IN (?, ?, ...)
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                // 1) Deleta inscritos que referenciam essas monitorias
                $sqlDelInscritos = "DELETE FROM alunos_inscritos WHERE ID_Monitoria IN ($placeholders)";
                $stmtDelInscritos = $pdo->prepare($sqlDelInscritos);
                $stmtDelInscritos->execute($ids);

                // 2) Agora apaga as monitorias do aluno
                $sqlDeletea = "DELETE FROM Monitoria WHERE ID_Monitoria IN ($placeholders)";
                $stmtDeletea = $pdo->prepare($sqlDeletea);
                $stmtDeletea->execute($ids);
            }

            // commit se deu tudo certo
            $pdo->commit();
        } catch (Exception $e) {
            // desfaz se houver erro
            $pdo->rollBack();
            // opcional: log do erro e redirecionar com mensagem
            error_log("Erro ao deletar monitorias do RA {$userId}: " . $e->getMessage());
            header("Location: ../../pages/adminPage.php?erro=deletar_monitorias");
            exit;
        }

        continue; // vai para o próximo aluno
    }

    // matérias selecionadas (array)
    $materiasSelecionadas = $_POST["subjects_$userId"] ?? [];

    if (!empty($materiasSelecionadas)) {

        $sqlInsert = "INSERT INTO Monitora (Registro_Academico, Disciplina_Monitorada)
                      VALUES (:id, :materia)";
        $stmtInsert = $pdo->prepare($sqlInsert);

        foreach ($materiasSelecionadas as $materia) {
            $stmtInsert->execute([
                ':id' => $userId,
                ':materia' => $materia
            ]);
        }
    }
}

header("Location: ../../pages/adminPage.php?success=1");
exit;
