<?php
    // Recebe o formulário de contato do site (front/contato.html) e grava na tabela contato
    session_start();
    header("Content-Type: application/json; charset=utf-8");
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        http_response_code(405);
        echo json_encode(["erro" => "Use POST"]);
        exit;
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $assunto = $_POST['assunto'] ?? '';
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($nome == '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $mensagem == ''
        || !in_array($assunto, ['duvida', 'pedido', 'outro'])) {
        http_response_code(400);
        echo json_encode(["erro" => "Preencha nome, e-mail válido, assunto e mensagem."]);
        exit;
    }

    include "../util.php";
    $conn = conecta();
    $insert = $conn->prepare("
        INSERT INTO contato (nome, email, assunto, mensagem, fk_usuario)
        VALUES (:nome, :email, :assunto, :mensagem, :fk_usuario)");
    $insert->execute([
        ':nome' => mb_substr($nome, 0, 100),
        ':email' => mb_substr($email, 0, 150),
        ':assunto' => $assunto,
        ':mensagem' => mb_substr($mensagem, 0, 2000),
        ':fk_usuario' => $_SESSION['id_usuario'] ?? null
    ]);

    echo json_encode(["sucesso" => true]);
?>
