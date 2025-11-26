<?php
require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/publico_alvo.php';
session_start();

$registro = $_SESSION['registro'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_monitoria = $_GET['id'];
    $data = $_POST['data'];
    $horario = $_POST['horario_inicio'];
    $sala = $_POST['sala'];
    $capacidade = $_POST['capacidade'];
    $conteudos = $_POST['conteudos'];

    $conteudos_json = json_encode($conteudos, JSON_UNESCAPED_UNICODE);

    $sql_disciplina = "SELECT Disciplina_Monitorada 
                       FROM Monitora 
                       WHERE Registro_Academico = :registro";

    $stmt_disciplina = $pdo->prepare($sql_disciplina);
    $stmt_disciplina->bindParam(":registro", $registro);
    $stmt_disciplina->execute();

    $resultado_disciplina = $stmt_disciplina->fetch(PDO::FETCH_ASSOC);

    if (!$resultado_disciplina) {
        header('Location: ../../pages/minhas_monitorias.php?mensagem=erro_monitor');
        exit;
    }

    $disciplina_completa = $resultado_disciplina['Disciplina_Monitorada'];

    if (!isset($publico_alvo_materias[$disciplina_completa])) {
        header('Location: ../../pages/minhas_monitorias.php?mensagem=erro_materia_monitor');
        exit;
    }

    $publico = $publico_alvo_materias[$disciplina_completa];

    $sql_update = "UPDATE Monitoria SET
        Publico_Alvo = :publico,
        Localizacao = :sala,
        Data = :data,
        Horario = :horario,
        Capacidade_Alunos = :capacidade,
        Conteudos_Abordados = :conteudos,
        Disciplina = :disciplina
        WHERE ID_Monitoria = :id_monitoria
        AND Registro_Academico = :registro";

    $stmt = $pdo->prepare($sql_update);

    $stmt->bindParam(':publico', $publico);
    $stmt->bindParam(':sala', $sala);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':horario', $horario);
    $stmt->bindParam(':capacidade', $capacidade, PDO::PARAM_INT);
    $stmt->bindParam(':conteudos', $conteudos_json);
    $stmt->bindParam(':disciplina', $disciplina_completa);
    $stmt->bindParam(':id_monitoria', $id_monitoria);
    $stmt->bindParam(':registro', $registro);

    if ($stmt->execute()) {
        header('Location: ../../pages/minhas_monitorias.php?mensagem=monitoria_editada');
        exit;
    } else {
        header('Location: ../../pages/minhas_monitorias.php?mensagem=erro_editar');
        exit;
    }
}
