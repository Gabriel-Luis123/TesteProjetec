<?php
require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/publico_alvo.php';
session_start();

$registro = $_SESSION['registro'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = $_POST['data'];
    $horario = $_POST['horario_inicio'];
    $sala = $_POST['sala'];
    $capacidade = $_POST['capacidade'];
    $conteudos = $_POST['conteudos'];

    $sql_disciplina = "SELECT Disciplina_Monitorada 
                       FROM Monitora 
                       WHERE Registro_Academico = :registro";

    $stmt_disciplina = $pdo->prepare($sql_disciplina);
    $stmt_disciplina->bindParam('registro', $registro);
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

    $sql_insert = "INSERT INTO Monitoria 
        (Registro_Academico, Publico_Alvo, Localizacao, Data, Horario, 
        Capacidade_Alunos, Quantidade_Inscritos, Conteudos_Abordados, Disciplina, Concluida)
        VALUES
        (:registro, :publico, :sala, :data, :horario, 
        :capacidade, 0, :conteudos, :disciplina, 0)";

    $stmt = $pdo->prepare($sql_insert);

    $stmt->bindParam(':registro', $registro);
    $stmt->bindParam(':publico', $publico);
    $stmt->bindParam(':sala', $sala);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':horario', $horario);
    $stmt->bindParam(':capacidade', $capacidade, PDO::PARAM_INT);
    $stmt->bindParam(':conteudos', $conteudos);
    $stmt->bindParam(':disciplina', $disciplina_completa);

    if ($stmt->execute()) {
        header('Location: ../../pages/minhas_monitorias.php?mensagem=cadastrada_com_sucesso');
        exit;
    } else {
        header('Location: ../../pages/minhas_monitorias.php?mensagem=erro_ao_cadastrar_monitorias');
        exit;
    }
}
