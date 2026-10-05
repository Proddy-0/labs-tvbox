<?php
    session_start();
    header("Content-Type: application/json; charset=utf-8");

    if (!isset($_SESSION['id_usuario'])) {
        http_response_code(401);
        echo json_encode(["erro" => "Não autenticado"]);
        exit;
    }

    include "../util.php";
    $conn = conecta();

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if ($nome === '' || $email === '') {
        http_response_code(400);
        echo json_encode(["erro" => "Nome e e-mail são obrigatórios."]);
        exit;
    }

    // Garante que o e-mail não pertence a outro usuário
    $checa = $conn->prepare("
        SELECT id_usuario FROM usuario
        WHERE email = :email AND id_usuario != :id AND excluido = false");
    $checa->execute([':email' => $email, ':id' => $_SESSION['id_usuario']]);

    if ($checa->fetch()) {
        http_response_code(409);
        echo json_encode(["erro" => "Esse e-mail já está em uso por outra conta."]);
        exit;
    }

    $update = $conn->prepare("
        UPDATE usuario
        SET nome = :nome, email = :email, telefone = :telefone
        WHERE id_usuario = :id");
    $update->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone,
        ':id' => $_SESSION['id_usuario']
    ]);

    $_SESSION['nome'] = $nome; // mantém o nome do header atualizado

    echo json_encode(["sucesso" => true]);
?>