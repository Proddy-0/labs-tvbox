<?php
    include "../util.php";
    $conn = conecta();
    $id = $_GET['id'];
    $varSQL = "
        UPDATE produto
        SET
            excluido = true,
            data_exclusao = CURRENT_TIMESTAMP
        WHERE id_produto = :id";
    $delete = $conn->prepare($varSQL);
    $delete->bindParam(':id',$id);
    $delete->execute();
    header("Location: listaprodutos.php");
    exit;
?>