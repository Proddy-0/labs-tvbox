<?php
 
include "../util.php";
 
$conn = conecta();
 
$varSQL = "UPDATE produto
           SET nome = :nome,
               descricao = :descricao,
               valor = :valor
           WHERE id = :id";
 
$update = $conn->prepare($varSQL);
 
$update->bindParam(':nome', $_POST['nome']);
$update->bindParam(':descricao', $_POST['descricao']);
$update->bindParam(':valor', $_POST['valor']);
$update->bindParam(':id', $_POST['id']);
 
$update->execute();
 
header("Location: listaProdutos.php");
 
?>
 