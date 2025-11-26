<?php

require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/lista_professores.php';
require_once __DIR__ . '/../utils/publico_alvo.php';

$sql = "
    SELECT 
        m.ID_Monitoria,
        m.Disciplina,
        a.Nome AS monitor,
        a.Foto_Perfil AS foto,
        m.Registro_Academico,
        m.Horario,
        m.Data,
        m.Localizacao,
        m.Capacidade_Alunos,
        m.Quantidade_Inscritos,
        m.Conteudos_Abordados,
        m.Publico_Alvo,
        m.Concluida
    FROM monitoria m
    INNER JOIN aluno a ON a.Registro_Academico = m.Registro_Academico
    ORDER BY m.ID_Monitoria ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$monitorias = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $materiaLimpa = explode('-', $row['Disciplina'])[0];

    $professoresFinal = $professores_monitorias[$materiaLimpa] ?? "Não informado";
    $novosProfessores = is_array($professoresFinal) ? implode(', ', $professoresFinal) : $professoresFinal;

    $publicoArray = json_decode($row['Publico_Alvo'], true);
    $publicoString = is_array($publicoArray) ? implode(', ', $publicoArray) : $row['Publico_Alvo'];

    $conteudosArray = json_decode($row['Conteudos_Abordados'], true);
    $conteudosString = is_array($conteudosArray)
        ? implode(', ', $conteudosArray)
        : $row['Conteudos_Abordados'];

    list($hora, $min, $seg) = explode(':', $row['Horario']);
    $horarioFormatado = ($min === "00") ? "{$hora}h" : "{$hora}h e {$min}min";

    $diasSemana = [
        'Monday' => 'Segunda',
        'Tuesday' => 'Terça',
        'Wednesday' => 'Quarta',
        'Thursday' => 'Quinta',
        'Friday' => 'Sexta',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];

    $dataOriginal = $row['Data'];
    $dateObj = new DateTime($dataOriginal);

    $diaSemana = $diasSemana[$dateObj->format('l')] ?? 'Indefinido';
    $dataFormatada = $dateObj->format('Y/m/d');

    $concluida = (
        $row['Concluida'] == 1 ||
        strtolower($row['Concluida']) == "sim" ||
        strtolower($row['Concluida']) == "concluida"
    );

    $sqlInscritos = "
        SELECT Registro_Academico
        FROM alunos_inscritos
        WHERE ID_Monitoria = " . $row['ID_Monitoria'];

    $stmtIns = $pdo->prepare($sqlInscritos);
    $stmtIns->execute();

    $inscritos = [];

    while ($aluno = $stmtIns->fetch(PDO::FETCH_ASSOC)) {
        $inscritos[] = $aluno['Registro_Academico'];
    }

    $monitorias[] = [
        'id' => $row['ID_Monitoria'],
        'materia' => $materiaLimpa,
        'RaMonitor' => $row['Registro_Academico'],
        'monitor' => $row['monitor'],
        'horario' => $horarioFormatado,
        'dia_semana' => $diaSemana,
        'data' => $dataOriginal,
        'foto' => $row['foto'],
        'data_formatada' => $dataFormatada,
        'local' => $row['Localizacao'],
        'vagas' => $row['Capacidade_Alunos'],
        'vagas_disponiveis' => $row['Capacidade_Alunos'] - $row['Quantidade_Inscritos'],
        'descricao' => $conteudosString,
        'professores' => $novosProfessores,
        'publico_alvo' => $publicoString,
        'concluida' => $concluida,
        'inscritos' => $inscritos
    ];
};

?>
