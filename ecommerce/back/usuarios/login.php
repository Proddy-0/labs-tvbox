<?php
    // A tela de login é a do site (front/login.html); aqui só processa o POST.
    include "../util.php";
    session_start();
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        header("Location: ../../front/login.html");
        exit;
    }
    $conn = conecta();
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $varSQL = "
        SELECT *
        FROM usuario
        WHERE email = :email
        AND excluido = false";
    $select = $conn->prepare($varSQL);
    $select->bindParam(':email', $email);
    $select->execute();
    $usuario = $select->fetch(PDO::FETCH_ASSOC);
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['admin'] = $usuario['admin'];
        if (($_POST['next'] ?? '') == 'carrinho') {
            // veio do "Finalizar encomenda": volta pro carrinho
            header("Location: ../../front/carrinho.html");
        } elseif ($usuario['admin']) {
            header("Location: ../painel/estoque.php");
        } else {
            header("Location: ../compras/encomendas.php");
        }
        exit;
    }
    $next = (($_POST['next'] ?? '') == 'carrinho') ? '&next=carrinho' : '';
    header("Location: ../../front/login.html?erro=1$next");
    exit;
?>
