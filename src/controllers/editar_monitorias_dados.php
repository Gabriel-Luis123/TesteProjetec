<?php

require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/salas_monitorias.php';
require_once __DIR__ . '/../utils/horarios_monitoria.php';

$cores_monitor = require __DIR__ . '/../utils/cores_monitor.php';

$registro = $_SESSION['registro'];
$id = $_GET['id'];

$sql_disciplina = '
    SELECT Disciplina_Monitorada 
    FROM Monitora 
    WHERE Registro_Academico = :registro
';

$stmt_disciplina = $pdo->prepare($sql_disciplina);
$stmt_disciplina->bindParam(':registro', $registro);
$stmt_disciplina->execute();

$resultado = $stmt_disciplina->fetch(PDO::FETCH_ASSOC);

if ($resultado && isset($resultado['Disciplina_Monitorada'])) {

    $disciplinaRaw = $resultado['Disciplina_Monitorada'];

    $partes = explode('-', $disciplinaRaw);

    $disciplina_base = $partes[0];    
    $ano = $partes[1] ?? null;        


    $cor_disciplina = $cores_lista_monitorias[$disciplina_base] 
        ?? "#000000"; 

} else {
    $disciplina_base = null;
    $ano = null;
    $cor_disciplina = "#000000";
}

$sql_monitoria_dados = 'SELECT Data, Horario, Localizacao, Capacidade_Alunos, Conteudos_Abordados 
                        FROM Monitoria 
                        WHERE ID_Monitoria = :id';

$stmt_monitoria_dados = $pdo->prepare($sql_monitoria_dados);
$stmt_monitoria_dados->bindParam('id', $id);
$stmt_monitoria_dados->execute();

$resultado_monitoria_dados = $stmt_monitoria_dados->fetch(PDO::FETCH_ASSOC);

if ($resultado_monitoria_dados) {

    $hora_original = $resultado_monitoria_dados['Horario'];
    
    $hora_formatada = date('H:i', strtotime($hora_original)); 

    $resultado_monitoria_dados['Horario'] = $hora_formatada;
}
$conteudosBrutos = $resultado_monitoria_dados['Conteudos_Abordados'] ?? '';

$primeira = stripslashes($conteudosBrutos);

$segunda = trim($primeira, '"');

$conteudosArray = json_decode($segunda, true);

if ($conteudosArray === null) {

    if (empty($segunda)) {
        $conteudosArray = [];
    } else {
        $conteudosArray = [$segunda];
    }
}



?>
