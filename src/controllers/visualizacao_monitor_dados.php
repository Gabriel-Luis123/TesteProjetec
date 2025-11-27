<?php
require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/cores_monitor.php';

$registro_academico = $_GET['registro'];
$usuario_logado = $_SESSION['registro'];

/* ---------------------------
   1. BUSCAR DADOS DO MONITOR
----------------------------*/

$sqlMonitor = "
    SELECT 
        a.Registro_Academico AS id,
        a.Nome AS nome,
        a.Email AS email,
        a.Curso AS curso,
        a.Foto_Perfil AS foto,
        (
            SELECT COUNT(*) 
            FROM Monitoria 
            WHERE Registro_Academico = a.Registro_Academico
        ) AS total_monitorias
    FROM aluno a
    WHERE a.Registro_Academico = :registro
    LIMIT 1
";


$stmtM = $pdo->prepare($sqlMonitor);
$stmtM->execute([':registro' => $registro_academico]);
$monitorRow = $stmtM->fetch(PDO::FETCH_ASSOC);

/* ---------------------------
   2. BUSCAR TELEFONE NA OUTRA TABELA
----------------------------*/

$sqlTelefone = "
    SELECT Telefone 
    FROM Telefone
    WHERE Registro_Academico = :registro
    LIMIT 1
";

$stmtT = $pdo->prepare($sqlTelefone);
$stmtT->execute([':registro' => $registro_academico]);
$telefoneRow = $stmtT->fetch(PDO::FETCH_ASSOC);

$telefone = $telefoneRow['Telefone'] ?? 'Não informado';

$sql_disciplina = "SELECT Disciplina_Monitorada FROM Monitora WHERE Registro_Academico = :registro";
$stmt_disciplina = $pdo->prepare($sql_disciplina);
$stmt_disciplina->bindParam('registro', $registro_academico);
$stmt_disciplina->execute();

$resultado = $stmt_disciplina->fetch(PDO::FETCH_ASSOC);



$disciplinaRaw = trim($resultado['Disciplina_Monitorada']);

$partes = explode('-', $disciplinaRaw);

$disciplina_base = $partes[0];


$disciplina_ano = $partes[1] ?? null;

$cor_disciplina = $cores_lista_monitorias[$disciplina_base]
    ?? "#000000"; 


$monitor = [
    'id' => (int)$monitorRow['id'],
    'nome' => $monitorRow['nome'],
    'foto' => $monitorRow['foto'] ?: 'https://i.pravatar.cc/200?img=1',
    'email' => $monitorRow['email'],
    'telefone' => $telefone,
    'curso' => $monitorRow['curso'] ?? 'Não informado',
    'total_monitorias' => (int)$monitorRow['total_monitorias']
];



$sql = "
    SELECT 
        m.ID_Monitoria AS id,
        m.Registro_Academico AS monitor_id,
        a.Nome AS monitor,
        m.Disciplina AS materia,
        m.Data,
        m.Horario,
        m.Conteudos_Abordados AS descricao,
        m.Quantidade_Inscritos AS inscritos,
        m.Capacidade_Alunos AS vagas,
        m.Concluida
    FROM Monitoria m
    INNER JOIN Aluno a ON a.Registro_Academico = m.Registro_Academico
    WHERE m.Registro_Academico = :ra
    ORDER BY m.Data DESC, m.Horario DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':ra' => $registro_academico]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$todasMonitorias = [];


foreach ($rows as $m) {
    $disciplinaRaw = trim($m['materia']);

    $partes = explode('-', $disciplinaRaw);

    $disciplina_base = $partes[0];

    $disciplina_ano = $partes[1] ?? null;

    $cor_disciplina = $cores_lista_monitorias[$disciplina_base]
        ?? "#000000"; 

    if ($m['Concluida']) {
        $status = "Concluída";
    } elseif ($m['inscritos'] >= $m['vagas']) {
        $status = "Lotada";
    } else {
        $status = "Disponível";
    }

    
    $raw = $m['descricao'];

    $raw = trim($raw);
    $raw = trim($raw, "\"");

    $raw = stripslashes($raw);

    $conteudosArray = json_decode($raw, true);

    if (!is_array($conteudosArray)) {

        $raw2 = trim($raw, "\"");
        $descricao = json_decode($raw2, true);
    }

    if (is_array($conteudosArray)) {

        $conteudosArray = array_map(function($item) {
            return ucwords(mb_strtolower(trim($item)));
        }, $conteudosArray);

        $descricao = implode(', ', $conteudosArray);

    } else {
        $descricao = $monitoria['Conteudos_Abordados'];
    }

    $sqlInscrito = "
        SELECT 1
        FROM Alunos_Inscritos
        WHERE Registro_Academico = :ra
          AND ID_Monitoria = :id
        LIMIT 1
    ";

    $stmtInscrito = $pdo->prepare($sqlInscrito);
    $stmtInscrito->execute([
        ':ra' => $usuario_logado,
        ':id' => $m['id']
    ]);

    $estouInscrito = $stmtInscrito->fetch(PDO::FETCH_ASSOC) ? true : false;


    $eDaMesmaPessoa = ($usuario_logado === $m['monitor_id']);


    $todasMonitorias[] = [
        'id'               => (int)$m['id'],
        'monitor_id'       => $m['monitor_id'],
        'monitor'          => $m['monitor'],
        'materia'          => $m['materia'],
        'data'             => date('d/m/Y', strtotime($m['Data'])),
        'horario'          => date('H:i', strtotime($m['Horario'])),
        'descricao'        => $descricao,
        'inscritos'        => (int)$m['inscritos'],
        'vagas'            => (int)$m['vagas'],
        'status'           => $status,
        'estou_inscrito'   => $estouInscrito,
        'e_da_mesma_pessoa' => $eDaMesmaPessoa,
        'cor' => $cor_disciplina
    ];
}
