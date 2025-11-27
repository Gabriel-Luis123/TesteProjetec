<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start(); 
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-error.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../utils/con_db.php';
session_start();

function respond($statusCode, $data) {
    while (ob_get_level()) ob_end_clean();

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['status']) || empty($_SESSION['registro'])) {
    respond(401, ['erro' => 'Não autorizado']);
}

$action = $_GET['action'] ?? '';

$sql_current_user = "SELECT Registro_Academico FROM Aluno WHERE Registro_Academico = :id";
$stmt_current = $pdo->prepare($sql_current_user);
$stmt_current->bindParam(':id', $_SESSION['registro']);
$stmt_current->execute();
$current_user = $stmt_current->fetch(PDO::FETCH_ASSOC);

if (!$current_user) {
    respond(401, ['erro' => 'Usuário não encontrado']);
}

if ($action === 'get_contacts') {

    $sql = "SELECT DISTINCT
            a.Registro_Academico as id,
            a.Nome,
            a.Foto_Perfil,
            m.disciplina_monitorada as disciplina,
            MAX(msg.data_hora) as ultima_mensagem
        FROM Aluno a
        LEFT JOIN Monitora m ON a.Registro_Academico = m.Registro_Academico
        LEFT JOIN Mensagens msg 
            ON (msg.remetente_id = :id AND msg.destinatario_id = a.Registro_Academico)
            OR (msg.remetente_id = a.Registro_Academico AND msg.destinatario_id = :id)
        WHERE a.Registro_Academico != :id
        GROUP BY a.Registro_Academico
        ORDER BY ultima_mensagem DESC, a.Nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $_SESSION['registro']);
    $stmt->execute();

    respond(200, ['sucesso' => true, 'contatos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}
if ($action === 'get_contact_details') {

    $id = $_GET['id'] ?? null;

    if (!$id) {
        respond(400, ['erro' => 'ID não informado']);
    }

    $sql = "SELECT 
                a.Registro_Academico as id,
                a.Nome,
                a.Foto_Perfil,
                m.disciplina_monitorada AS disciplina
            FROM Aluno a
            LEFT JOIN Monitora m 
                ON a.Registro_Academico = m.Registro_Academico
            WHERE a.Registro_Academico = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $id);
    $stmt->execute();

    $contato = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contato) {
        respond(404, ['erro' => 'Contato não encontrado']);
    }

    respond(200, ['sucesso' => true, 'contato' => $contato]);
}

if ($action === 'get_messages') {

    $contact_id = $_POST["contact_id"] ?? null;

    if (!$contact_id) {
        respond(400, ['erro' => 'ID de contato ausente']);
    }

    $sql = "SELECT * FROM Mensagens
            WHERE (remetente_id = :me AND destinatario_id = :c)
               OR (remetente_id = :c AND destinatario_id = :me)
            ORDER BY data_hora ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':me', $_SESSION['registro']);
    $stmt->bindValue(':c', $contact_id);
    $stmt->execute();

    $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    respond(200, ['sucesso' => true, 'mensagens' => $msgs]);
}

/* -------------------- SEND MESSAGE -------------------- */
if ($action === 'send_message') {

    $text = trim($_POST['mensagem'] ?? '');
    $contact = $_POST['contact_id'] ?? null;

    if (!$text || !$contact) {
        respond(400, ['erro' => 'Dados incompletos']);
    }

    if ($contact == $_SESSION['registro']) {
        respond(400, ['erro' => 'Não é possível enviar mensagem para si mesmo']);
    }

    // Inserir
    $sql = "INSERT INTO Mensagens (remetente_id, destinatario_id, conteudo, data_hora)
            VALUES (:r, :d, :c, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':r' => $_SESSION['registro'],
        ':d' => $contact,
        ':c' => $text
    ]);

    // Pegar a mensagem inserida (assumindo id autoincrement)
    $lastId = $pdo->lastInsertId();
    $fetchSql = "SELECT * FROM Mensagens WHERE id = :id LIMIT 1";
    $fstmt = $pdo->prepare($fetchSql);
    $fstmt->execute([':id' => $lastId]);
    $mensagem = $fstmt->fetch(PDO::FETCH_ASSOC);

    respond(200, ['sucesso' => true, 'mensagem' => $mensagem]);
}


if ($action === 'search_contacts') {

    $q = trim($_GET['q'] ?? '');

    $sql = "SELECT DISTINCT
                a.Registro_Academico as id,
                a.Nome,
                a.Foto_Perfil,
                m.disciplina_monitorada as disciplina
            FROM Aluno a
            LEFT JOIN Monitora m ON a.Registro_Academico = m.Registro_Academico
            WHERE a.Registro_Academico != :me
              ";

    if ($q !== '') {
        $sql .= " AND (a.Nome LIKE :q OR a.Registro_Academico LIKE :q OR m.disciplina_monitorada LIKE :q)";
    }

    $sql .= " ORDER BY a.Nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':me', $_SESSION['registro']);

    if ($q !== '') {
        $like = "%{$q}%";
        $stmt->bindValue(':q', $like, PDO::PARAM_STR);
    }

    $stmt->execute();
    $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    respond(200, ['sucesso' => true, 'contatos' => $contatos]);
}


if ($action === 'send_file') {

error_log("=== DEBUG SEND_FILE ===");
error_log("DOCUMENT_ROOT=" . $_SERVER['DOCUMENT_ROOT']);
error_log("UPLOAD_DIR=" . $uploadDir);
error_log("FILES=" . print_r($_FILES, true));
error_log("POST=" . print_r($_POST, true));

    if (!isset($_FILES['file'])) {
        respond(400, ['erro' => 'Nenhum arquivo recebido']);
    }

    $contact_id = $_POST['contact_id'] ?? null;

    if (!$contact_id) {
        respond(400, ['erro' => 'Contato não informado']);
    }

    if ($contact_id == $_SESSION['registro']) {
        respond(400, ['erro' => 'Não é possível enviar arquivo para si mesmo']);
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/projetecmainec2/Projeto/uploads/chat/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = "chat_" . uniqid() . "." . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filePath = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        error_log("Erro ao mover arquivo para: $filePath");
        respond(500, ['erro' => 'Falha ao mover o arquivo']);
    }

    $publicUrl = "http://localhost/projetecmainec2/Projeto/uploads/chat/" . $filename;

    $sql = "INSERT INTO Mensagens (remetente_id, destinatario_id, conteudo, arquivo_url, data_hora, tipo)
            VALUES (:r, :d, NULL, :u, NOW(), 'arquivo')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':r' => $_SESSION['registro'],
        ':d' => $contact_id,
        ':u' => $publicUrl
    ]);

    $lastId = $pdo->lastInsertId();

    $fstmt = $pdo->prepare("SELECT * FROM Mensagens WHERE id = :id LIMIT 1");
    $fstmt->execute([':id' => $lastId]);
    $mensagem = $fstmt->fetch(PDO::FETCH_ASSOC);

    respond(200, [
        'sucesso' => true,
        'mensagem' => $mensagem
    ]);
}







respond(400, ['erro' => 'Ação inválida']);
