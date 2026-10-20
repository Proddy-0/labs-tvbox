<?php
    // Inscrição na newsletter (form da home). E-mail repetido não dá erro: só não duplica.
    header("Content-Type: application/json; charset=utf-8");
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        http_response_code(405);
        echo json_encode(["erro" => "Use POST"]);
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["erro" => "E-mail inválido."]);
        exit;
    }

    include "../util.php";
    $conn = conecta();
    $insert = $conn->prepare("
        INSERT INTO newsletter (email)
        VALUES (:email)
        ON CONFLICT (email) DO NOTHING");
    $insert->execute([':email' => mb_strtolower(mb_substr($email, 0, 150))]);

    echo json_encode(["sucesso" => true]);
?>
