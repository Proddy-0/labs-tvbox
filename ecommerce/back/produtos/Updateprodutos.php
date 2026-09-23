<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $varSQL = "
        UPDATE produto
        SET
            nome = :nome,
            descricao = :descricao,
            valor_unitario = :valor_unitario
        WHERE id_produto = :id_produto";
    $update = $conn->prepare($varSQL);
    $update->bindParam(':nome', $_POST['nome']);
    $update->bindParam(':descricao', $_POST['descricao']);
    $update->bindParam(':valor_unitario', $_POST['valor_unitario']);
    $update->bindParam(':id_produto', $_POST['id_produto']);
    if ($update->execute()) {
        if (!empty($_FILES['arquivo']['name'])) {
            salvaUpload(
                $_POST['id_produto'], "imagens/produtos", $_FILES, 'arquivo');
        }
        header("Location: listaprodutos.php");
        exit;
    }
    echo "Erro ao alterar o produto.";
?>
