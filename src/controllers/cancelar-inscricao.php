<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../utils/con_db.php';


if (!isset($_SESSION['registro'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não autenticado'
    ]);
    exit;
}

$ra = $_SESSION['registro'];


$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['monitoria_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ]);
    exit;
}

$monitoria_id = intval($input['monitoria_id']);

try {
    $sqlCheck = "SELECT * FROM Alunos_Inscritos 
                 WHERE Registro_Academico = :ra AND ID_Monitoria = :monitoria_id";
    $stmt = $pdo->prepare($sqlCheck);
    $stmt->bindParam(':ra', $ra);
    $stmt->bindParam(':monitoria_id', $monitoria_id);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Inscrição não encontrada'
        ]);
        exit;
    }

    $sqlDelete = "DELETE FROM Alunos_Inscritos 
                  WHERE Registro_Academico = :ra AND ID_Monitoria = :monitoria_id";
    $stmt = $pdo->prepare($sqlDelete);
    $stmt->bindParam(':ra', $ra);
    $stmt->bindParam(':monitoria_id', $monitoria_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Inscrição cancelada com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao cancelar inscrição'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno: ' . $e->getMessage()
    ]);
}
