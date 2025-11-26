<?php

require_once __DIR__ . '/../utils/con_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siape = $_POST['siape'];
    $senha = $_POST['senha'];

    $siape = $_POST['siape'] ?? '';

    if (strlen($siape) !== 7 || !ctype_digit($siape)) {
        header("Location: ../../pages/login.php?mensagem=invalid_length");
        exit;
    }


    $sql = "SELECT SIAPE, Senha FROM Administrador WHERE SIAPE = :siape";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':siape', $siape);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $senha_banco = $resultado['Senha'];
        if ($senha === $senha_banco) {
            session_start();
            $_SESSION['status'] = 'Administrador';
            $_SESSION['siape'] = $siape;
            header('Location: ../../pages/inicial.php?mensagem=usuario_logado_administrador');
            exit;
        } else {
            header('Location: ../../pages/login.php?mensagem=usuario_senha_incorreta');
            exit;
        }
    } else {
        header('Location: ../../pages/login.php?mensagem=usuario_nao_encontrado');
        exit;
    }
}
