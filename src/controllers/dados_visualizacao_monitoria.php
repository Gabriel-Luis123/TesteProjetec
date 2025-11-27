<?php

require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/cores_monitor.php';
require_once __DIR__ . '/../utils/lista_professores.php';
require_once __DIR__ . '/../utils/publico_alvo.php';

$monitoria = null;

if (!isset($id_monitoria)) {
    return;
}

if (!isset($_SESSION['registro'])) {
    return; 
}

$ra_aluno = $_SESSION['registro'];


$sql = "
    SELECT 
        m.ID_Monitoria,
        m.Registro_Academico,
        m.Disciplina,
        m.Conteudos_Abordados,
        m.Horario,
        m.Data,
        m.Localizacao,
        m.Capacidade_Alunos,
        m.Quantidade_Inscritos,
        m.Registro_Academico,
        a.Nome AS monitor,
        a.Email AS email_monitor,
        a.Foto_Perfil
    FROM monitoria m
    INNER JOIN aluno a ON a.Registro_Academico = m.Registro_Academico
    WHERE m.ID_Monitoria = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id_monitoria);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    return;
}


$sqlInscricao = "
    SELECT ID_Inscricao
    FROM Alunos_Inscritos
    WHERE Registro_Academico = :ra
      AND ID_Monitoria = :id_monitoria
    LIMIT 1
";

$stmtInscricao = $pdo->prepare($sqlInscricao);
$stmtInscricao->bindValue(':ra', $ra_aluno);
$stmtInscricao->bindValue(':id_monitoria', $id_monitoria);
$stmtInscricao->execute();

$estaInscrito = $stmtInscricao->fetch(PDO::FETCH_ASSOC) ? true : false;


$materiaCompleta = $row['Disciplina'];
$materiaBase = explode('-', $materiaCompleta)[0];


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

$primeiroConteudo = is_array($conteudosArray) ? $conteudosArray[0] : $row['Conteudos_Abordados'];

$professoresBase = $professores_monitorias[$materiaBase] ?? ['Não informado'];
$professoresString = implode(', ', $professoresBase);

$publicoArray = $publico_alvo_materias[$materiaCompleta] ?? ['Alunos interessados'];

$cor = $cores_lista_monitorias[$materiaBase] ?? '#64748b';


$monitoria = [
    'id' => $row['ID_Monitoria'],
    'RaMonitor' => $row['Registro_Academico'],
    'materia' => $materiaBase,
    'disciplina' => $conteudosString,
    'monitor' => $row['monitor'],
    'email_monitor' => $row['email_monitor'],
    'horario' => date('H:i', strtotime($row['Horario'])),
    'local' => $row['Localizacao'],
    'vagas' => $row['Capacidade_Alunos'],
    'vagas_disponiveis' => $row['Capacidade_Alunos'] - $row['Quantidade_Inscritos'],
    'alunos_inscritos' => $row['Quantidade_Inscritos'],
    'professores' => $professoresString,
    'publico_alvo' => $publicoArray,
    'conteudo_programatico' => $conteudosArray,
    'proxima_sessao' => $row['Data'],
    'cor' => $cor,
    'foto' => $row['Foto_Perfil'],
    'data' => date('d/m/Y', strtotime($row['Data'])),
    'duracao_sessao' => '2 horas',
    'inscrito' => $estaInscrito 
];

?>
