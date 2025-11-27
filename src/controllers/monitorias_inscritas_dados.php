<?php

require_once __DIR__.'/../utils/con_db.php';
require_once __DIR__.'/../utils/cores_monitor.php';
require_once __DIR__.'/../utils/lista_professores.php';
require_once __DIR__.'/../utils/publico_alvo.php';

$raAluno = $_SESSION['registro'];

$sql = "
    SELECT 
        m.ID_Monitoria,
        a.Nome AS monitor_nome,
        a.Foto_Perfil AS foto_monitor,
        m.Conteudos_Abordados,
        m.Disciplina,
        m.Localizacao,
        m.Data,
        m.Horario,
        m.Capacidade_Alunos,
        m.Quantidade_Inscritos,
        m.Publico_Alvo,
        m.Concluida
    FROM Alunos_Inscritos ai
    JOIN Monitoria m ON ai.ID_Monitoria = m.ID_Monitoria
    JOIN Aluno a ON m.Registro_Academico = a.Registro_Academico
    WHERE ai.Registro_Academico = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$raAluno]);
$monitorias = $stmt->fetchAll(PDO::FETCH_ASSOC);


$lista_final = [];

foreach ($monitorias as $row) {
    $disciplinaCompleta = $row['Disciplina'];   
    $partes = explode("-", $disciplinaCompleta);
    $materia = $partes[0];                       
    $ano = isset($partes[1]) ? $partes[1] : "1"; 

    $cor = $cores_lista_monitorias[$materia] ?? "#4b5563";

    $professores = $professores_monitorias[$materia] ?? ["Professor não definido"];

    $publico = $publico_alvo[$materia] ?? "Alunos interessados";

    $status = $row['Concluida'] ? "concluido" : "em_espera";
    
    $raw = $row['Conteudos_Abordados'];

    $raw = trim($raw);
    $raw = trim($raw, "\"");

    $raw = stripslashes($raw);

    $conteudosArray = json_decode($raw, true);

    if (!is_array($conteudosArray)) {

        $raw2 = trim($raw, "\"");
        $conteudosArray = json_decode($raw2, true);
    }

    if (is_array($conteudosArray)) {

        $conteudosArray = array_map(function($item) {
            return ucwords(mb_strtolower(trim($item)));
        }, $conteudosArray);

        $conteudosString = implode(', ', $conteudosArray);

    } else {
        $conteudosString = $monitoria['Conteudos_Abordados'];
    }


    $inscricoes[] = [
        'id' => $row['ID_Monitoria'],
        'disciplina' => $conteudosString,      
        'materia' => $materia,                            
        'ano' => $ano,                                    
        'foto' => $row['foto_monitor'],
        'monitor' => $row['monitor_nome'],
        'professor' => implode(", ", $professores),
        'horario' => date("H\hi", strtotime($row['Horario'])),
        'sala' => $row['Localizacao'],
        'data_proxima_sessao' => $row['Data'],
        'publico_alvo' => $publico,
        'alunos_inscritos' => $row['Quantidade_Inscritos'],
        'capacidade_maxima' => $row['Capacidade_Alunos'],
        'status' => $status,
        'cor' => $cor
    ];
}

?>
