<?php
    // Usado pelo JS do site pra saber se tem alguém logado (troca Entrar/Sair no header)
    session_start();
    header("Content-Type: application/json; charset=utf-8");
    if (isset($_SESSION['id_usuario'])) {
        echo json_encode([
            "logado" => true,
            "nome" => $_SESSION['nome'],
            "admin" => (bool) $_SESSION['admin']
        ]);
    } else {
        echo json_encode(["logado" => false]);
    }
?>
