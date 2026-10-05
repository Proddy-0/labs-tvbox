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

    $select = $conn->prepare("SELECT * FROM usuario WHERE id_usuario = :id");
    $select->execute([':id' => $_SESSION['id_usuario']]);
    $usuario = $select->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(["erro" => "Usuário não encontrado"]);
        exit;
    }

    echo json_encode([
        "nome" => $usuario['nome'],
        "email" => $usuario['email'],
        "telefone" => $usuario['telefone'] ?? null,
        "curso" => $usuario['curso'] ?? null,
        "admin" => (bool) $usuario['admin']
    ]);
?>