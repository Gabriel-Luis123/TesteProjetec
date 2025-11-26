<?php
session_start();
require_once __DIR__ . '/../utils/con_db.php';


$redirect = $_SERVER['HTTP_REFERER'] ?? '../../pages/disciplinas.php';

$redirect = filter_var($redirect, FILTER_SANITIZE_URL);

if (!filter_var($redirect, FILTER_VALIDATE_URL) && !str_starts_with($redirect, '../') && !str_starts_with($redirect, './')) {
    $redirect = '../../pages/disciplinas.php';
}

$path = parse_url($redirect, PHP_URL_PATH);
$paginaAtual = basename($path);


$paginas_bloqueadas = [
    'visualizacao_monitor.php',
    'meus_dados.php'
];

$bloquearMensagem = in_array($paginaAtual, $paginas_bloqueadas, true);


$registro = $_SESSION['registro'] ?? null;

if (!$registro) {
    header("Location: ../../pages/login.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_GET['id_monitoria']) || !is_numeric($_GET['id_monitoria'])) {
        header("Location: $redirect");
        exit;
    }

    $monitoria_id = (int)$_GET['id_monitoria'];


    $stmt = $pdo->prepare("
        SELECT 1 FROM Alunos_Inscritos
        WHERE Registro_Academico = :registro AND ID_Monitoria = :id
    ");
    $stmt->execute([
        ':registro' => $registro,
        ':id' => $monitoria_id
    ]);
    $ja_inscrito = (bool) $stmt->fetchColumn();


    $stmtInfo = $pdo->prepare("
        SELECT Data, Horario, Capacidade_Alunos, Quantidade_Inscritos
        FROM Monitoria
        WHERE ID_Monitoria = :id
    ");
    $stmtInfo->execute([':id' => $monitoria_id]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        $msg = "Monitoria não encontrada.";
        enviarMensagem($redirect, $msg, $bloquearMensagem);
    }

    $data_monitoria = $info['Data'];
    $horario_monitoria = $info['Horario'];
    $capacidade = (int)$info['Capacidade_Alunos'];
    $inscritos_atual = (int)$info['Quantidade_Inscritos'];


    $stmtConflito = $pdo->prepare("
        SELECT ai.ID_Monitoria
        FROM Alunos_Inscritos ai
        INNER JOIN Monitoria m ON m.ID_Monitoria = ai.ID_Monitoria
        WHERE ai.Registro_Academico = :registro
        AND m.Data = :data
        AND m.Horario = :horario
        AND ai.ID_Monitoria != :id
    ");
    $stmtConflito->execute([
        ':registro' => $registro,
        ':data' => $data_monitoria,
        ':horario' => $horario_monitoria,
        ':id' => $monitoria_id
    ]);
    $conflito = $stmtConflito->fetch(PDO::FETCH_ASSOC);

    if ($conflito) {
        enviarMensagem($redirect, 
            "Você já está inscrito em outra monitoria neste mesmo horário!",
            $bloquearMensagem
        );
    }


    if (isset($_POST['inscricao'])) {

        if ($ja_inscrito) {
            enviarMensagem($redirect, 
                "Você já está inscrito nesta monitoria!",
                $bloquearMensagem
            );
        }

        if ($inscritos_atual >= $capacidade) {
            enviarMensagem($redirect, 
                "A monitoria atingiu a capacidade máxima!",
                $bloquearMensagem
            );
        }

        $stmt_inscrever = $pdo->prepare("
            INSERT INTO Alunos_Inscritos 
            (Registro_Academico, Presenca_Confirmada, Presenca_Requisitada, ID_Monitoria)
            VALUES (:registro, 0, 0, :id)
        ");
        $stmt_inscrever->execute([
            ':registro' => $registro,
            ':id' => $monitoria_id
        ]);

        $pdo->prepare("
            UPDATE Monitoria
            SET Quantidade_Inscritos = Quantidade_Inscritos + 1
            WHERE ID_Monitoria = :id
        ")->execute([':id' => $monitoria_id]);

        enviarMensagem($redirect, 
            "Inscrição realizada com sucesso!",
            $bloquearMensagem
        );
    }


    if (isset($_POST['cancelar'])) {

        if ($ja_inscrito) {

            $pdo->prepare("
                DELETE FROM Alunos_Inscritos
                WHERE Registro_Academico = :registro AND ID_Monitoria = :id
            ")->execute([
                ':registro' => $registro,
                ':id' => $monitoria_id
            ]);

            $pdo->prepare("
                UPDATE Monitoria
                SET Quantidade_Inscritos = Quantidade_Inscritos - 1
                WHERE ID_Monitoria = :id
            ")->execute([':id' => $monitoria_id]);

            enviarMensagem($redirect, 
                "Inscrição cancelada com sucesso!",
                $bloquearMensagem
            );

        } else {
            enviarMensagem($redirect, 
                "Você não estava inscrito.",
                $bloquearMensagem
            );
        }
    }
}

function enviarMensagem(string $redirect, string $msg, bool $bloquearMensagem)
{
    if ($bloquearMensagem) {
        header("Location: $redirect");
    } else {
        header("Location: $redirect?mensagem=" . urlencode($msg));
    }
    exit;
}
?>
