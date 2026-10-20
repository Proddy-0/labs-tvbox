<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $id = (int) $_GET['id'];

    $conn->beginTransaction();
    $produto = $conn->prepare("SELECT fk_produto FROM entrada WHERE id_entrada = :id");
    $produto->execute([':id' => $id]);
    $idProduto = (int) $produto->fetchColumn();
    $antes = resumoEstoque($conn, $idProduto)[0] ?? null;

    $delete = $conn->prepare("
        DELETE FROM entrada
        WHERE id_entrada = :id");
    $delete->bindParam(':id', $id);
    if (!$delete->execute()) {
        $conn->rollBack();
        echo "Erro ao excluir a entrada.";
        exit;
    }

    // excluir a entrada não pode deixar entrega/reserva sem estoque
    $depois = resumoEstoque($conn, $idProduto)[0] ?? null;
    if ($antes && $depois && pioraSaldo($antes, $depois)) {
        $conn->rollBack();
        header("Location: entradas.php?erro=estoque");
        exit;
    }
    $conn->commit();
    header("Location: entradas.php");
    exit;
?>
