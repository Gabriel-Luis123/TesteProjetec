<?php
session_start();
require_once __DIR__ . '/../utils/con_db.php';

$registro_academico = $_SESSION['registro'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $novo_email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);

    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if (!filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensagem_erro'] = "Email inválido!";
        header("Location: ../../pages/perfil.php");
        exit;
    }

    if (empty($telefone)) {
        $_SESSION['mensagem_erro'] = "Telefone não pode ser vazio!";
        header("Location: ../../pages/perfil.php");
        exit;
    }

    $sqlSenha = "SELECT Senha FROM Aluno WHERE Registro_Academico = :ra";
    $stmtS = $pdo->prepare($sqlSenha);
    $stmtS->bindParam(':ra', $registro_academico);
    $stmtS->execute();
    $dadosSenha = $stmtS->fetch(PDO::FETCH_ASSOC);
    $senhaBanco = $dadosSenha['Senha'] ?? '';

    if (!empty($nova_senha)) {

        if ($senha_atual !== $senhaBanco) {
            $_SESSION['mensagem_erro'] = "Senha atual incorreta!";
            header("Location: ../../pages/perfil.php");
            exit;
        }

        if ($nova_senha !== $confirmar_senha) {
            $_SESSION['mensagem_erro'] = "As senhas não coincidem!";
            header("Location: ../../pages/perfil.php");
            exit;
        }

        if (strlen($nova_senha) < 6) {
            $_SESSION['mensagem_erro'] = "A nova senha precisa de 6 caracteres!";
            header("Location: ../../pages/perfil.php");
            exit;
        }

        $sql = "UPDATE Aluno 
                SET Email = :email, Telefone = :telefone, Senha = :senha 
                WHERE Registro_Academico = :ra";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $novo_email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':senha', $nova_senha);
        $stmt->bindParam(':ra', $registro_academico);
        $stmt->execute();

        $_SESSION['mensagem_sucesso'] = "Email, telefone e senha atualizados com sucesso!";
        header("Location: ../../pages/perfil.php");
        exit;
    }
    $sql = "UPDATE Aluno 
            SET Email = :email
            WHERE Registro_Academico = :ra";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $novo_email);
    $stmt->bindParam(':ra', $registro_academico);
    $stmt->execute();
    $telefone = preg_replace('/\D/', '', $telefone);

    $sql_telefone = "UPDATE Telefone SET telefone = :telefone WHERE Registro_Academico = :ra";

    $stmt_telefone = $pdo->prepare($sql_telefone);
    $stmt_telefone->bindParam(':telefone', $telefone);
    $stmt_telefone->bindParam(':ra', $registro_academico);
    $stmt_telefone->execute();

    $_SESSION['mensagem_sucesso'] = "Email e telefone atualizados com sucesso!";
    header("Location: ../../pages/perfil.php");
    exit;
}
