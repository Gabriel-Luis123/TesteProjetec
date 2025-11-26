<?php 

require_once __DIR__ . '/../utils/con_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../pages/adminPage.php?erro=metodo_invalido");
    exit;
}

$sql = "SELECT Registro_Academico FROM Aluno";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lista_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($lista_users as $userId) {

    $monitorMarcado = isset($_POST["monitor_$userId"]);
    
    $sqlUpdate = "UPDATE Aluno SET E_Monitor = :monitor WHERE Registro_Academico = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ':monitor' => $monitorMarcado ? 1 : 0,
        ':id' => $userId
    ]);

    $sqlDelete = "DELETE FROM Monitora WHERE Registro_Academico = :id";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([':id' => $userId]);

    if (!$monitorMarcado) {
        continue;
    }

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
