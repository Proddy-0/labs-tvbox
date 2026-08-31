<?php
    include "../util.php";
    $conn = conecta();
    $id = $_POST['id_usuario'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $varSQL = "
        UPDATE usuarios
        SET
            nome = :nome,
            email = :email,
            telefone = :telefone
        WHERE id_usuario = :id_usuario";
    $update = $conn->prepare($varSQL);
    $update->bindParam(':nome', $nome);
    $update->bindParam(':email', $email);
    $update->bindParam(':telefone', $telefone);
    $update->bindParam(':id_usuario', $id);
    if ($update->execute()) {
        if (
            isset($_FILES['arquivo']) &&
            !empty($_FILES['arquivo']['name'])
        ) {
            salvaUpload(
                $id,
                "imagens/usuarios",
                $_FILES,
                'arquivo');
        }
        header("Location: usuarios.php");
        exit;
    } else {
        echo "Erro ao alterar o usuário.";
    }
?>