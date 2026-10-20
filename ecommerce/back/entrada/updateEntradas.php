<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $id = (int) $_POST['id_entrada'];
    $fk_produto = (int) $_POST['fk_produto'];
    $quantidade = (int) $_POST['quantidade'];
    $custo_unitario = $_POST['custo_unitario'];
    $obs = $_POST['obs'];

    if ($quantidade <= 0) {
        header("Location: entradas.php?erro=quantidade");
        exit;
    }

    $conn->beginTransaction();
    $antigo = $conn->prepare("SELECT fk_produto FROM entrada WHERE id_entrada = :id");
    $antigo->execute([':id' => $id]);
    $produtoAntigo = (int) $antigo->fetchColumn();
    $produtos = array_unique([$produtoAntigo, $fk_produto]);
    $antes = [];
    foreach ($produtos as $p) {
        $antes[$p] = resumoEstoque($conn, $p)[0] ?? null;
    }

    $varSQL = "
        UPDATE entrada
        SET
            fk_produto = :fk_produto,
            quantidade = :quantidade,
            custo_unitario = :custo_unitario,
            obs = :obs
        WHERE id_entrada = :id_entrada";
    $update = $conn->prepare($varSQL);
    $update->bindParam(':fk_produto', $fk_produto);
    $update->bindParam(':quantidade', $quantidade);
    $update->bindParam(':custo_unitario', $custo_unitario);
    $update->bindParam(':obs', $obs);
    $update->bindParam(':id_entrada', $id);
    if (!$update->execute()) {
        $conn->rollBack();
        echo "Erro ao alterar a entrada.";
        exit;
    }

    // a alteração não pode deixar entrega/reserva sem estoque
    foreach ($produtos as $p) {
        $depois = resumoEstoque($conn, $p)[0] ?? null;
        if ($antes[$p] && $depois && pioraSaldo($antes[$p], $depois)) {
            $conn->rollBack();
            header("Location: entradas.php?erro=estoque");
            exit;
        }
    }
    $conn->commit();
    header("Location: entradas.php");
    exit;
?>
