<?php

require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/horarios_monitoria.php';

header("Content-Type: application/json");

session_start();

$registro_academico = $_SESSION['registro'];

$data = $_POST['data'] ?? null;
$idMonitoriaAtual = $_POST['id'] ?? null;  

if (!$data) {
    echo json_encode([
        "status" => "error",
        "mensagem" => "Nenhuma data recebida"
    ]);
    exit;
}

$dataTimestamp = strtotime($data);
$hojeTimestamp = strtotime(date("Y-m-d"));

if ($dataTimestamp < $hojeTimestamp) {
    echo json_encode([
        "validacao_data" => [
            "status" => "inválido",
            "mensagem" => "Data inválida (não pode ser passada).",
            "disponivel" => false,
            "cor" => "red"
        ]
    ]);
    exit;
}

$resposta["validacao_data"] = [
    "status" => "válido",
    "mensagem" => "Data válida.",
    "disponivel" => true,
    "cor" => "green"
];

$sql = "SELECT ID_Monitoria, Horario, Localizacao, Concluida 
        FROM Monitoria 
        WHERE Data = :data 
          AND Registro_Academico = :registro";

$stmt = $pdo->prepare($sql);
$stmt->bindParam('data', $data);
$stmt->bindParam('registro', $registro_academico);
$stmt->execute();

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$horariosIndisponiveis = [];
$horariosDisponiveis = [];


foreach ($resultados as $res) {

    if ($idMonitoriaAtual && $res["ID_Monitoria"] == $idMonitoriaAtual) {
        continue;
    }

    $hora = new DateTime($res['Horario']);
    $horariosIndisponiveis[] = $hora->format('H:i');
}

global $horarios_monitoria;

$horariosDisponiveis = array_values(array_diff($horarios_monitoria, $horariosIndisponiveis));


$resposta["ocupacao"] = [
    "horarios_disponiveis" => $horariosDisponiveis,
    "horarios_indisponiveis" => $horariosIndisponiveis,
    "disponivel" => count($horariosIndisponiveis) === 0
];

echo json_encode($resposta);
