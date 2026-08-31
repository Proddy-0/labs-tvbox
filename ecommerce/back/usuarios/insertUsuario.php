<?php
    include "../util.php";
    $conn = conecta();
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $telefone = $_POST['telefone'];
    // Verifica se o email já existe
    $varSQL = "
        SELECT id_usuario
        FROM usuario
        WHERE email = :email";
    $select = $conn->prepare($varSQL);
    $select->bindParam(':email', $email);
    $select->execute();
    if ($select->fetch()) {
        echo "Este email já está cadastrado.";
        echo "<br><br>";
        echo "<a href='adicionarUsuario.php'>Voltar</a>";
        exit;
    }
    // Insere o usuário
    $varSQL = "
        INSERT INTO usuario
        (
            nome,
            email,
            senha,
            telefone,
            admin,
            excluido
        )
        VALUES
        (
            :nome,
            :email,
            :senha,
            :telefone,
            false,
            false
        )";
    $insert = $conn->prepare($varSQL);
    $insert->bindParam(':nome', $nome);
    $insert->bindParam(':email', $email);
    $insert->bindParam(':senha', $senha);
    $insert->bindParam(':telefone', $telefone);
    if ($insert->execute()) {
        $id = $conn->lastInsertId();
        if (
            isset($_FILES['imagem']) &&
            !empty($_FILES['imagem']['name'])
        ) {
            salvaUpload(
                $id,
                "imagens/usuarios",
                $_FILES,
                'imagem');
        }
        header("Location: usuarios.php");
        exit;
    }
?>