<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $id = $_POST['id_usuario'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    // o admin logado não pode tirar o próprio acesso (senão fica trancado fora do painel)
    $ehAdmin = isset($_POST['admin']) || $id == $_SESSION['id_usuario'];
    $varSQL = "
        UPDATE usuario
        SET
            nome = :nome,
            email = :email,
            telefone = :telefone,
            admin = :admin
        WHERE id_usuario = :id_usuario";
    $update = $conn->prepare($varSQL);
    $update->bindParam(':nome', $nome);
    $update->bindParam(':email', $email);
    $update->bindParam(':telefone', $telefone);
    $update->bindValue(':admin', $ehAdmin, PDO::PARAM_BOOL);
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
