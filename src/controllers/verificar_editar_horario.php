<?php

require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/salas_monitorias.php';

header("Content-Type: application/json");

session_start();

$registro = $_SESSION['registro'] ?? null;
$horario = $_POST['horario'] ?? null;
$data = $_POST['data'] ?? null;
$idMonitoriaAtual = $_POST['id'] ?? null;     
$sala_atual = $_POST['sala_atual'] ?? null;   
if (!$horario || !$data) {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Horário ou data não enviados."
    ]);
    exit;
}

$hoje = date("Y-m-d");
$agora = date("H:i");

if ($data == $hoje && $horario < $agora) {
    echo json_encode([
        "validacao_horario" => [
            "status" => "inválido",
            "mensagem" => "Horário já passou.",
            "disponivel" => false,
            "cor" => "red"
        ]
    ]);
    exit;
}

$sql = "SELECT Localizacao, ID_Monitoria
        FROM Monitoria 
        WHERE Data = :data 
        AND Horario = :horario";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(":data", $data);
$stmt->bindParam(":horario", $horario);
$stmt->execute();

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$salasIndisponiveis = [];
$salasDisponiveis = $lista_Salas;

foreach ($resultados as $linha) {

    if (!empty($idMonitoriaAtual) && $linha["ID_Monitoria"] == $idMonitoriaAtual) {
        continue;
    }

    if (!empty($sala_atual) && $linha["Localizacao"] == $sala_atual) {
        continue;
    }

    $salasIndisponiveis[] = $linha['Localizacao'];
}


$salasDisponiveis = array_diff($salasDisponiveis, $salasIndisponiveis);
$salasDisponiveis = array_values($salasDisponiveis);

if (!empty($sala_atual) && !in_array($sala_atual, $salasDisponiveis)) {
    array_unshift($salasDisponiveis, $sala_atual);
}

echo json_encode([
    "validacao_horario" => [
        "status" => "válido",
        "mensagem" => "Horário válido.",
        "disponivel" => true,
        "cor" => "green"
    ],
    "ocupacao" => [
        "salas_disponiveis" => $salasDisponiveis,
        "salas_indisponiveis" => $salasIndisponiveis
    ]
]);
