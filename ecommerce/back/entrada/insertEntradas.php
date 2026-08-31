<?php
    include "../util.php";
    $conn = conecta();
    $fk_produto =$_POST['fk_produto'];
    $quantidade =$_POST['quantidade'];
    $custo_unitario =$_POST['custo_unitario'];
    $obs =$_POST['obs'];
    $varSQL = "
        INSERT INTO entrada
        (
            quantidade,
            custo_unitario,
            obs,
            fk_produto
        )
        VALUES
        (
            :quantidade,
            :custo_unitario,
            :obs,
            :fk_produto
        )";
    $insert = $conn->prepare($varSQL);
    $insert->bindParam(':quantidade',$quantidade);
    $insert->bindParam(':custo_unitario',$custo_unitario);
    $insert->bindParam(':obs',$obs);
    $insert->bindParam(':fk_produto',$fk_produto);
    if ($insert->execute()) {
        header("Location: entradas.php");
        exit;
    }
    echo "Erro ao adicionar a entrada.";
?>