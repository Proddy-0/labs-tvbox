<?php
    include "../util.php";
    session_start();
    $conn = conecta();
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $telefone = $_POST['telefone'];
    // Verifica se o email já existe
    $varSQL = "
        SELECT id_usuario
        FROM usuario
        WHERE email = :email";
    $select = $conn->prepare($varSQL);
    $select->bindParam(':email', $email);
    $select->execute();
    // cadastro feito pela tela do site (front/cadastro.html)
    $doSite = ($_POST['origem'] ?? '') == 'site';
    if ($select->fetch()) {
        if ($doSite) {
            header("Location: ../../front/cadastro.html?erro=email");
            exit;
        }
        echo "Este email já está cadastrado.";
        echo "<br><br>";
        echo "<a href='adicionarUsuario.php'>Voltar</a>";
        exit;
    }
    // Só um admin logado (tela do painel) pode criar outro admin. Cadastro pelo site é sempre cliente.
    $ehAdmin = !$doSite
        && !empty($_SESSION['admin'])
        && isset($_POST['admin']);
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
            :admin,
            false
        )";
    $insert = $conn->prepare($varSQL);
    $insert->bindParam(':nome', $nome);
    $insert->bindParam(':email', $email);
    $insert->bindParam(':senha', $senha);
    $insert->bindParam(':telefone', $telefone);
    $insert->bindValue(':admin', $ehAdmin, PDO::PARAM_BOOL);
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
        header($doSite ? "Location: ../../front/login.html?cadastro=1" : "Location: usuarios.php");
        exit;
    }
?>
