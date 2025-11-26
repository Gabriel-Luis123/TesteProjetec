<?php

require_once __DIR__ . '/../utils/con_db.php';

$registro = $_SESSION['registro'];

$cores_monitor = require __DIR__ . '/../utils/cores_monitor.php';

$registro = $_SESSION['registro'];

$sql_disciplina = '
    SELECT Disciplina_Monitorada 
    FROM Monitora 
    WHERE Registro_Academico = :registro
';

$stmt_disciplina = $pdo->prepare($sql_disciplina);
$stmt_disciplina->bindParam(':registro', $registro);
$stmt_disciplina->execute();

$resultado = $stmt_disciplina->fetch(PDO::FETCH_ASSOC);

$disciplina_base = null;
$ano = null;
$cor_disciplina = "#000000"; // fallback padrão

if (!empty($resultado) && !empty($resultado['Disciplina_Monitorada'])) {

    $disciplinaRaw = trim($resultado['Disciplina_Monitorada']);

    // Garantir que realmente temos algo como "Eletrônica-2º ano"
    if (strpos($disciplinaRaw, '-') !== false) {

        // Divide a string
        $partes = explode('-', $disciplinaRaw, 2);

        $disciplina_base = trim($partes[0]);
        var_dump($partes[1]);
        $ano = $partes[1];
        var_dump($ano);

    } else {
        // Caso venha só "Eletrônica"
        $disciplina_base = $disciplinaRaw;
        $ano = null;
    }

    // Cor baseada no nome da disciplina
    $cor_disciplina = $cores_lista_monitorias[$disciplina_base] 
        ?? "#000000";
}


$sql_monitoria = "
    SELECT 
        ID_Monitoria,
        Disciplina,
        Concluida,
        Capacidade_Alunos,
        Quantidade_Inscritos,
        Data,
        Horario,
        Localizacao 
    FROM Monitoria 
    WHERE Registro_Academico = :registro
";

$stmt = $pdo->prepare($sql_monitoria);
$stmt->bindParam(':registro', $registro);
$stmt->execute();

$monitoriasBrutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$monitorias = [];

$nomesDias = [
    'Monday'    => 'Segunda',
    'Tuesday'   => 'Terça',
    'Wednesday' => 'Quarta',
    'Thursday'  => 'Quinta',
    'Friday'    => 'Sexta',
    'Saturday'  => 'Sábado',
    'Sunday'    => 'Domingo'
];

foreach ($monitoriasBrutas as $m) {


    $partes = explode('.-', $m['Disciplina']);
    $disciplina = $partes[0];    

    if ((int)$m['Concluida'] === 1) {
        $status = 'concluido';
    }else {
        $status = 'em_espera';
    }

    $dateObj = new DateTime($m['Data']);
    $diaSemanaIngles = $dateObj->format('l');
    $diaSemanaPT = $nomesDias[$diaSemanaIngles];

    $hora_formatada = date('H', strtotime($m['Horario'])) . 'h';

    $horario_final = $diaSemanaPT . ' ' . $hora_formatada;

    $monitorias[] = [
        'id' => (int)$m['ID_Monitoria'],
        'disciplina' => $disciplina,
        'ano' => $ano,
        'status' => $status,
        'alunos_inscritos' => (int)$m['Quantidade_Inscritos'],
        'capacidade_maxima' => (int)$m['Capacidade_Alunos'],
        'data_criacao' => $m['Data'],   
        'horario' => $horario_final,
        'sala' => $m['Localizacao']
    ];
}

?>
