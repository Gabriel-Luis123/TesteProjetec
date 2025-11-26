<?php
require_once __DIR__ . '/../utils/con_db.php';

$cores = require __DIR__ . '/../utils/cores_monitor.php';

$sql = "SELECT Registro_Academico, Nome, Email, Sala, Curso, Foto_Perfil 
        FROM Aluno 
        WHERE E_Monitor = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$resultado_monitor = $stmt->fetchAll(PDO::FETCH_ASSOC);

$monitores = [];

foreach ($resultado_monitor as $monitor) {

    $sql_disciplina = "SELECT Disciplina_Monitorada 
                       FROM Monitora 
                       WHERE Registro_Academico = :registro";
    $stmt_disciplina = $pdo->prepare($sql_disciplina);
    $stmt_disciplina->bindParam('registro', $monitor['Registro_Academico']);
    $stmt_disciplina->execute();
    $resultado_disciplina = $stmt_disciplina->fetch(PDO::FETCH_ASSOC);

    if (!$resultado_disciplina) {
        continue;
    }

    $disciplinaCompleta = $resultado_disciplina['Disciplina_Monitorada'];

    $disciplinaBase = explode('-', $disciplinaCompleta)[0];

    $cor = $cores_lista_monitorias[$disciplinaBase] ?? "#000000";


    $sql_telefone = "SELECT Telefone 
                     FROM Telefone 
                     WHERE Registro_Academico = :registro";
    $stmt_telefone = $pdo->prepare($sql_telefone);
    $stmt_telefone->bindParam('registro', $monitor['Registro_Academico']);
    $stmt_telefone->execute();
    $resultado_telefone = $stmt_telefone->fetch(PDO::FETCH_ASSOC);

    $telefone = $resultado_telefone['Telefone'] ?? "Não informado";


    $monitores[] = [
        'id' => $monitor['Registro_Academico'],
        'nome' => $monitor['Nome'],
        'email' => $monitor['Email'],
        'telefone' => $telefone,
        'turma' => $monitor['Sala'],
        'curso' => $monitor['Curso'],
        'disciplina_monitorada' => $disciplinaCompleta,
        'foto' => $monitor['Foto_Perfil'] ?: '/placeholder.svg?height=100&width=100',
        'cor' => $cor
    ];
}
