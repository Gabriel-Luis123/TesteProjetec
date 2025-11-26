<?php

require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/lista_materias.php';


$sql = "SELECT * FROM Monitoria";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$monitores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$disciplinas_cadastradas = $materias;

$sql_alunos = "SELECT * FROM Aluno";
$stmt_aluno = $pdo->prepare($sql_alunos);
$stmt_aluno->execute();

$resultado_aluno_cadastrados = $stmt_aluno->fetchAll(PDO::FETCH_ASSOC);


$alunos_Monitores = [];

$sql_monitores = "SELECT Foto_Perfil, Nome, Curso, Registro_Academico 
                  FROM Aluno 
                  WHERE E_Monitor = 1";
$stmt_monitores = $pdo->prepare($sql_monitores);
$stmt_monitores->execute();
$resultado_monitores = $stmt_monitores->fetchAll(PDO::FETCH_ASSOC);

$diaHoje = new DateTime();
$inicioSemana = (clone $diaHoje)->modify('monday last week');
$fimSemana = (clone $diaHoje)->modify('sunday last week');

foreach ($resultado_monitores as $monitor) {

    $registro = $monitor['Registro_Academico'];

    $sql_disciplinas = "SELECT Disciplina_monitorada 
                        FROM Monitora 
                        WHERE Registro_Academico = :registro";
    $stmt_disciplinas = $pdo->prepare($sql_disciplinas);
    $stmt_disciplinas->execute([':registro' => $registro]);

    $resultado_disciplina = $stmt_disciplinas->fetch(PDO::FETCH_ASSOC);

    $disciplina_monitorada = $resultado_disciplina['Disciplina_monitorada'] ?? null;

    $disciplinaNome = null;
    $disciplinaAno  = null;
    if ($disciplina_monitorada && strpos($disciplina_monitorada, '-') !== false) {
        list($disciplinaNome, $disciplinaAno) = explode('-', $disciplina_monitorada);
    }

    $sql_monitorias_semana = "
        SELECT * 
        FROM Monitoria 
        WHERE Registro_Academico = :registro
        AND Data BETWEEN :inicio AND :fim
    ";
    $stmt_ms = $pdo->prepare($sql_monitorias_semana);
    $stmt_ms->execute([
        ':registro' => $registro,
        ':inicio'   => $inicioSemana->format('Y-m-d'),
        ':fim'      => $fimSemana->format('Y-m-d')
    ]);

    $monitoriasSemana = $stmt_ms->fetchAll(PDO::FETCH_ASSOC);

    $quantidadeMonitorias = count($monitoriasSemana);

    $somaAlunosSemana = 0;
    foreach ($monitoriasSemana as $m) {
        $somaAlunosSemana += $m['Quantidade_Inscritos'];
    }

    $sql_ultima = "
SELECT * FROM Monitoria 
WHERE Registro_Academico = :registro
ORDER BY Data DESC, Horario DESC
LIMIT 1
";
    $stmtUltima = $pdo->prepare($sql_ultima);
    $stmtUltima->execute([':registro' => $registro]);
    $ultimaMonitoria = $stmtUltima->fetch(PDO::FETCH_ASSOC);

    $diasSemana = [
        'Monday'    => 'Segunda',
        'Tuesday'   => 'Terça',
        'Wednesday' => 'Quarta',
        'Thursday'  => 'Quinta',
        'Friday'    => 'Sexta',
        'Saturday'  => 'Sábado',
        'Sunday'    => 'Domingo'
    ];

    if ($ultimaMonitoria) {
        $dataObj = new DateTime($ultimaMonitoria['Data']);

        $diaIngles = $dataObj->format('l');

        $diaSemana = $diasSemana[$diaIngles];

        $horario = substr($ultimaMonitoria['Horario'], 0, 5); 

        $ultimaMonitoriaString = "$diaSemana-$horario";
    } else {
        $ultimaMonitoriaString = "";
    }

    $alunos_Monitores[] = [
        'Registro_Academico' => $monitor['Registro_Academico'],
        'nome'               => $monitor['Nome'],
        'turma'              => $monitor['Curso'],
        'disciplina'         => $disciplinaNome,
        'ano'                => $disciplinaAno,
        'avatar'        => $monitor['Foto_Perfil'],
        'horarios'  => $quantidadeMonitorias,
        'alunos'       => $somaAlunosSemana,
        'Ultima_Monitoria'   => $ultimaMonitoriaString ?: null,
        'Monitorias'         => $monitoriasSemana
    ];
}
