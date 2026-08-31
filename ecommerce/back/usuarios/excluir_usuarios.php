<?php
    include "../util.php";
    $conn = conecta();
    $id = $_GET['id'];
    $varSQL = "
        UPDATE usuario
        SET
            excluido = true,
            data_exclusao = CURRENT_TIMESTAMP
        WHERE id_usuario = :id";
    $delete = $conn->prepare($varSQL);
    $delete->bindParam(':id', $id);
    $delete->execute();
    header("Location: usuarios.php");
    exit;
?>
