<?php

require_once __DIR__ . '/../utils/con_db.php';
require_once __DIR__ . '/../utils/salas_monitorias.php';
require_once __DIR__ . '/../utils/horarios_monitoria.php';

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

?>
