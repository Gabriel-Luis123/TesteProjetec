<?php
require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/cores_monitor.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID da monitoria inválido.");
}

$monitoriaId = (int) $_GET['id'];

$sqlMonitoria = "
    SELECT 
        m.ID_Monitoria AS id,
        m.Registro_Academico AS monitor_id,
        m.Disciplina AS disciplina_id,
        m.Data,
        m.Horario AS hora_inicio,
        m.Localizacao,
        m.Conteudos_Abordados,
        m.Capacidade_Alunos,
        m.Quantidade_Inscritos,
        m.Concluida,
        m.Feedback
    FROM Monitoria m
    WHERE m.ID_Monitoria = :id
";

$stmt = $pdo->prepare($sqlMonitoria);
$stmt->execute([':id' => $monitoriaId]);
$monitoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$monitoria) {
    die("Monitoria não encontrada.");
}


$disciplinaRaw = trim($monitoria['disciplina_id']);

$partes = explode('-', $disciplinaRaw);

$disciplina_base = $partes[0];


$disciplina_ano = $partes[1] ?? null;

$cor_disciplina = $cores_lista_monitorias[$disciplina_base]
    ?? "#000000"; 

$sqlDisc = "SELECT Disciplina_Monitorada FROM Monitora WHERE Registro_Academico = :id";
$stmtDisc = $pdo->prepare($sqlDisc);
$stmtDisc->execute([':id' => $monitoria['monitor_id']]);
$disciplina = $stmtDisc->fetchColumn() ?: "Não informada";

$sqlMonitor = "
    SELECT Nome
    FROM Aluno
    WHERE Registro_Academico = :id
";

$stmtMonitor = $pdo->prepare($sqlMonitor);
$stmtMonitor->execute([':id' => $monitoria['monitor_id']]);
$nomeMonitor = $stmtMonitor->fetchColumn() ?: "Monitor não encontrado";

$sqlAlunos = "
    SELECT 
        a.Nome AS nome,
        a.Registro_Academico AS id,
        ai.Presenca_confirmada AS confirmada,
        ai.Presenca_requisitada AS requisitada
    FROM Alunos_Inscritos ai
    INNER JOIN Aluno a ON a.Registro_Academico = ai.Registro_Academico
    WHERE ai.ID_Monitoria = :id
";

$stmtAlunos = $pdo->prepare($sqlAlunos);
$stmtAlunos->execute([':id' => $monitoriaId]);
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

$raw = $monitoria['Conteudos_Abordados'];

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

$horaFormatada = date('H:i', strtotime($monitoria['hora_inicio']));

$presentes = 0;
$ausentes = 0;

foreach ($alunos as $aluno) {
    if ((int)$aluno['confirmada'] === 1) {
        $presentes++;
    } else {
        $ausentes++;
    }
}

$monitoria = [
    'id'            => $monitoria['id'],
    'disciplina'    => $disciplina,
    'monitor'       => $nomeMonitor,
    'data'          => $monitoria['Data'],
    'hora_inicio'   => $horaFormatada,
    'local'         => $monitoria['Localizacao'],
    'descricao'     => $conteudosString,
    'status'        => $monitoria['Concluida'],
    'capacidade'    => $monitoria['Capacidade_Alunos'],
    'inscritos_qtd' => $monitoria['Quantidade_Inscritos'],
    'feedback' => $monitoria['Feedback'] ??  '',
    'presentes' => $presentes,
    'ausentes' => $ausentes,
    'cor' => $cor_disciplina
];

?>
