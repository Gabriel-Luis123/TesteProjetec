w<?php

    require_once __DIR__ . '/../utils/con_db.php';
    require_once __DIR__ . '/../utils/cores_monitor.php';

    $registroAcademico = $_SESSION['registro'];


    $sqlAluno = "SELECT Nome, Registro_Academico, Email, Curso, Sala, Foto_Perfil, E_Monitor
             FROM Aluno
             WHERE Registro_Academico = :ra";

    $stmtAluno = $pdo->prepare($sqlAluno);
    $stmtAluno->bindParam(':ra', $registroAcademico);
    $stmtAluno->execute();

    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

    if ($aluno['E_Monitor'] === 1) {
        $sql_disciplina = "SELECT Disciplina_Monitorada FROM Monitora WHERE Registro_Academico = :registro";
        $stmt_disciplina = $pdo->prepare($sql_disciplina);
        $stmt_disciplina->bindParam('registro', $registroAcademico);
        $stmt_disciplina->execute();

        $resultado = $stmt_disciplina->fetch(PDO::FETCH_ASSOC);



        $disciplinaRaw = trim($resultado['Disciplina_Monitorada']);

        $partes = explode('-', $disciplinaRaw);

        $disciplina_base = $partes[0];


        $disciplina_ano = $partes[1] ?? null;

        $cor_disciplina = $cores_lista_monitorias[$disciplina_base]
            ?? "#000000"; 
    } else {
        $cor_disciplina = 'linear-gradient(135deg, #2d8025 0%, #4ab91b 100%);';
    }



    if (!$aluno) {
        die("Usuário não encontrado.");
    }


    $sqlTelefone = "SELECT Telefone FROM Telefone WHERE Registro_Academico = :ra";

    $stmtTel = $pdo->prepare($sqlTelefone);
    $stmtTel->bindParam(':ra', $registroAcademico);
    $stmtTel->execute();

    $telData = $stmtTel->fetch(PDO::FETCH_ASSOC);

    $telefone = $telData['Telefone'] ?? null;

    $telefone = preg_replace('/\D/', '', $telefone);

    $telefone_formatado = preg_replace(
        '/^(\d{2})(\d{1})(\d{4})(\d{4})$/',
        '($1) $2 $3-$4',
        $telefone
    );


    $usuario = [
        'nome' => $aluno['Nome'],
        'registro_academico' => $aluno['Registro_Academico'],
        'email' => $aluno['Email'],
        'curso' => $aluno['Curso'],
        'telefone' => $telefone_formatado,
        'foto_perfil' => $aluno['Foto_Perfil'],
        'is_monitor' => (bool)$aluno['E_Monitor'],
        'cor' => $cor_disciplina
    ];
