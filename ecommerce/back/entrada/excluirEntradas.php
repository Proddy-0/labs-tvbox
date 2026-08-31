<?php
    include "../util.php";
    $conn = conecta();
    $id = $_GET['id'];
    $varSQL = "
        DELETE FROM entrada
        WHERE id_entrada = :id";
    $delete = $conn->prepare($varSQL);
    $delete->bindParam(':id',$id);
    if ($delete->execute()) {
        header("Location: entradas.php");
        exit;
    }
    echo "Erro ao excluir a entrada.";
?>