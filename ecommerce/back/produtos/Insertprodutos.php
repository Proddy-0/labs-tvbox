<?php
    include "../util.php";
    $conn = conecta();
    $varSQL = "INSERT INTO produto
            (nome, descricao, valor_unitario)
            VALUES
            (:nome, :descricao, :valor_unitario)";
    $insert = $conn->prepare($varSQL);
    $insert->bindParam(':nome', $_POST['nome']);
    $insert->bindParam(':descricao', $_POST['descricao']);
    $insert->bindParam(':valor_unitario', $_POST['valor_unitario']);
    if ($insert->execute()) {
        $id = $conn->lastInsertId();
        if (!empty($_FILES['arquivo']['name'])) {
            salvaUpload(
                $id, "imagens/produtos", $_FILES, 'arquivo');
        }
    }
    header("Location: listaprodutos.php");
    exit;
?>