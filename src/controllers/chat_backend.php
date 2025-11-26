<?php

require_once __DIR__ . '/../utils/con_db.php';
session_start();

if (!isset($_SESSION['status'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

$sql_current_user = "SELECT Registro_Academico FROM Aluno WHERE Registro_Academico = :id";
$stmt_current = $pdo->prepare($sql_current_user);
$stmt_current->bindParam(':id', $_SESSION['registro']);
$stmt_current->execute();
$current_user = $stmt_current->fetch(PDO::FETCH_ASSOC);

if (!$current_user) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não encontrado']);
    exit;
}

if ($action === 'get_contacts') {
    $current_id = $_SESSION['registro'];
    
    $sql = "SELECT DISTINCT
            a.Registro_Academico as id,
            a.Nome,
            a.Foto_Perfil,
            m.disciplina_monitorada as disciplina,
            MAX(msg.data_hora) as ultima_mensagem,
            msg.data_hora as data_mensagem
        FROM Aluno a
        LEFT JOIN Monitora m ON a.Registro_Academico = m.Registro_Academico
        LEFT JOIN Mensagens msg ON (msg.remetente_id = :current_id AND msg.destinatario_id = a.Registro_Academico)
                                OR (msg.remetente_id = a.Registro_Academico AND msg.destinatario_id = :current_id)
        WHERE a.Registro_Academico != :current_id
        GROUP BY a.Registro_Academico, a.Nome, a.Foto_Perfil, m.disciplina_monitorada
        ORDER BY ultima_mensagem DESC, a.Nome ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':current_id', $current_id);
    $stmt->execute();
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['sucesso' => true, 'contatos' => $contacts]);
    exit;
}

if ($action === 'get_messages') {
    $contact_id = $_POST['contact_id'] ?? '';
    $current_id = $_SESSION['registro'];
    
    if (empty($contact_id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID de contato não fornecido']);
        exit;
    }
    
    $sql = "SELECT * FROM Mensagens 
            WHERE (remetente_id = :current_id AND destinatario_id = :contact_id)
            OR (remetente_id = :contact_id AND destinatario_id = :current_id)
            ORDER BY data_hora ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':current_id', $current_id);
    $stmt->bindParam(':contact_id', $contact_id);
    $stmt->execute();
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as &$msg) {
    $timestamp = strtotime($msg['data_hora']);
    $meses = [
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
    ];

    $dia = date('j', $timestamp);
    $mes = $meses[(int)date('n', $timestamp)];

    $msg['data_formatada'] = "$dia de $mes";
    }

    echo json_encode(['sucesso' => true, 'mensagens' => $messages]);
    exit;
}

if ($action === 'send_message') {
    $contact_id = $_POST['contact_id'] ?? '';
    $message_text = $_POST['mensagem'] ?? '';
    $current_id = $_SESSION['registro'];
    
    if (empty($contact_id) || empty($message_text)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Dados incompletos']);
        exit;
    }
    
    if ($contact_id === $current_id) {
        http_response_code(400);
        echo json_encode(['erro' => 'Você não pode enviar mensagens para si mesmo']);
        exit;
    }
    
    $sql_verify = "SELECT Registro_Academico FROM Aluno WHERE Registro_Academico = :contact_id";
    $stmt_verify = $pdo->prepare($sql_verify);
    $stmt_verify->bindParam(':contact_id', $contact_id);
    $stmt_verify->execute();
    
    if (!$stmt_verify->fetch()) {
        http_response_code(400);
        echo json_encode(['erro' => 'Contato não existe']);
        exit;
    }
    
    $sql_insert = "INSERT INTO Mensagens (remetente_id, destinatario_id, conteudo, data_hora) 
                   VALUES (:remetente_id, :destinatario_id, :conteudo, NOW())";
    
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->bindParam(':remetente_id', $current_id);
    $stmt_insert->bindParam(':destinatario_id', $contact_id);
    $stmt_insert->bindParam(':conteudo', $message_text);
    
    if ($stmt_insert->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Mensagem enviada com sucesso']);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao enviar mensagem']);
        exit;
    }
}

if ($action === 'send_file') {
    $contact_id = $_POST['contact_id'] ?? '';
    $current_id = $_SESSION['registro'];

    if (empty($contact_id)) {
        echo json_encode(['erro' => 'Contato inválido']);
        exit;
    }

    if (!isset($_FILES['file'])) {
        echo json_encode(['erro' => 'Nenhum arquivo enviado']);
        exit;
    }

    $file = $_FILES['file'];

    $allowed = ['png','jpg','jpeg','gif','webp','pdf','doc','docx','zip','rar','txt','ppt','pptx'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        echo json_encode(['erro' => 'Tipo de arquivo não permitido']);
        exit;
    }

    $uploadDir = __DIR__ . "/../../uploads/chat/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newName = uniqid() . "." . $ext;
    $filePath = $uploadDir . $newName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['erro' => 'Falha ao salvar arquivo']);
        exit;
    }

    $fileURL = "../uploads/chat/" . $newName;

    $sql = "INSERT INTO Mensagens 
            (remetente_id, destinatario_id, conteudo, arquivo_url, data_dia, data_hora) 
            VALUES 
            (:r, :d, :conteudo, :arquivo, CURDATE(), NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':r', $current_id);
    $stmt->bindValue(':d', $contact_id);
    $stmt->bindValue(':conteudo', $file['name']);
    $stmt->bindValue(':arquivo', $fileURL);

    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true]);
        exit;
    } else {
        echo json_encode(['erro' => 'Erro ao salvar no banco']);
        exit;
    }
}

if ($action === 'get_contact_details') {
    $contact_id = $_POST['contact_id'] ?? '';
    
    if (empty($contact_id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID de contato não fornecido']);
        exit;
    }
    
    $sql = "SELECT 
            a.Registro_Academico as id,
            a.Nome,
            a.Foto_Perfil,
            m.disciplina_monitorada as disciplina
        FROM Aluno a
        LEFT JOIN Monitora m ON a.Registro_Academico = m.Registro_Academico
        WHERE a.Registro_Academico = :contact_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':contact_id', $contact_id);
    $stmt->execute();
    
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($contact) {
        echo json_encode(['sucesso' => true, 'contato' => $contact]);
    } else {
        http_response_code(404);
        echo json_encode(['erro' => 'Contato não encontrado']);
    }
    exit;
}

if ($action === 'search_contacts') {

    $search = $_GET['q'] ?? '';
    $current_id = $_SESSION['registro'];
    $sql = "SELECT DISTINCT 
                a.Registro_Academico as id, 
                a.Nome, 
                a.Foto_Perfil,
                m.disciplina_monitorada as disciplina
            FROM Aluno a
            LEFT JOIN Monitora m ON a.Registro_Academico = m.Registro_Academico
            WHERE a.Registro_Academico != :current_id
              AND a.Nome LIKE :search
            ORDER BY a.Nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':current_id', $current_id);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->execute();

    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['sucesso' => true, 'contatos' => $contacts]);
    exit;
}

http_response_code(400);
echo json_encode(['erro' => 'Ação não reconhecida']);
exit;

?>
